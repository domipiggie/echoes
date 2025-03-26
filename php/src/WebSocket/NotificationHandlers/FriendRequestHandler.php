<?php

namespace WebSocket\NotificationHandlers;

class FriendRequestHandler implements NotificationHandlerInterface
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
        $this->log("Processing friend request notification: " . json_encode($data), "debug");
        $this->log("Recipients: " . json_encode($recipients), "debug");
        $this->log("User connections: " . json_encode(array_keys($userConnections)), "debug");
        
        $notification = json_encode([
            'type' => 'friend_request',
            'friendRequest' => $data
        ]);
        
        $sent = 0;
        foreach ($recipients as $userID) {
            $this->log("Checking recipient: {$userID}", "debug");
            
            if (isset($userConnections[$userID])) {
                $this->log("Found {$userID} in connections with " . count($userConnections[$userID]) . " active connections", "debug");
                
                foreach ($userConnections[$userID] as $conn) {
                    try {
                        $conn->send($notification);
                        $sent++;
                        $this->log("Successfully sent notification to user {$userID}", "debug");
                    } catch (\Exception $e) {
                        $this->log("Failed to send friend request notification to user {$userID}: " . $e->getMessage(), "error");
                    }
                }
            } else {
                $this->log("User {$userID} not connected", "debug");
            }
        }
        
        $this->log("Sent friend request notification to {$sent} connections");
        return $sent > 0;
    }
}