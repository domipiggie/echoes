<?php

namespace WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Authentication\Authenticator;
use WebSocket\Handlers\ConnectionHandler;
use WebSocket\Handlers\MessageHandler;

class Server implements MessageComponentInterface
{
    protected $clients;
    protected $logger;
    protected $authenticator;
    protected $connectionHandler;
    protected $messageHandler;
    protected $pingInterval;
    protected $lastPingTime;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->logger = new Logger();
        $this->authenticator = new Authenticator();
        $this->connectionHandler = new ConnectionHandler($this->clients, $this->logger);
        $this->messageHandler = new MessageHandler($this->clients, $this->logger);
        $this->pingInterval = \Config\WebSocketConfig::HEARTBEAT_INTERVAL;
        $this->lastPingTime = time();
        
        $this->logger->info('WebSocket server initialized');
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->connectionHandler->handleOpen($conn);
        
        $this->authenticator->initiateAuthentication($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            
            if (!$data) {
                $this->logger->warning("Received invalid JSON from connection {$from->resourceId}");
                return;
            }
            
            if (isset($data['type']) && $data['type'] === 'auth') {
                $this->authenticator->authenticate($from, $data);
                return;
            }
            
            if (isset($data['type']) && $data['type'] === 'pong') {
                $from->lastPongTime = time();
                return;
            }
            
            if (!$this->authenticator->isAuthenticated($from)) {
                $this->logger->warning("Unauthenticated message from connection {$from->resourceId}");
                $from->close();
                return;
            }
            
            $this->messageHandler->handleMessage($from, $data);
        } catch (\Exception $e) {
            $this->logger->error("Error processing message: " . $e->getMessage());
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->connectionHandler->handleClose($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->connectionHandler->handleError($conn, $e);
    }
    
    public function checkConnections()
    {
        $currentTime = time();
        
        if ($currentTime - $this->lastPingTime >= $this->pingInterval) {
            $this->sendPingToAll();
            $this->lastPingTime = $currentTime;
        }
        
        $this->connectionHandler->checkTimeouts($currentTime);
    }
    
    protected function sendPingToAll()
    {
        $pingMessage = json_encode(['type' => 'ping', 'timestamp' => time()]);
        
        foreach ($this->clients as $client) {
            if ($this->authenticator->isAuthenticated($client)) {
                $client->send($pingMessage);
            }
        }
        
        $this->logger->debug("Sent ping to " . count($this->clients) . " clients");
    }
}