<?php
class FriendshipStatus
{
    private $conn;
    private $status_table = 'friendshipStatus';
    public $statusID;
    public $initiator;
    public $status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function loadFromRow($row)
    {
        $this->statusID = $row['statusID'];
        $this->initiator = $row['initiator'];
        $this->status = $row['status'];
    }

    public function createNewEntry($initiator)
    {
        $this->initiator = $initiator;
        $sql = "INSERT INTO " . $this->status_table . "
                SET
                    initiator = :initiator,
                    status = 0;";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':initiator', $this->initiator);

        $stmt->execute();

        $this->statusID = $this->conn->lastInsertId();
        $this->status = 0;
    }
}