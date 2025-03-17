<?php

namespace WebSocket;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'middleware' . DIRECTORY_SEPARATOR . 'AuthMiddleware.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'exceptions' . DIRECTORY_SEPARATOR . 'ApiException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use AuthMiddleware;

class MessageNotifier implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections = [];
    protected $lastPings = [];
    protected $rateLimiter;
    protected $notificationQueue;
    protected $logger;

    public function __construct($logger = null)
    {
        $this->clients = new \SplObjectStorage;
        $this->rateLimiter = new RateLimiter();
        $this->notificationQueue = new NotificationQueue(50, $logger);
        $this->logger = $logger;
        $this->log("MessageNotifier initialized");
    }

    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->$level($message);
        } else {
            echo date('[Y-m-d H:i:s]') . " {$level}: {$message}\n";
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->lastPings[$conn->resourceId] = time();
        $this->log("New connection! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            if (!$this->rateLimiter->isAllowed($from->resourceId)) {
                $this->log("Rate limit exceeded for connection {$from->resourceId}", "warning");
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'Rate limit exceeded. Please slow down.'
                ]));
                return;
            }
            
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON received");
            }
            
            if (isset($data['type']) && $data['type'] === 'ping') {
                $this->lastPings[$from->resourceId] = time();
                
                $from->send(json_encode([
                    'type' => 'pong',
                    'timestamp' => $data['timestamp'] ?? time()
                ]));
                return;
            }
            
            if (isset($data['type']) && $data['type'] === 'pong') {
                $this->lastPings[$from->resourceId] = time();
                return;
            }
            
            if (isset($data['type']) && $data['type'] === 'auth' && isset($data['userID'])) {
                $this->handleAuthentication($from, $data);
            } else if (isset($data['type']) && $data['type'] === 'admin_notification') {
                $this->log("Received admin notification: " . json_encode($data));
                $this->processAdminNotification($data);
            } else {
                $this->log("Received unknown message type: " . ($data['type'] ?? 'undefined'), "warning");
            }
        } catch (\Exception $e) {
            $this->log("Error processing message: " . $e->getMessage(), "error");
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Error processing your request'
            ]));
        }
    }

    protected function handleAuthentication(ConnectionInterface $from, $data)
    {
        try {
            $userID = AuthMiddleware::validateToken($data['userID'])->id;

            if (is_null($userID)) {
                $from->send(json_encode([
                    'type' => 'auth_error',
                    'message' => 'Authentication failed'
                ]));
                $from->close();
                return;
            }

            $this->userConnections[$userID][] = $from;
            $this->log("User {$userID} authenticated on connection {$from->resourceId}");

            $from->send(json_encode([
                'type' => 'auth_success',
                'message' => 'Authentication successful'
            ]));
        } catch (\Exception $e) {
            $this->log("Authentication error: " . $e->getMessage(), "error");
            $from->send(json_encode([
                'type' => 'auth_error',
                'message' => 'Authentication failed'
            ]));
            $from->close();
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->lastPings[$conn->resourceId]);

        foreach ($this->userConnections as $userID => $connections) {
            foreach ($connections as $index => $connection) {
                if ($connection === $conn) {
                    unset($this->userConnections[$userID][$index]);
                    $this->userConnections[$userID] = array_values($this->userConnections[$userID]);

                    if (empty($this->userConnections[$userID])) {
                        unset($this->userConnections[$userID]);
                    }
                    break;
                }
            }
        }

        $this->log("Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->log("An error has occurred: {$e->getMessage()}", "error");
        $conn->close();
    }

    public function notifyNewMessage($messageData, $channelID, $accessibleUsers)
    {
        $notification = json_encode([
            'type' => 'new_message',
            'channelID' => $channelID,
            'message' => $messageData
        ]);

        $this->notificationQueue->add($notification, $accessibleUsers);

        $processed = $this->notificationQueue->process($this->userConnections);

        $this->log("Queued notification for {$channelID} to " . count($accessibleUsers) . " users. Processed {$processed} items.");
        return count($accessibleUsers);
    }

    public function processAdminNotification($data)
    {
        if (isset($data['action']) && $data['action'] === 'new_message') {
            $result = $this->notifyNewMessage(
                $data['messageData'],
                $data['channelID'],
                $data['accessibleUsers']
            );
            return true;
        }
        $this->log("Unknown admin notification action: " . ($data['action'] ?? 'undefined'), "warning");
        return false;
    }

    public function sendHeartbeat()
    {
        $now = time();
        $timedOutConnections = [];

        foreach ($this->lastPings as $resourceId => $lastPing) {
            if ($now - $lastPing > 30) {
                foreach ($this->clients as $conn) {
                    if ($conn->resourceId == $resourceId) {
                        $timedOutConnections[] = $conn;
                        $this->log("Connection {$resourceId} timed out", "warning");
                        break;
                    }
                }
            }
        }

        foreach ($timedOutConnections as $conn) {
            $conn->close();
        }

        foreach ($this->clients as $conn) {
            $conn->send(json_encode([
                'type' => 'ping',
                'timestamp' => $now
            ]));
        }

        $this->log("Sent heartbeat to " . count($this->clients) . " connections");
        return count($timedOutConnections);
    }

    public function processNotificationQueue()
    {
        $processed = $this->notificationQueue->process($this->userConnections);
        $this->log("Processed {$processed} notifications from queue");
        return $processed;
    }
}
