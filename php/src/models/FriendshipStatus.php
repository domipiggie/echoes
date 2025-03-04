<?php
class FriendshipStatus
{
    private $dbConn;
    private $status_table = 'friendshipStatus';
    private $statusID;
    private $initiator;
    private $status;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function loadFromDB($statusID)
    {
        $query = "SELECT * FROM " . $this->status_table ."
                WHERE 
                    statusID = :statusID";
            
        $dbStmt = $this->dbConn->prepare($query);
        $dbStmt->bindParam(':statusID', $statusID);
        $dbStmt->execute();
        if ($dbStmt->rowCount() > 0) {
            $row = $dbStmt->fetch(PDO::FETCH_ASSOC);
            $this->statusID = $row['statusID'];
            $this->initiator = $row['initiator'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function createNewEntry($initiator)
    {
        $this->initiator = $initiator;
        $sql = "INSERT INTO " . $this->status_table . "
                SET
                    initiator = :initiator,
                    status = 0;";

        $dbStmt = $this->dbConn->prepare($sql);

        $dbStmt->bindParam(':initiator', $this->initiator);

        $dbStmt->execute();

        $this->statusID = $this->dbConn->lastInsertId();
        $this->status = 0;
    }

    public function removeEntry()
    {
        $query = "DELETE FROM " . $this->status_table . "
                WHERE
                    statusID = :statusID";

        $dbStmt = $this->dbConn->prepare($query);
        $dbStmt->bindParam(':statusID', $this->statusID);
        $dbStmt->execute();
    }

    public function updateStatus($status)
    {
        $query = "UPDATE " . $this->status_table . "
                SET
                    status = :newStatus
                WHERE
                    statusID = :statusID";
                
        $dbStmt = $this->dbConn->prepare($query);
        $dbStmt->bindParam(':newStatus', $status);
        $dbStmt->bindParam(':statusID', $this->statusID);
        $dbStmt->execute();
        if ($dbStmt->rowCount() > 0){
            return true;
        }
        return false;
    }

    //getters
    public function getStatusID()
    {
        return $this->statusID;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    public function getStatus()
    {
        return $this->status;
    }
}