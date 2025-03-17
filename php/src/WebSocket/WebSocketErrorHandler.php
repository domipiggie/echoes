<?php

namespace WebSocket;

use Ratchet\ConnectionInterface;

class WebSocketErrorHandler
{
    protected $logger;

    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }

    public function handleException(\Exception $e, ConnectionInterface $conn = null)
    {
        if ($e instanceof WebSocketException) {
            $this->log("WebSocket error: {$e->getMessage()} (Code: {$e->getErrorCode()})", "error");
            
            if ($conn && method_exists($conn, 'send')) {
                try {
                    $conn->send(json_encode($e->toArray()));
                    
                    if ($e->shouldCloseConnection()) {
                        $this->log("Closing connection due to error: {$e->getMessage()}", "info");
                        $conn->close();
                    }
                } catch (\Exception $sendError) {
                    $this->log("Error sending error message: {$sendError->getMessage()}", "error");
                    if ($e->shouldCloseConnection()) {
                        $conn->close();
                    }
                }
            }
        } else {
            $this->log("Unexpected error: {$e->getMessage()}", "error");
            
            if ($conn && method_exists($conn, 'send')) {
                try {
                    $conn->send(json_encode([
                        'type' => 'error',
                        'code' => 1011, // Internal server error WebSocket code
                        'message' => 'An unexpected error occurred'
                    ]));
                } catch (\Exception $sendError) {
                    $this->log("Error sending error message: {$sendError->getMessage()}", "error");
                }
            }
        }
    }

    protected function log($message, $level = 'info')
    {
        if ($this->logger && method_exists($this->logger, $level)) {
            $this->logger->$level($message);
        }
    }
}