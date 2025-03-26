<?php

namespace WebSocket;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'middleware' . DIRECTORY_SEPARATOR . 'AuthMiddleware.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'exceptions' . DIRECTORY_SEPARATOR . 'ApiException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'WebSocketException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'WebSocketErrorHandler.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'NotificationQueue.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'NotificationHandlers/NotificationHandlerInterface.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'NotificationHandlers/FriendRequestHandler.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'NotificationHandlers/MessageNotificationHandler.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'NotificationHandlers/AdminNotificationHandler.php';

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
    protected $notificationHandlers = [];

    public function __construct($logger = null)
    {
        $this->clients = new \SplObjectStorage;
        $this->rateLimiter = new RateLimiter();
        $this->notificationQueue = new NotificationQueue(50, $logger);
        $this->logger = $logger;
        $this->errorHandler = new WebSocketErrorHandler($logger);
        
        $this->registerNotificationHandlers();
        
        $this->log("MessageNotifier initialized");
    }
    
    protected function registerNotificationHandlers()
    {
        $this->notificationHandlers = [
            'friend_request' => new NotificationHandlers\FriendRequestHandler($this->logger),
            'new_message' => new NotificationHandlers\MessageNotificationHandler($this->logger),
            'admin_notification' => new NotificationHandlers\AdminNotificationHandler($this->logger)
        ];
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
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new WebSocketException("Invalid JSON received", 4000, "invalid_json");
            }
            
            if (!isset($data['type'])) {
                throw new WebSocketException("Message type not specified", 4001, "missing_type");
            }
            
            $this->handleClientMessage($from, $data);
        } catch (WebSocketException $e) {
            $this->errorHandler->handleException($e, $from);
        } catch (\Exception $e) {
            $this->errorHandler->handleException(
                new WebSocketException("Error processing message: " . $e->getMessage(), 4002, "message_error"),
                $from
            );
        }
    }
    
    protected function handleClientMessage(ConnectionInterface $conn, array $data)
    {
        switch ($data['type']) {
            case 'auth':
                $this->handleAuthentication($conn, $data);
                break;
            case 'ping':
                $this->handlePing($conn);
                break;
            default:
                throw new WebSocketException("Unknown message type: {$data['type']}", 4003, "unknown_type");
        }
    }
    
    protected function handleAuthentication(ConnectionInterface $conn, array $data)
    {
        if (!isset($data['token'])) {
            throw new WebSocketException("Authentication token not provided", 4010, "auth_error");
        }
        
        $token = $data['token'];
        $authMiddleware = new AuthMiddleware();
        $userID = $authMiddleware->validateToken($token)->id;
        
        if (!$userID) {
            throw new WebSocketException("Invalid authentication token", 4011, "auth_error", true);
        }
        
        $userID = (int)$userID;
        
        $conn->userID = $userID;
        
        $this->log("Authenticating user ID: {$userID}", "debug");
        
        if (!isset($this->userConnections)) {
            $this->userConnections = [];
        }
        
        if (!isset($this->userConnections[$userID])) {
            $this->userConnections[$userID] = [];
        }
        
        $this->userConnections[$userID][$conn->resourceId] = $conn;
        
        $this->log("Connected users after authentication: " . json_encode(array_keys($this->userConnections)), "debug");
        
        $this->log("User {$userID} authenticated on connection {$conn->resourceId}");
        
        $conn->send(json_encode([
            'type' => 'auth_success',
            'userID' => $userID
        ]));
    }
    
    protected function handlePing(ConnectionInterface $conn)
    {
        $this->lastPings[$conn->resourceId] = time();
        $conn->send(json_encode(['type' => 'pong']));
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        if (isset($conn->userID)) {
            $userID = $conn->userID;
            
            if (isset($this->userConnections[$userID][$conn->resourceId])) {
                unset($this->userConnections[$userID][$conn->resourceId]);
                
                if (empty($this->userConnections[$userID])) {
                    unset($this->userConnections[$userID]);
                }
            }
            
            $this->log("Connection {$conn->resourceId} for user {$userID} has disconnected");
        } else {
            $this->log("Connection {$conn->resourceId} has disconnected");
        }
        
        unset($this->lastPings[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->errorHandler->handleException($e, $conn);
        $conn->close();
    }

    public function sendHeartbeat()
    {
        $currentTime = time();
        $timeoutConnections = [];
        
        foreach ($this->clients as $client) {
            try {
                if (isset($this->lastPings[$client->resourceId])) {
                    $lastPing = $this->lastPings[$client->resourceId];
                    
                    if ($currentTime - $lastPing > 120) {
                        $timeoutConnections[] = $client;
                        continue;
                    }
                }
                
                $client->send(json_encode(['type' => 'heartbeat']));
            } catch (\Exception $e) {
                $this->log("Error sending heartbeat: " . $e->getMessage(), "error");
                $timeoutConnections[] = $client;
            }
        }
        
        foreach ($timeoutConnections as $conn) {
            $this->log("Closing inactive connection: {$conn->resourceId}");
            $conn->close();
        }
    }

    public function processNotification($notificationData)
    {
        try {
            if (!isset($notificationData['type']) || !isset($notificationData['recipients'])) {
                throw new WebSocketException("Invalid notification format", 4001, "invalid_format");
            }
            
            $type = $notificationData['type'];
            $recipients = $notificationData['recipients'];
            
            $this->log("Currently connected users: " . json_encode(array_keys($this->userConnections)), "debug");
            $this->log("Notification recipients: " . json_encode($recipients), "debug");
            $this->log("Full notification data: " . json_encode($notificationData), "debug");
            
            $recipients = array_map('intval', $recipients);
            
            static $processedHashes = [];
            $contentHash = md5(json_encode($notificationData));
            
            if (isset($processedHashes[$contentHash])) {
                $this->log("Skipping duplicate notification with hash: {$contentHash}");
                return;
            }
            
            $processedHashes[$contentHash] = true;
            
            if ($type === 'friend_request') {
                if (isset($this->notificationHandlers['friend_request'])) {
                    $result = $this->notificationHandlers['friend_request']->handle($notificationData['data'], $recipients, $this->userConnections);
                    $this->log("Friend request notification handled with result: " . ($result ? "success" : "failure"));
                } else {
                    $this->log("Friend request handler not registered", "error");
                }
            } else {
                $this->log("Unknown notification type: {$type}", "warning");
            }
        } catch (WebSocketException $e) {
            $this->errorHandler->handleException($e);
        } catch (\Exception $e) {
            $this->errorHandler->handleException(
                new WebSocketException("Error processing notification: " . $e->getMessage(), 4002, "notification_error")
            );
        }
    }

    public function processAdminNotification($data)
    {
        if (isset($data['action'])) {
            if (isset($this->notificationHandlers['admin_notification'])) {
                return $this->notificationHandlers['admin_notification']->handle($data, [], $this->userConnections);
            }
        }
        
        $this->log("Unknown admin notification action: " . ($data['action'] ?? 'undefined'), "warning");
        return false;
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
}
