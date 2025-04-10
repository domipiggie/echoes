<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Authentication\Authenticator;

class ConnectionHandler
{
    protected $clients;
    protected $logger;
    protected $authenticator;
    protected $connectionTimeout;
    
    public function __construct(\SplObjectStorage $clients, Logger $logger)
    {
        $this->clients = $clients;
        $this->logger = $logger;
        $this->authenticator = new Authenticator();
        $this->connectionTimeout = \Config\WebSocketConfig::CONNECTION_TIMEOUT;
    }
    
    public function handleOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $conn->lastActivity = time();
        
        $this->logger->info("New connection! ({$conn->resourceId})");
    }
    
    public function handleClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        $userId = isset($conn->userData) ? $conn->userData->id : 'unauthenticated';
        $this->logger->info("Connection {$conn->resourceId} has disconnected (User: {$userId})");
    }
    
    public function handleError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->error("Error on connection {$conn->resourceId}: {$e->getMessage()}");
        $conn->close();
    }
    
    public function checkTimeouts($currentTime)
    {
        foreach ($this->clients as $client) {
            if ($this->authenticator->checkAuthTimeout($client, $currentTime)) {
                continue;
            }
            
            if ($this->authenticator->isAuthenticated($client)) {
                if (isset($client->lastPongTime) && 
                    ($currentTime - $client->lastPongTime) > $this->connectionTimeout) {
                    $this->logger->warning("Connection {$client->resourceId} timed out (no pong response)");
                    $client->close();
                }
            }
        }
    }
}