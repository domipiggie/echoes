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
        try {
            $query = "SELECT * FROM " . $this->status_table . "
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
            throw new ApiException('Failed to load friendship status', 500);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function createNewEntry($initiator)
    {
        try {
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
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function removeEntry()
    {
        try {
            $query = "DELETE FROM " . $this->status_table . "
        WHERE
            statusID = :statusID";

            $dbStmt = $this->dbConn->prepare($query);
            $dbStmt->bindParam(':statusID', $this->statusID);
            $dbStmt->execute();
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function updateStatus($status)
    {
        try {
            $query = "UPDATE " . $this->status_table . "
            SET
                status = :newStatus
            WHERE
                statusID = :statusID";

            $dbStmt = $this->dbConn->prepare($query);
            $dbStmt->bindParam(':newStatus', $status);
            $dbStmt->bindParam(':statusID', $this->statusID);
            $dbStmt->execute();
            if ($dbStmt->rowCount() > 0) {
                return true;
            }
            throw new ApiException('Failed to update friendship status', 500);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
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
