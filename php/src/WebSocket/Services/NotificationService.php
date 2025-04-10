<?php

namespace WebSocket\Services;

use Ratchet\ConnectionInterface;
use Utils\Logger;

class NotificationService
{
    protected $clients;
    protected $logger;
    
    public function __construct(\SplObjectStorage $clients, Logger $logger)
    {
        $this->clients = $clients;
        $this->logger = $logger;
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