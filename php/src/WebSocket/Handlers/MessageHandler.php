<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Handlers\FriendshipHandler;
use WebSocket\Handlers\ChatMessageHandler;

class MessageHandler
{
    protected $clients;
    protected $logger;
    protected $dbConn;
    protected $friendshipHandler;
    protected $chatMessageHandler;
    protected $groupHandler;

    public function __construct(\SplObjectStorage $clients, Logger $logger)
    {
        $this->clients = $clients;
        $this->logger = $logger;

        $database = new \Database();
        $this->dbConn = $database->getConnection();

        $this->friendshipHandler = new FriendshipHandler($clients, $logger, $this->dbConn);
        $this->chatMessageHandler = new ChatMessageHandler($clients, $logger, $this->dbConn);
        $this->groupHandler = new GroupHandler($this->dbConn, $logger, $this->clients);
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
            case 'friend_accept':
                $this->friendshipHandler->handleFriendRequestAccept($from, $data);
                break;

            case 'chatmessage_send':
                $this->chatMessageHandler->handleChatMessage($from, $data);
                break;
            case 'chatmessage_delete':
                $this->chatMessageHandler->handleDeleteMessage($from, $data);
                break;
            case 'chatmessage_edit':
                $this->chatMessageHandler->handleEditMessage($from, $data);
                break;
            
            case 'group_create':
                $this->groupHandler->handleCreateGroupChannel($from, $data);
                break;

            default:
                $this->logger->debug("Received unknown message type '{$data['type']}' from connection {$from->resourceId}");
                break;
        }
    }
}
