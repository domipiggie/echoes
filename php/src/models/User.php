<?php
class User
{
    private $conn;
    private $table_name = "user";

    public $userID;
    public $username;
    public $email;
    public $password;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    email = :email,
                    username = :username,
                    password = :password";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = htmlspecialchars(strip_tags($this->password));

        $password_hash = hash('sha256', $this->password);

        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $password_hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getById()
    {
        $query = "SELECT userID, username, email, password
                FROM " . $this->table_name . "
                WHERE userID = :userID
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userID', $this->userID);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->userID = $row['userID'];
            $this->email = $row['email'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            return true;
        }
        return false;
    }

    public function getByEmail()
    {
        $query = "SELECT userID, username, email, password
                FROM " . $this->table_name . "
                WHERE email = :email
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->userID = $row['userID'];
            $this->email = $row['email'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            return true;
        }
        return false;
    }

    public function emailExists()
    {
        $query = "SELECT email
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        return $num > 0;
    }

    public function usernameExists()
    {
        $query = "SELECT username
                FROM " . $this->table_name . "
                WHERE username = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        $num = $stmt->rowCount();

        return $num > 0;
    }
}