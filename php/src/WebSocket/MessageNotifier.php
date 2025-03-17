<?php

namespace WebSocket;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'middleware' . DIRECTORY_SEPARATOR . 'AuthMiddleware.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'exceptions' . DIRECTORY_SEPARATOR . 'ApiException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'WebSocketException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'WebSocketErrorHandler.php';

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
    protected $errorHandler;

    public function __construct($logger = null)
    {
        $this->clients = new \SplObjectStorage;
        $this->rateLimiter = new RateLimiter();
        $this->notificationQueue = new NotificationQueue(50, $logger);
        $this->logger = $logger;
        $this->log("MessageNotifier initialized");
        $this->errorHandler = new WebSocketErrorHandler($logger);
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
            $this->rateLimiter->isAllowed($from->resourceId);
            
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new WebSocketException("Invalid JSON received", 4000, "invalid_format");
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
                throw new WebSocketException("Unknown message type: " . ($data['type'] ?? 'undefined'), 4001, "unknown_type");
            }
        } catch (WebSocketException $e) {
            $this->errorHandler->handleException($e, $from);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, $from);
        }
    }

    protected function handleAuthentication(ConnectionInterface $from, $data)
    {
        try {
            $userID = AuthMiddleware::validateToken($data['userID'])->id;
    
            if (is_null($userID)) {
                throw new WebSocketException(
                    "Authentication failed", 
                    4003, 
                    "auth_error",
                    true
                );
            }

            $this->userConnections[$userID][] = $from;
            $from->send(json_encode([
                'type' => 'auth_success',
                'message' => 'Authentication successful'
            ]));
            
        } catch (WebSocketException $e) {
            $this->errorHandler->handleException($e, $from);
        } catch (\Exception $e) {
            $this->errorHandler->handleException(
                new WebSocketException(
                    "Authentication error: " . $e->getMessage(), 
                    4003, 
                    "auth_error",
                    true
                ), 
                $from
            );
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
        try {
            $now = time();
            foreach ($this->clients as $client) {
                $client->send(json_encode([
                    'type' => 'ping',
                    'timestamp' => $now
                ]));
            }
        } catch (\Exception $e) {
            $this->log("Error sending heartbeat: " . $e->getMessage(), "error");
        }
    }
}
