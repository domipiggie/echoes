<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Handlers\FriendshipHandler;

class MessageHandler
{
    protected $clients;
    protected $logger;
    protected $dbConn;
    protected $friendshipHandler;

    public function __construct(\SplObjectStorage $clients, Logger $logger)
    {
        $this->clients = $clients;
        $this->logger = $logger;

        $database = new \Database();
        $this->dbConn = $database->getConnection();
        
        $this->friendshipHandler = new FriendshipHandler($clients, $logger, $this->dbConn);
    }

    public function handleMessage(ConnectionInterface $from, $data)
    {
        $from->lastActivity = time();

        if (!isset($data['type'])) {
            $this->logger->warning("Received message without type from connection {$from->resourceId}");
            return;
        }

        switch ($data['type']) {
            case 'friend_add':
                $this->friendshipHandler->handleFriendRequest($from, $data);
                break;
                
            case 'friend_deny':
                $this->friendshipHandler->handleFriendRequestDeny($from, $data);
                break;

            default:
                $this->logger->debug("Received unknown message type '{$data['type']}' from connection {$from->resourceId}");
                break;
        }
    }
}
