<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Services\NotificationService;

class FriendshipHandler
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
        $this->notificationService = new NotificationService($clients, $logger);
    }

    public function handleFriendRequest(ConnectionInterface $from, $data)
    {
        if (!isset($data['recipient_id'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Missing recipient ID for friend request',
                'code' => 'INVALID_REQUEST'
            ]));
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            $friendship = new \Friendship($this->dbConn, $sender->id, $recipientId);
            $result = $friendship->sendFriendRequest();

            if ($result) {
                $from->send(json_encode([
                    'type' => 'friend_request_sent',
                    'recipient_id' => $recipientId,
                    'status' => 'success',
                    'timestamp' => time()
                ]));

                $this->notificationService->notifyFriendRequest($sender, $recipientId);

                $this->logger->info("Friend request sent from user {$sender->id} to user {$recipientId}");
            }
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
                'message' => 'Failed to send friend request',
                'code' => 'INTERNAL_ERROR'
            ]));

            $this->logger->error("Friend request exception: {$e->getMessage()}");
        }
    }

    public function handleFriendRequestDeny(ConnectionInterface $from, $data)
    {
        if (!isset($data['recipient_id'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Missing recipient ID for friend request deny',
                'code' => 'INVALID_REQUEST'
            ]));
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            $friendship = new \Friendship($this->dbConn, $sender->id, $recipientId);
            $result = $friendship->declineFriendRequest();

            if ($result) {
                $from->send(json_encode([
                    'type' => 'friend_request_denied',
                    'recipient_id' => $recipientId,
                    'status' => 'success',
                    'timestamp' => time()
                ]));

                $this->notificationService->notifyFriendRequestDeny($sender, $recipientId);

                $this->logger->info("Friend request denied from user {$sender->id} to user {$recipientId}");
            }
        } catch (\ApiException $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage(),
                'code' => 'FRIEND_REQUEST_DENY_FAILED',
                'status_code' => $e->getStatusCode()
            ]));
        } catch (\Exception $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Failed to deny friend request',
                'code' => 'INTERNAL_ERROR'
            ]));
        }
    }

    public function handleFriendRequestAccept(ConnectionInterface $from, $data)
    {
        if (!isset($data['recipient_id'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Missing recipient ID for friend request accept',
                'code' => 'INVALID_REQUEST'
            ]));
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            $friendship = new \Friendship($this->dbConn, $sender->id, $recipientId);
            $result = $friendship->acceptFriendRequest($sender->id);

            if ($result) {
                $from->send(json_encode([
                    'type' => 'friend_request_accepted',
                    'recipient_id' => $recipientId,
                    'status' => 'success',
                    'timestamp' => time()
                ]));

                $this->notificationService->notifyFriendRequestAccept($sender, $recipientId);

                $this->logger->info("Friend request accepted from user {$sender->id} to user {$recipientId}");
            }
        } catch (\ApiException $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage(),
                'code' => 'FRIEND_REQUEST_ACCEPT_FAILED',
                'status_code' => $e->getStatusCode()
            ]));
        } catch (\Exception $e) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Failed to accept friend request',
                'code' => 'INTERNAL_ERROR'
            ]));
        }
    }
}
