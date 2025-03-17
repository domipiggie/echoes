<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require dirname(__DIR__, 2) . '/vendor/autoload.php';

require_once __DIR__ . '/MessageNotifier.php';
require_once __DIR__ . '/WebSocketException.php';
require_once __DIR__ . '/WebSocketErrorHandler.php';
require_once __DIR__ . '/../utils/Logger.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use WebSocket\MessageNotifier;
use WebSocket\WebSocketException;
use WebSocket\WebSocketErrorHandler;
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
    protected $errorHandler;
    
    public function __construct($logger = null) {
        $this->logger = $logger ?: new Logger();
        $this->loop = Loop::get();
        $this->notificationDir = __DIR__ . '/notifications';
        $this->errorHandler = new WebSocketErrorHandler($this->logger);
        
        if (!is_dir($this->notificationDir)) {
            try {
                mkdir($this->notificationDir, 0755, true);
            } catch (\Exception $e) {
                $this->errorHandler->handleException(
                    new WebSocketException("Failed to create notification directory: " . $e->getMessage(), 5003, "server_error")
                );
                exit(1);
            }
        }
    }
    
    public function start($secure = false, $port = 8080) {
        try {
            $this->logger->info("Starting WebSocket server...");
            
            $this->messageNotifier = new MessageNotifier($this->logger);
            $wsServer = new WsServer($this->messageNotifier);
            $httpServer = new HttpServer($wsServer);
            
            if ($secure) {
                $certPath = __DIR__ . '/../../ssl/certificate.pem';
                $keyPath = __DIR__ . '/../../ssl/private_key.pem';
                
                if (!file_exists($certPath) || !file_exists($keyPath)) {
                    throw new WebSocketException(
                        "SSL certificate or key not found at {$certPath} or {$keyPath}", 
                        5004, 
                        "ssl_error"
                    );
                }
                
                $socket = new SocketServer('0.0.0.0:' . $port);
                $secureSocket = new SecureServer($socket, $this->loop, [
                    'local_cert' => $certPath,
                    'local_pk' => $keyPath,
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
        } catch (WebSocketException $e) {
            $this->errorHandler->handleException($e);
            exit(1);
        } catch (\Exception $e) {
            $this->errorHandler->handleException(
                new WebSocketException("Server error: " . $e->getMessage(), 5000, "server_error")
            );
            exit(1);
        }
    }
    
    protected function setupPeriodicTasks() {
        $this->loop->addPeriodicTimer(1, function () {
            try {
                $files = glob($this->notificationDir . '/*.json');
                foreach ($files as $file) {
                    try {
                        $content = file_get_contents($file);
                        if ($content === false) {
                            throw new WebSocketException("Failed to read notification file: " . basename($file), 5005, "file_error");
                        }
                        
                        $data = json_decode($content, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new WebSocketException("Invalid JSON in notification file: " . basename($file), 5006, "json_error");
                        }

                        if ($data && isset($data['type']) && $data['type'] === 'admin_notification') {
                            $this->logger->info("Processing notification from file: " . basename($file));
                            $this->messageNotifier->processAdminNotification($data);
                        }

                        unlink($file);
                    } catch (WebSocketException $e) {
                        $this->errorHandler->handleException($e);
                        @unlink($file);
                    } catch (\Exception $e) {
                        $this->errorHandler->handleException(
                            new WebSocketException("Error processing notification file: " . $e->getMessage(), 5007, "notification_error")
                        );
                        
                        @unlink($file);
                    }
                }
            } catch (\Exception $e) {
                $this->errorHandler->handleException(
                    new WebSocketException("Error in notification processing: " . $e->getMessage(), 5008, "task_error")
                );
            }
        });
        
        $this->loop->addPeriodicTimer(30, function() {
            try {
                $this->logger->debug("Sending heartbeat to clients");
                $this->messageNotifier->sendHeartbeat();
            } catch (\Exception $e) {
                $this->errorHandler->handleException(
                    new WebSocketException("Error in heartbeat: " . $e->getMessage(), 5009, "heartbeat_error")
                );
            }
        });
    }
}

// Main execution
try {
    $options = getopt('sp:', ['secure', 'port:']);
    $secure = isset($options['s']) || isset($options['secure']);
    $port = isset($options['p']) ? (int)$options['p'] : (isset($options['port']) ? (int)$options['port'] : 8080);

    $logger = new Logger();
    $errorHandler = new WebSocketErrorHandler($logger);

    $manager = new ServerManager($logger);
    $manager->start($secure, $port);
} catch (WebSocketException $e) {
    $errorHandler->handleException($e);
    exit(1);
} catch (\Exception $e) {
    $errorHandler = new WebSocketErrorHandler(new Logger());
    $errorHandler->handleException(
        new WebSocketException("Fatal server error: " . $e->getMessage(), 5010, "fatal_error")
    );
    exit(1);
}
