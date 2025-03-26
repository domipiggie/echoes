<?php

namespace WebSocket\NotificationHandlers;

interface NotificationHandlerInterface
{
    /**
     * Handle a notification
     * 
     * @param array $data Notification data
     * @param array $recipients List of recipient user IDs
     * @param array $userConnections Map of user connections
     * @return bool Success status
     */
    public function handle(array $data, array $recipients, array $userConnections): bool;
}