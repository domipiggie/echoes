<?php
class DatabaseConnectionManager
{
    private static $instance = null;
    private $connection = null;
    private $config = null;
    
    private function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    public static function getInstance($config = null)
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new Exception("Configuration required for initial connection");
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    private function connect()
    {
        $this->connection = new PDO(
            "mysql:host={$this->config['host']};dbname={$this->config['db']};charset=utf8mb4",
            $this->config['user'],
            $this->config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            ]
        );
    }
    
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connect();
            return $this->connection;
        }
        
        try {
            $this->connection->query("SELECT 1");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 2006) {
                $this->connect();
            } else {
                throw $e;
            }
        }
        
        return $this->connection;
    }
}