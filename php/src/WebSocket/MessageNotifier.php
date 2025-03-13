<?php

namespace WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessageNotifier implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (isset($data['type']) && $data['type'] === 'auth' && isset($data['userID'])) {
            $userID = $data['userID'];
            $this->userConnections[$userID][] = $from;
            echo "User {$userID} authenticated on connection {$from->resourceId}\n";

            $from->send(json_encode([
                'type' => 'auth_success',
                'message' => 'Authentication successful'
            ]));
        } else if (isset($data['type']) && $data['type'] === 'admin_notification') {
            echo "Received admin notification: " . json_encode($data) . "\n";
            $this->processAdminNotification($data);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        foreach ($this->userConnections as $userID => $connections) {
            foreach ($connections as $index => $connection) {
                if ($connection === $conn) {
                    unset($this->userConnections[$userID][$index]);
                    $this->userConnections[$userID] = array_values($this->userConnections[$userID]);

                    if (empty($this->userConnections[$userID])) {
                        unset($this->userConnections[$userID]);
                    }
                    break;
                }
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function notifyNewMessage($messageData, $channelID, $accessibleUsers)
    {
        $notification = json_encode([
            'type' => 'new_message',
            'channelID' => $channelID,
            'message' => $messageData
        ]);

        foreach ($accessibleUsers as $userID) {
            if (isset($this->userConnections[$userID])) {
                foreach ($this->userConnections[$userID] as $conn) {
                    $conn->send($notification);
                }
            }
        }
    }

    public function processAdminNotification($data)
    {
        if (isset($data['action']) && $data['action'] === 'new_message') {
            $this->notifyNewMessage(
                $data['messageData'],
                $data['channelID'],
                $data['accessibleUsers']
            );
            return true;
        }
        return false;
    }
}
