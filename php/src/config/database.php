<?php
class Database
{
    public $host = "localhost";
    public $db_name = "echoes";
    public $username = "username";
    public $password = "password";
    private $conn;

    public function getConnection()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new ApiException('Connection failed: ' . $e->getMessage(), 500);
        }
        return $this->conn;
    }
}
