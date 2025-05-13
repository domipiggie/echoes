<?php

namespace WebSocket\Services;

use Ratchet\ConnectionInterface;

class ResponseHandlerService
{
    public static function sendSuccess(ConnectionInterface $connection, string $type, array $data = [])
    {
        $response = array_merge([
            'type' => $type,
            'status' => 'success',
            'timestamp' => time()
        ], $data);
        
        $connection->send(json_encode($response));
    }

    public static function sendToClients(array $clients, string $type, array $data = [])
    {
        $response = array_merge([
            'type' => $type,
            'status' => 'success',
            'timestamp' => time()
        ], $data);
        
        $encodedResponse = json_encode($response);
        
        foreach ($clients as $client) {
            $client->send($encodedResponse);
        }
    }
}