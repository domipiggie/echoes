<?php

namespace WebSocket;

class NotificationQueue
{
    protected $queue = [];
    protected $processing = false;
    protected $batchSize;
    protected $logger;
    
    public function __construct($batchSize = 50, $logger = null)
    {
        $this->batchSize = $batchSize;
        $this->logger = $logger;
    }
    
    public function add($notification, $recipients)
    {
        $this->queue[] = [
            'notification' => $notification,
            'recipients' => $recipients,
            'timestamp' => time()
        ];
        
        $this->log("Added notification to queue. Queue size: " . count($this->queue));
        return count($this->queue);
    }
    
    public function process($userConnections)
    {
        if ($this->processing || empty($this->queue)) {
            return 0;
        }
        
        $this->processing = true;
        $processed = 0;
        $failed = 0;
        
        $batch = array_splice($this->queue, 0, $this->batchSize);
        
        foreach ($batch as $item) {
            $notification = $item['notification'];
            $recipients = $item['recipients'];
            $sent = 0;
            
            foreach ($recipients as $userID) {
                if (isset($userConnections[$userID])) {
                    foreach ($userConnections[$userID] as $conn) {
                        try {
                            $conn->send($notification);
                            $sent++;
                        } catch (\Exception $e) {
                            $this->log("Failed to send notification to user {$userID}: " . $e->getMessage(), "error");
                            $failed++;
                        }
                    }
                }
            }
            
            $processed++;
            $this->log("Processed notification {$processed}/{$this->batchSize}: Sent to {$sent} connections");
        }
        
        $this->processing = false;
        $this->log("Queue processing complete. Processed: {$processed}, Failed: {$failed}, Remaining: " . count($this->queue));
        
        return $processed;
    }
    
    public function getQueueSize()
    {
        return count($this->queue);
    }
    
    public function clear()
    {
        $count = count($this->queue);
        $this->queue = [];
        $this->log("Queue cleared. Removed {$count} items.");
        return $count;
    }
    
    protected function log($message, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->$level($message);
        }
    }
}