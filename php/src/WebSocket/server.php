<?php
error_reporting(E_ALL & ~E_DEPRECATED);  // Add this line at the top
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use WebSocket\MessageNotifier;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

$messageNotifier = new MessageNotifier();
$wsServer = new WsServer($messageNotifier);
$httpServer = new HttpServer($wsServer);

$loop = Loop::get();

$socket = new SocketServer('0.0.0.0:8080');

$server = new IoServer($httpServer, $socket, $loop);

echo "WebSocket server started on port 8080\n";

$notificationDir = __DIR__ . '/notifications';
if (!is_dir($notificationDir)) {
    mkdir($notificationDir, 0755, true);
}

$loop->addPeriodicTimer(1, function () use ($messageNotifier, $notificationDir) {
    $files = glob($notificationDir . '/*.json');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if ($data && isset($data['type']) && $data['type'] === 'admin_notification') {
            echo "Processing notification from file: " . basename($file) . "\n";
            $messageNotifier->processAdminNotification($data);
        }

        unlink($file);
    }
});

$loop->run();
