<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/core.php';
require_once __DIR__ . '/../config/WebSocketConfig.php';
require_once __DIR__ . '/../exceptions/ApiException.php';
require_once __DIR__ . '/../exceptions/WebSocketException.php';
require_once __DIR__ . '/../utils/JWTTools.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/DatabaseOperations.php';
require_once __DIR__ . '/../models/Friendship.php';
require_once __DIR__ . '/../models/FriendshipStatus.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Channel.php';
require_once __DIR__ . '/../utils/DatabaseOperations.php';
require_once __DIR__ . '/../models/Group.php';
require_once __DIR__ . '/../models/User.php';

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

    $certPath = \Config\WebSocketConfig::SSL_CERT_PATH;
    $keyPath = \Config\WebSocketConfig::SSL_KEY_PATH;
    
    $logger->info("Using Let's Encrypt certificates");
    $logger->info("Certificate path (fullchain.pem): " . $certPath);
    $logger->info("Private key path (privkey.pem): " . $keyPath);
    
    if (!file_exists($certPath)) {
        $logger->error("SSL certificate file not found: " . $certPath);
        exit(1);
    }
    
    if (!is_readable($certPath)) {
        $logger->error("SSL certificate file not readable: " . $certPath);
        exit(1);
    }
    
    if (!file_exists($keyPath)) {
        $logger->error("SSL key file not found: " . $keyPath);
        exit(1);
    }
    
    if (!is_readable($keyPath)) {
        $logger->error("SSL key file not readable: " . $keyPath);
        exit(1);
    }
    
    $certData = file_get_contents($certPath);
    $certInfo = openssl_x509_parse($certData);
    
    if ($certInfo) {
        $logger->info("Certificate subject: " . $certInfo['subject']['CN']);
        $logger->info("Certificate valid until: " . date('Y-m-d H:i:s', $certInfo['validTo_time_t']));
        
        if (time() > $certInfo['validTo_time_t']) {
            $logger->warning("Certificate has expired!");
        }
    } else {
        $logger->warning("Could not parse certificate information");
    }
    
    $context = [
        'tls' => [
            'local_cert' => $certPath,
            'local_pk' => $keyPath,
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false,
            'SNI_enabled' => true,
            'disable_compression' => true,
            'ciphers' => 'HIGH:!aNULL:!MD5'
        ]
    ];

    try {
        $socketServer = new SocketServer(
            'tls://' . \Config\WebSocketConfig::HOST . ':' . \Config\WebSocketConfig::SECURE_PORT,
            $context,
            $loop
        );
        $logger->info("SSL WebSocket server running on wss://" . \Config\WebSocketConfig::HOST . ":" . \Config\WebSocketConfig::SECURE_PORT);
    } catch (\Exception $e) {
        $logger->error("Failed to create SSL socket server: " . $e->getMessage());
        $logger->error("Stack trace: " . $e->getTraceAsString());
        exit(1);
    }
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
