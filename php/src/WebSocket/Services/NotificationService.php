<?php

namespace WebSocket\Services;

use Ratchet\ConnectionInterface;
use Utils\Logger;

class NotificationService
{
    protected $clients;
    protected $logger;

    public function __construct(\SplObjectStorage $clients, Logger $logger)
    {
        $this->clients = $clients;
        $this->logger = $logger;
    }

    public function notifyClient($clientID, $type, $data = [])
    {
        $client = $this->findRecipient($clientID);

        if ($client) {
            $responseData = array_merge([
                'type' => $type,
                'timestamp' => time()
            ], $data);
    
            $client->send(json_encode($responseData));
        }

        $this->logger->debug("Recipient {$clientID} is offline, no real-time notification sent");
    }

    public function notifyMultipleClients(array $clientIds, string $senderId, string $type, array $data = [])
    {
        foreach ($clientIds as $clientID) {
            $this->notifyClient($clientID, $type, $data);
        }
    }
    
    protected function findRecipient($recipientId)
    {
        foreach ($this->clients as $client) {
            if (isset($client->userData) && $client->userData->id == $recipientId) {
                return $client;
            }
        }

        return null;
    }
}
