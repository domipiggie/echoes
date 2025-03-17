<?php

namespace WebSocket;

use Ratchet\ConnectionInterface;

class HeartbeatHandler
{
    protected $connections;
    protected $lastPings;
    protected $timeout;
    protected $logger;
    protected $errorHandler;

    public function __construct($timeout = 30, $logger = null)
    {
        $this->connections = new \SplObjectStorage;
        $this->lastPings = [];
        $this->timeout = $timeout;
        $this->logger = $logger;
        $this->errorHandler = new WebSocketErrorHandler($logger);
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
        try {
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
                try {
                    $conn->close();
                } catch (\Exception $e) {
                    $this->log("Error closing connection: " . $e->getMessage(), "error");
                }
            }
            
            foreach ($this->connections as $conn) {
                try {
                    $conn->send(json_encode(['type' => 'ping', 'time' => $now]));
                } catch (\Exception $e) {
                    $this->log("Error sending ping: " . $e->getMessage(), "error");
                }
            }
            
            return count($disconnected);
        } catch (WebSocketException $e) {
            $this->errorHandler->handleException($e);
            return 0;
        } catch (\Exception $e) {
            $this->errorHandler->handleException(
                new WebSocketException("Heartbeat error: " . $e->getMessage(), 5001, "heartbeat_error")
            );
            return 0;
        }
    }
    
    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->$level($message);
        }
    }
}