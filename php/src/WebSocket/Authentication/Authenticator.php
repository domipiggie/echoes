<?php

namespace WebSocket\Authentication;

use Ratchet\ConnectionInterface;
use Utils\Logger;

class Authenticator
{
    protected $logger;
    protected $authTimeout;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->authTimeout = \Config\WebSocketConfig::AUTH_TIMEOUT;
    }

    public function initiateAuthentication(ConnectionInterface $conn)
    {
        $conn->authDeadline = time() + $this->authTimeout;
        $conn->isAuthenticated = false;

        $conn->send(json_encode([
            'type' => 'auth_required',
            'message' => 'Please authenticate with your JWT token'
        ]));

        $this->logger->debug("Authentication initiated for connection {$conn->resourceId}");
    }

    public function authenticate(ConnectionInterface $conn, $data)
    {
        try {
            if (!isset($data['token'])) {
                $this->sendAuthError($conn, 'No token provided');
                return false;
            }

            $userData = \JWTTools::validateToken($data['token']);

            if ($userData) {
                $conn->isAuthenticated = true;
                $conn->userData = $userData;
                $conn->lastActivity = time();
                $conn->lastPongTime = time();

                $conn->send(json_encode([
                    'type' => 'auth_success',
                    'message' => 'Authentication successful',
                    'user' => [
                        'id' => $userData->id,
                        'username' => $userData->username
                    ]
                ]));

                $this->logger->info("User {$userData->username} (ID: {$userData->id}) authenticated on connection {$conn->resourceId}");
                return true;
            }
        } catch (\ApiException $e) {
            $this->sendAuthError($conn, $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Authentication error: " . $e->getMessage());
            $this->sendAuthError($conn, 'Authentication failed');
        }

        return false;
    }

    public function isAuthenticated(ConnectionInterface $conn)
    {
        return isset($conn->isAuthenticated) && $conn->isAuthenticated === true;
    }

    public function checkAuthTimeout(ConnectionInterface $conn, $currentTime)
    {
        if (!$this->isAuthenticated($conn) && isset($conn->authDeadline) && $currentTime > $conn->authDeadline) {
            $this->logger->warning("Authentication timeout for connection {$conn->resourceId}");
            $conn->send(json_encode([
                'type' => 'auth_timeout',
                'message' => 'Authentication timeout'
            ]));
            $conn->close();
            return true;
        }

        return false;
    }

    protected function sendAuthError(ConnectionInterface $conn, $message)
    {
        $conn->send(json_encode([
            'type' => 'auth_error',
            'message' => $message
        ]));

        $this->logger->warning("Authentication failed for connection {$conn->resourceId}: {$message}");
    }
}
