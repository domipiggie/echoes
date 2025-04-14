<?php

namespace WebSocket\Services;

use Ratchet\ConnectionInterface;
use Utils\Logger;

class ErrorHandlerService
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function handleException(ConnectionInterface $connection, \Exception $e, $context = '')
    {
        if ($e instanceof \WebSocketException) {
            $response = $e->getErrorResponse();
            $connection->send(json_encode($response));
            
            $this->logger->error("WebSocket error ({$context}): {$e->getMessage()} [Code: {$e->getErrorCode()}]");
        } else {
            $response = [
                'type' => 'error',
                'message' => 'An internal error occurred',
                'code' => 'INTERNAL_ERROR ' . $e->getMessage(),
                'status_code' => 500
            ];
            $connection->send(json_encode($response));
            
            $this->logger->error("WebSocket exception ({$context}): {$e->getMessage()}");
        }
    }

    public function sendError(ConnectionInterface $connection, $message, $code = 'INTERNAL_ERROR', $statusCode = 500)
    {
        $response = [
            'type' => 'error',
            'message' => $message,
            'code' => $code,
            'status_code' => $statusCode
        ];
        
        $connection->send(json_encode($response));
        $this->logger->error("WebSocket error sent: {$message} [Code: {$code}]");
    }

    public function validateRequest(ConnectionInterface $connection, $data, $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $this->sendError(
                    $connection,
                    "Missing required field: {$field}",
                    'INVALID_REQUEST',
                    400
                );
                return false;
            }
        }
        
        return true;
    }
}