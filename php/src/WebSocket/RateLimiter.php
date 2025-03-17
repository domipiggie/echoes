<?php

namespace WebSocket;

class RateLimiter
{
    protected $limits = [];
    protected $windowSize;
    protected $maxRequests;
    
    public function __construct($windowSize = 60, $maxRequests = 100)
    {
        $this->windowSize = $windowSize;
        $this->maxRequests = $maxRequests;
    }
    
    public function isAllowed($identifier)
    {
        $now = time();
        
        if (!isset($this->limits[$identifier])) {
            $this->limits[$identifier] = [
                'count' => 0,
                'window_start' => $now
            ];
            return true;
        }
        
        if ($now - $this->limits[$identifier]['window_start'] > $this->windowSize) {
            $this->limits[$identifier] = [
                'count' => 1,
                'window_start' => $now
            ];
            return true;
        }
        
        $this->limits[$identifier]['count']++;
        
        if ($this->limits[$identifier]['count'] > $this->maxRequests) {
            throw new WebSocketException(
                'Rate limit exceeded. Please slow down.',
                4029, // Custom code for rate limiting
                'rate_limit_exceeded'
            );
        }
        
        return true;
    }
    
    public function getRemainingLimit($identifier)
    {
        if (!isset($this->limits[$identifier])) {
            return $this->maxRequests;
        }
        
        $remaining = $this->maxRequests - $this->limits[$identifier]['count'];
        return $remaining > 0 ? $remaining : 0;
    }
}