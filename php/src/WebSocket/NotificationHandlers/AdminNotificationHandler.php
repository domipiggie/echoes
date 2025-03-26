<?php

namespace WebSocket\NotificationHandlers;

class AdminNotificationHandler implements NotificationHandlerInterface
{
    protected $logger;
    
    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }
    
    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->$level($message);
        }
    }
    
    public function handle(array $data, array $recipients, array $userConnections): bool
    {
        if (isset($data['action']) && $data['action'] === 'new_message') {
            $messageHandler = new MessageNotificationHandler($this->logger);
            return $messageHandler->handle(
                $data['messageData'],
                $data['accessibleUsers'] ?? [],
                $userConnections
            );
        }
        
        $this->log("Unknown admin notification action: " . ($data['action'] ?? 'undefined'), "warning");
        return false;
    }
}