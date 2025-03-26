<?php

namespace WebSocket\NotificationHandlers;

class MessageNotificationHandler implements NotificationHandlerInterface
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
        $notification = json_encode([
            'type' => 'new_message',
            'channelID' => $data['channelID'] ?? null,
            'message' => $data
        ]);
        
        $sent = 0;
        foreach ($recipients as $userID) {
            if (isset($userConnections[$userID])) {
                foreach ($userConnections[$userID] as $conn) {
                    try {
                        $conn->send($notification);
                        $sent++;
                    } catch (\Exception $e) {
                        $this->log("Failed to send message notification to user {$userID}: " . $e->getMessage(), "error");
                    }
                }
            }
        }
        
        $this->log("Sent message notification to {$sent} connections");
        return $sent > 0;
    }
}