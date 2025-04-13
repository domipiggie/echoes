<?php

namespace WebSocket\Services;

use Ratchet\ConnectionInterface;
use Utils\Logger;

class NotificationService
{
    protected $clients;
    protected $logger;
    protected $dbConn;

    public function __construct(\SplObjectStorage $clients, Logger $logger, $dbConn)
    {
        $this->clients = $clients;
        $this->logger = $logger;
        $this->dbConn = $dbConn;
    }

    public function notifyFriendRequest($sender, $recipientId)
    {
        $recipient = $this->findRecipient($recipientId);

        if ($recipient) {
            $recipient->send(json_encode([
                'type' => 'friend_request_received',
                'sender' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ],
                'timestamp' => time()
            ]));

            $this->logger->debug("Friend request notification sent to online user {$recipientId}");
        } else {
            $this->logger->debug("Recipient {$recipientId} is offline, no real-time notification sent");
        }
    }

    public function notifyFriendRequestDeny($sender, $recipientId)
    {
        $recipient = $this->findRecipient($recipientId);

        if ($recipient) {
            $recipient->send(json_encode([
                'type' => 'friend_request_denied',
                'sender' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ],
                'timestamp' => time()
            ]));

            $this->logger->debug("Friend request deny notification sent to online user {$recipientId}");
        } else {
            $this->logger->debug("Recipient {$recipientId} is offline, no real-time notification sent");
        }
    }

    public function notifyFriendRequestAccept($sender, $recipientId)
    {
        $recipient = $this->findRecipient($recipientId);

        if ($recipient) {
            $recipient->send(json_encode([
                'type' => 'friend_request_accepted',
                'sender' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ],
                'timestamp' => time()
            ]));

            $this->logger->debug("Friend request accept notification sent to online user {$recipientId}");
        } else {
            $this->logger->debug("Recipient {$recipientId} is offline, no real-time notification sent");
        }
    }

    public function notifyChatMessage($sender, $channelId, $content, $messageType)
    {
        try {
            $messageModel = new \Message($this->dbConn);

            $userIds = $messageModel->getUsersWithChannelAccess($channelId);

            $this->logger->debug("Notifying " . count($userIds) . " users about new message in channel {$channelId}");

            $messageData = [
                'type' => 'new_message',
                'message' => [
                    'channelId' => $channelId,
                    'content' => $content,
                    'messageType' => $messageType,
                    'sender' => [
                        'id' => $sender->id,
                        'username' => $sender->username
                    ],
                    'timestamp' => time()
                ]
            ];

            $encodedMessage = json_encode($messageData);
            $notifiedCount = 0;

            foreach ($this->clients as $client) {
                if (isset($client->userData) && isset($client->userData->id)  && $client->userData->id != $sender->id) {
                    if (in_array($client->userData->id, $userIds)) {
                        $client->send($encodedMessage);
                        $notifiedCount++;
                    }
                }
            }

            $this->logger->debug("Sent chat message notification to {$notifiedCount} online users");
        } catch (\Exception $e) {
            $this->logger->error("Failed to notify users about new message: " . $e->getMessage());
        }
    }

    public function notifyMessageDeleted($sender, $channelId, $messageId)
    {
        try {
            $messageModel = new \Message($this->dbConn);

            $userIds = $messageModel->getUsersWithChannelAccess($channelId);

            $this->logger->debug("Notifying " . count($userIds) . " users about deleted message {$messageId} in channel {$channelId}");

            $messageData = [
                'type' => 'message_deleted',
                'data' => [
                    'channelId' => $channelId,
                    'messageId' => $messageId,
                    'deletedBy' => [
                        'id' => $sender->id,
                        'username' => $sender->username
                    ],
                    'timestamp' => time()
                ]
            ];

            $encodedMessage = json_encode($messageData);
            $notifiedCount = 0;

            foreach ($this->clients as $client) {
                if (isset($client->userData) && isset($client->userData->id) && $client->userData->id != $sender->id) {
                    if (in_array($client->userData->id, $userIds)) {
                        $client->send($encodedMessage);
                        $notifiedCount++;
                    }
                }
            }

            $this->logger->debug("Sent message deletion notification to {$notifiedCount} online users");
        } catch (\Exception $e) {
            $this->logger->error("Failed to notify users about deleted message: " . $e->getMessage());
        }
    }

    public function notifyMessageEdited($sender, $channelId, $messageId, $newContent)
    {
        try {
            $messageModel = new \Message($this->dbConn);

            $userIds = $messageModel->getUsersWithChannelAccess($channelId);

            $this->logger->debug("Notifying " . count($userIds) . " users about edited message {$messageId} in channel {$channelId}");

            $messageData = [
                'type' => 'message_edited',
                'data' => [
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

            $encodedMessage = json_encode($messageData);
            $notifiedCount = 0;

            foreach ($this->clients as $client) {
                if (isset($client->userData) && isset($client->userData->id) && $client->userData->id != $sender->id) {
                    if (in_array($client->userData->id, $userIds)) {
                        $client->send($encodedMessage);
                        $notifiedCount++;
                    }
                }
            }

            $this->logger->debug("Sent message edit notification to {$notifiedCount} online users");
        } catch (\Exception $e) {
            $this->logger->error("Failed to notify users about edited message: " . $e->getMessage());
        }
    }

    protected function findRecipient($recipientId)
    {
        foreach ($this->clients as $client) {
            if (isset($client->userData) && $client->userData->id == $recipientId) {
                return $client;
            }
        }

        return null;
    }
}
