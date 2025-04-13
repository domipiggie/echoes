<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/core.php';
require_once __DIR__ . '/../config/WebSocketConfig.php';
require_once __DIR__ . '/../exceptions/ApiException.php';
require_once __DIR__ . '/../utils/JWTTools.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/DatabaseOperations.php';
require_once __DIR__ . '/../models/Friendship.php';
require_once __DIR__ . '/../models/FriendshipStatus.php';
require_once __DIR__ . '/../models/Message.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\SocketServer;
use WebSocket\Server;
use Utils\Logger;

$options = getopt('', ['ssl']);
$useSSL = isset($options['ssl']);

$logger = new Logger();
$logger->info("Starting WebSocket server" . ($useSSL ? " with SSL" : ""));

$loop = Factory::create();

$webSocket = new Server();

$loop->addPeriodicTimer(1, function () use ($webSocket) {
    $webSocket->checkConnections();
});

$wsServer = new WsServer($webSocket);
$httpServer = new HttpServer($wsServer);

if ($useSSL) {
    if (empty(\Config\WebSocketConfig::SSL_CERT_PATH) || empty(\Config\WebSocketConfig::SSL_KEY_PATH)) {
        $logger->error("SSL certificate or key path not configured. Check WebSocketConfig.php");
        exit(1);
    }

    $context = [
        'tls' => [
            'local_cert' => \Config\WebSocketConfig::SSL_CERT_PATH,
            'local_pk' => \Config\WebSocketConfig::SSL_KEY_PATH,
            'verify_peer' => false
        ]
    ];

    $socketServer = new SocketServer(
        'tls://' . \Config\WebSocketConfig::HOST . ':' . \Config\WebSocketConfig::SECURE_PORT,
        ['tls' => $context],
        $loop
    );

    $logger->info("SSL WebSocket server running on wss://" . \Config\WebSocketConfig::HOST . ":" . \Config\WebSocketConfig::SECURE_PORT);
} else {
    $socketServer = new SocketServer(
        \Config\WebSocketConfig::HOST . ':' . \Config\WebSocketConfig::PORT,
        [],
        $loop
    );

    $logger->info("WebSocket server running on ws://" . \Config\WebSocketConfig::HOST . ":" . \Config\WebSocketConfig::PORT);
}

$server = new IoServer($httpServer, $socketServer, $loop);

$server->run();
