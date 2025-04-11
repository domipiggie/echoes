<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Services\NotificationService;

class ChatMessageHandler
{
    protected $clients;
    protected $logger;
    protected $dbConn;
    protected $notificationService;

    public function __construct(\SplObjectStorage $clients, Logger $logger, $dbConn)
    {
        $this->clients = $clients;
        $this->logger = $logger;
        $this->dbConn = $dbConn;
        $this->notificationService = new NotificationService($clients, $logger, $dbConn);
    }

    public function handleChatMessage(ConnectionInterface $from, $data)
    {
        if (!isset($data['channelId']) || !isset($data['content']) || !isset($data['messageType'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Missing channelId, content or messageType for chat message',
                'code' => 'INVALID_REQUEST'
            ]));
            return;
        }

        $sender = $from->userData;
        $channelId = $data['channelId'];
        $content = $data['content'];
        $messageType = $data['messageType'];

        try {
            $message = new \Message($this->dbConn);
            if (!$message->hasChannelAccess($sender->id, $channelId)) {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'You do not have access to this channel',
                    'code' => 'ACCESS_DENIED'
                ]));
                return;
            }
            $message->createMessage($channelId, $sender->id, $content, $messageType);

            $this->logger->info("Chat message sent by user {$sender->id} in channel {$channelId}");

            $this->notificationService->notifyChatMessage($sender, $channelId, $content, $messageType);
        } catch (\ApiException $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage(),
                'code' => 'FRIEND_REQUEST_FAILED',
                'status_code' => $e->getStatusCode()
            ]));

            $this->logger->error("Friend request failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Failed to send chat message',
                'code' => 'INTERNAL_ERROR'
            ]));

            $this->logger->error("Chat message exception: {$e->getMessage()}");
        }
    }

    public function handleDeleteMessage(ConnectionInterface $from, $data)
    {
        if (!isset($data['messageId']) || !isset($data['channelId'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Missing messageId or channelId for message deletion',
                'code' => 'INVALID_REQUEST'
            ]));
            return;
        }

        $sender = $from->userData;
        $messageId = $data['messageId'];
        $channelId = $data['channelId'];

        try {
            $message = new \Message($this->dbConn);

            if (!$message->hasChannelAccess($sender->id, $channelId)) {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'You do not have access to this channel',
                    'code' => 'ACCESS_DENIED'
                ]));
                return;
            }

            $result = $message->deleteMessage($messageId, $sender->id);

            if ($result) {
                $this->logger->info("Message {$messageId} deleted by user {$sender->id} in channel {$channelId}");

                $from->send(json_encode([
                    'type' => 'message_deleted',
                    'messageId' => $messageId,
                    'channelId' => $channelId
                ]));

                $this->notificationService->notifyMessageDeleted($sender, $channelId, $messageId);
            } else {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'Failed to delete message',
                    'code' => 'DELETE_FAILED'
                ]));
            }
        } catch (\ApiException $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage(),
                'code' => 'DELETE_FAILED',
                'status_code' => $e->getStatusCode()
            ]));

            $this->logger->error("Message deletion failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Failed to delete message',
                'code' => 'INTERNAL_ERROR'
            ]));

            $this->logger->error("Message deletion exception: {$e->getMessage()}");
        }
    }

    public function handleEditMessage(ConnectionInterface $from, $data)
    {
        if (!isset($data['messageId']) || !isset($data['channelId']) || !isset($data['content'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Missing messageId, channelId or content for message editing',
                'code' => 'INVALID_REQUEST'
            ]));
            return;
        }

        $sender = $from->userData;
        $messageId = $data['messageId'];
        $channelId = $data['channelId'];
        $newContent = $data['content'];

        try {
            $message = new \Message($this->dbConn);

            if (!$message->hasChannelAccess($sender->id, $channelId)) {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'You do not have access to this channel',
                    'code' => 'ACCESS_DENIED'
                ]));
                return;
            }

            $result = $message->editMessage($messageId, $sender->id, $newContent);

            if ($result) {
                $this->logger->info("Message {$messageId} edited by user {$sender->id} in channel {$channelId}");

                $from->send(json_encode([
                    'type' => 'message_edited',
                    'messageId' => $messageId,
                    'channelId' => $channelId,
                    'content' => $newContent
                ]));

                $this->notificationService->notifyMessageEdited($sender, $channelId, $messageId, $newContent);
            } else {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'Failed to edit message',
                    'code' => 'EDIT_FAILED'
                ]));
            }
        } catch (\ApiException $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage(),
                'code' => 'EDIT_FAILED',
                'status_code' => $e->getStatusCode()
            ]));

            $this->logger->error("Message editing failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Failed to edit message',
                'code' => 'INTERNAL_ERROR'
            ]));

            $this->logger->error("Message editing exception: {$e->getMessage()}");
        }
    }
}
