<?php
class RefreshToken
{
    private $conn;
    private $table_name = "refresh_token";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($userID)
    {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

        $query = "INSERT INTO " . $this->table_name . "
                (userID, token, expires_at)
                VALUES (:userID, :token, :expires_at)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":userID", $userID);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expires_at", $expires_at);

        if ($stmt->execute()) {
            return [
                'token' => $token,
                'expires_at' => $expires_at
            ];
        }
        return false;
    }

    public function validate($token)
    {
        $query = "SELECT userID, expires_at, revoked 
                 FROM " . $this->table_name . "
                 WHERE token = :token
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['revoked']) {
                throw new Exception("Refresh token has been revoked");
            }

            if (strtotime($row['expires_at']) < time()) {
                throw new Exception("Refresh token has expired");
            }

            return $row['userID'];
        }
        throw new Exception("Invalid refresh token");
    }

    public function revoke($token)
    {
        $query = "UPDATE " . $this->table_name . "
                 SET revoked = true
                 WHERE token = :token";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        return $stmt->execute();
    }
}