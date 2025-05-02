<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Services\NotificationService;
use WebSocket\Services\ErrorHandlerService;
use WebSocket\Services\ResponseHandlerService;

class ChatMessageHandler
{
    protected $clients;
    protected $logger;
    protected $dbConn;
    protected $notificationService;
    protected $errorHandler;

    public function __construct(\SplObjectStorage $clients, Logger $logger, $dbConn)
    {
        $this->clients = $clients;
        $this->logger = $logger;
        $this->dbConn = $dbConn;
        $this->notificationService = new NotificationService($clients, $logger, $dbConn);
        $this->errorHandler = new ErrorHandlerService($logger);
    }

    public function handleChatMessage(ConnectionInterface $from, $data)
    {
        $this->logger->info(json_encode($data));
        if (!$this->errorHandler->validateRequest($from, $data, ['channelId', 'content', 'messageType'])) {
            return;
        }

        $sender = $from->userData;
        $channelId = $data['channelId'];
        $content = $data['content'];
        $messageType = $data['messageType'];

        try {
            $message = new \Message($this->dbConn);
            if (!$message->hasChannelAccess($sender->id, $channelId)) {
                throw new \WebSocketException(
                    'You do not have access to this channel',
                    'ACCESS_DENIED',
                    403
                );
            }
            $result = $message->createMessage($channelId, $sender->id, $content, $messageType);

            ResponseHandlerService::sendSuccess($from, 'chatmessage_sent', ['messageId' => $result['messageID']]);

            $this->logger->info("Chat message sent by user {$sender->id} in channel {$channelId}");

            $notifyData = [
                'message' => [
                    'channelId' => $channelId,
                    'messageId' => $result['messageID'],
                    'content' => $content,
                    'messageType' => $messageType,
                    'sender' => [
                        'id' => $sender->id,
                        'username' => $sender->username
                    ],
                    'timestamp' => $result['sent_at']
                ]
            ];
            $userIds = $message->getUsersWithChannelAccess($channelId);
            $this->notificationService->notifyMultipleClients($userIds, $sender->id, 'new_message', $notifyData);
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'chatmessage_send');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'CHAT_MESSAGE_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Chat message failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'chatmessage_send');
        }
    }

    public function handleDeleteMessage(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['messageId'])) {
            return;
        }

        $sender = $from->userData;
        $messageId = $data['messageId'];

        try {
            $message = new \Message($this->dbConn);
            $channelId = $message->getChannelIdFromMessageId($messageId);

            if (!$message->hasChannelAccess($sender->id, $channelId)) {
                throw new \WebSocketException(
                    'You do not have access to this channel',
                    'ACCESS_DENIED',
                    403
                );
            }

            $result = $message->deleteMessage($messageId, $sender->id);

            if ($result) {
                $this->logger->info("Message {$messageId} deleted by user {$sender->id} in channel {$channelId}");

                $responseData = [
                    'messageId' => $messageId,
                    'channelId' => $channelId,
                ];

                ResponseHandlerService::sendSuccess($from, 'chatmessage_deleted', $responseData);

                $notifyData = [
                    'message' => [
                        'channelId' => $channelId,
                        'messageId' => $messageId,
                        'deletedBy' => [
                            'id' => $sender->id,
                            'username' => $sender->username
                        ],
                        'timestamp' => time()
                    ]
                ];
                $userIds = $message->getUsersWithChannelAccess($channelId);
                $this->notificationService->notifyMultipleClients($userIds, $sender->id, 'message_deleted', $notifyData);
            } else {
                throw new \WebSocketException(
                    'Failed to delete message',
                    'DELETE_FAILED',
                    500
                );
            }
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'chatmessage_delete');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'DELETE_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Message deletion failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'chatmessage_delete');
        }
    }

    public function handleEditMessage(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['messageId', 'content'])) {
            return;
        }

        $sender = $from->userData;
        $messageId = $data['messageId'];
        $newContent = $data['content'];

        try {
            $message = new \Message($this->dbConn);
            $channelId = $message->getChannelIdFromMessageId($messageId);

            if (!$message->hasChannelAccess($sender->id, $channelId)) {
                throw new \WebSocketException(
                    'You do not have access to this channel',
                    'ACCESS_DENIED',
                    403
                );
            }

            $result = $message->editMessage($messageId, $sender->id, $newContent);

            if ($result) {
                $this->logger->info("Message {$messageId} edited by user {$sender->id} in channel {$channelId}");

                $responseData = [
                    'messageId' => $messageId,
                    'channelId' => $channelId,
                    'content' => $newContent
                ];

                ResponseHandlerService::sendSuccess($from, 'chatmessage_edited', $responseData);

                $notifyData = [
                    'message' => [
                        'channelId' => $channelId,
                        'messageId' => $messageId,
                        'content' => $newContent,
                        'editedBy' => [
                            'id' => $sender->id,
                            'username' => $sender->username
                        ],
                        'timestamp' => time()
                    ]
                ];
                $userIds = $message->getUsersWithChannelAccess($channelId);
                $this->notificationService->notifyMultipleClients($userIds, $sender->id, 'message_edited', $notifyData);
            } else {
                throw new \WebSocketException(
                    'Failed to edit message',
                    'EDIT_FAILED',
                    500
                );
            }
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'chatmessage_edit');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'EDIT_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Message editing failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'chatmessage_edit');
        }
    }
}
