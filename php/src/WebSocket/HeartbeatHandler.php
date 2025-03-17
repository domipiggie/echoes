<?php

namespace WebSocket;

use Ratchet\ConnectionInterface;

class HeartbeatHandler
{
    protected $connections;
    protected $lastPings;
    protected $timeout;
    protected $logger;

    public function __construct($timeout = 30, $logger = null)
    {
        $this->connections = new \SplObjectStorage;
        $this->lastPings = [];
        $this->timeout = $timeout;
        $this->logger = $logger;
    }

    public function addConnection(ConnectionInterface $conn)
    {
        $this->connections->attach($conn);
        $this->lastPings[$conn->resourceId] = time();
    }

    public function removeConnection(ConnectionInterface $conn)
    {
        $this->connections->detach($conn);
        unset($this->lastPings[$conn->resourceId]);
    }

    public function handlePong(ConnectionInterface $conn)
    {
        $this->lastPings[$conn->resourceId] = time();
    }

    public function checkConnections()
    {
        $now = time();
        $disconnected = [];
        
        foreach ($this->connections as $conn) {
            if (!isset($this->lastPings[$conn->resourceId])) {
                $this->lastPings[$conn->resourceId] = $now;
                continue;
            }
            
            if ($now - $this->lastPings[$conn->resourceId] > $this->timeout) {
                $this->log("Connection {$conn->resourceId} timed out");
                $disconnected[] = $conn;
            }
        }
        
        foreach ($disconnected as $conn) {
            $conn->close();
        }
        
        foreach ($this->connections as $conn) {
            $conn->send(json_encode(['type' => 'ping', 'time' => $now]));
        }
        
        return count($disconnected);
    }
    
    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->$level($message);
        }
    }
}