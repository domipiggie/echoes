<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Services\NotificationService;
use WebSocket\Services\ErrorHandlerService;
use WebSocket\Services\ResponseHandlerService;

class FriendshipHandler
{
    protected $clients;
    protected $logger;
    protected $notificationService;
    protected $errorHandler;

    public function __construct(\SplObjectStorage $clients, Logger $logger)
    {
        $this->clients = $clients;
        $this->logger = $logger;
        $this->notificationService = new NotificationService($clients, $logger);
        $this->errorHandler = new ErrorHandlerService($logger);
    }

    public function handleFriendRequest(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['recipient_id'])) {
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            $friendship = new \Friendship($sender->id, $recipientId);
            $result = $friendship->sendFriendRequest($sender->id);

            try {
                if ($result) {
                    $channel = new \Channel();
                    $channel->createFriendshipChannel($result);
                }
            } catch (\Exception $e) {
                
            }

            ResponseHandlerService::sendSuccess($from, 'friend_add');

            $notifyData = ['sender' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]];
            $this->notificationService->notifyClient($recipientId, 'friend_request_recieved', $notifyData);

            $this->logger->info("Friend request sent from user {$sender->id} to user {$recipientId}");
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'friend_add');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'FRIEND_REQUEST_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Friend request failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'friend_add');
        }
    }

    public function handleFriendRequestDeny(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['recipient_id'])) {
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            echo json_encode(["sender"=>$sender->id, "recipient"=>$recipientId]);
            $friendship = new \Friendship($sender->id, $recipientId);
            $result = $friendship->declineFriendRequest();

            if ($result) {
                ResponseHandlerService::sendSuccess($from, 'friend_deny');

                $notifyData = ['sender' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ]];
                $this->notificationService->notifyClient($recipientId, 'friend_request_denied', $notifyData);

                $this->logger->info("Friend request denied from user {$sender->id} to user {$recipientId}");
            }
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'friend_request_deny');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'FRIEND_REQUEST_DENY_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Friend request deny failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'friend_request_deny');
        }
    }

    public function handleFriendRequestAccept(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['recipient_id'])) {
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            $friendship = new \Friendship($sender->id, $recipientId);
            $result = $friendship->acceptFriendRequest($sender->id);

            if ($result) {
                ResponseHandlerService::sendSuccess($from, 'friend_accept');

                $notifyData = ['sender' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ]];
                $this->notificationService->notifyClient($recipientId, 'friend_request_accepted', $notifyData);

                $this->logger->info("Friend request accepted from user {$sender->id} to user {$recipientId}");
            }
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'friend_request_accept');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'FRIEND_REQUEST_ACCEPT_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Friend request accept failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'friend_request_accept');
        }
    }
    
    public function handleFriendRemove(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['recipient_id'])) {
            return;
        }

        $sender = $from->userData;
        $recipientId = $data['recipient_id'];

        try {
            $friendship = new \Friendship($sender->id, $recipientId);
            $result = $friendship->removeFriend();

            if ($result) {
                ResponseHandlerService::sendSuccess($from, 'friend_remove');

                $notifyData = ['sender' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ]];
                $this->notificationService->notifyClient($recipientId, 'friend_removed', $notifyData);

                $this->logger->info("Friend removed: user {$sender->id} removed user {$recipientId} from friends");
            }
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'friend_remove');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'FRIEND_REMOVE_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Friend removal failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'friend_remove');
        }
    }
}
