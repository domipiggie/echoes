<?php
class User
{
    private $conn;
    private $table_name = "users";

    public $id;
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
                    password = :password";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));

        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $password_hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function emailExists()
    {
        $query = "SELECT id, email, password
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->password = $row['password'];
            return true;
        }
        return false;
    }

    public function getById()
    {
        $query = "SELECT id, email
                FROM " . $this->table_name . "
                WHERE id = :id
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return array(
                'id' => $row['id'],
                'email' => $row['email']
            );
        }
        return false;
    }
}