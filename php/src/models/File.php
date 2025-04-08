<?php

class File
{
    private $conn;
    private $table_name = "file";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function saveFile($userId, $originalName, $uniqueName, $size)
    {
        $query = "INSERT INTO " . $this->table_name . " (userID, file_name, unique_name, size) VALUES (:user_id, :original_name, :unique_name, :size)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':original_name', $originalName);
        $stmt->bindParam(':unique_name', $uniqueName);
        $stmt->bindParam(':size', $size);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function getFileById($fileId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE fileID = :file_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':file_id', $fileId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}