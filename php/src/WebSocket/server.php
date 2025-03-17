<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require dirname(__DIR__, 2) . '/vendor/autoload.php';

require_once __DIR__ . '/MessageNotifier.php';
require_once __DIR__ . '/../utils/Logger.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use WebSocket\MessageNotifier;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Socket\SecureServer;

class Logger {
    public function info($message) {
        echo date('[Y-m-d H:i:s]') . " INFO: {$message}\n";
    }
    
    public function debug($message) {
        echo date('[Y-m-d H:i:s]') . " DEBUG: {$message}\n";
    }
    
    public function warning($message) {
        echo date('[Y-m-d H:i:s]') . " WARNING: {$message}\n";
    }
    
    public function error($message) {
        echo date('[Y-m-d H:i:s]') . " ERROR: {$message}\n";
    }
}

class ServerManager {
    protected $server;
    protected $loop;
    protected $messageNotifier;
    protected $logger;
    protected $notificationDir;
    
    public function __construct($logger = null) {
        $this->logger = $logger ?: new Logger();
        $this->loop = Loop::get();
        $this->notificationDir = __DIR__ . '/notifications';
        
        if (!is_dir($this->notificationDir)) {
            mkdir($this->notificationDir, 0755, true);
        }
    }
    
    public function start($secure = false, $port = 8080) {
        $this->logger->info("Starting WebSocket server...");
        
        $this->messageNotifier = new MessageNotifier();
        $wsServer = new WsServer($this->messageNotifier);
        $httpServer = new HttpServer($wsServer);
        
        if ($secure) {
            $socket = new SocketServer('0.0.0.0:' . $port);
            $secureSocket = new SecureServer($socket, $this->loop, [
                'local_cert' => __DIR__ . '/../../ssl/certificate.pem',
                'local_pk' => __DIR__ . '/../../ssl/private_key.pem',
                'allow_self_signed' => true,
                'verify_peer' => false
            ]);
            $this->server = new IoServer($httpServer, $secureSocket, $this->loop);
            $this->logger->info("Secure WebSocket server started on port {$port}");
        } else {
            $socket = new SocketServer('0.0.0.0:' . $port);
            $this->server = new IoServer($httpServer, $socket, $this->loop);
            $this->logger->info("WebSocket server started on port {$port}");
        }
        
        $this->setupPeriodicTasks();
        
        $this->loop->run();
    }
    
    protected function setupPeriodicTasks() {
        $this->loop->addPeriodicTimer(1, function () {
            $files = glob($this->notificationDir . '/*.json');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $data = json_decode($content, true);

                if ($data && isset($data['type']) && $data['type'] === 'admin_notification') {
                    $this->logger->info("Processing notification from file: " . basename($file));
                    $this->messageNotifier->processAdminNotification($data);
                }

                unlink($file);
            }
        });
        
        $this->loop->addPeriodicTimer(30, function() {
            $this->logger->debug("Sending heartbeat to clients");
            $this->messageNotifier->sendHeartbeat();
        });
    }
}

$options = getopt('sp:', ['secure', 'port:']);
$secure = isset($options['s']) || isset($options['secure']);
$port = isset($options['p']) ? (int)$options['p'] : (isset($options['port']) ? (int)$options['port'] : 8080);

$logger = new Logger();

$manager = new ServerManager($logger);
$manager->start($secure, $port);
