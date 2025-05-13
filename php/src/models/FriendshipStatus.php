<?php
class FriendshipStatus
{
    private $status_table = 'friendshipStatus';
    private $statusID;
    private $initiator;
    private $status;

    public function loadFromDB($statusID)
    {
        try {
            $sql = "SELECT * FROM " . $this->status_table . "
                    WHERE 
                        statusID = :statusID";

            $args = [
                [':statusID', $statusID]
            ];

            $result = DatabaseOperations::fetchFromDB($sql, $args);

            if (count($result) > 0) {
                $row = $result[0];
                $this->statusID = $row['statusID'];
                $this->initiator = $row['initiator'];
                $this->status = $row['status'];
                return true;
            }
            throw new ApiException('Loading friendship status yielded no result', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to load friendship status ' . $e->getMessage(), 500);
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

            $args = [
                [':initiator', $this->initiator]
            ];

            $result = DatabaseOperations::insertIntoDB($sql, $args);

            if ($result) {
                $this->statusID = $result[1];
                $this->status = 0;
                return $this->statusID;
            }
            throw new ApiException('Creating new friendship status yielded no result', 500);
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException('Failed to create new friendship status ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus($status)
    {
        try {
            $sql = "UPDATE " . $this->status_table . "
                    SET
                        status = :newStatus
                    WHERE
                        statusID = :statusID";

            $args = [
                [':newStatus', $status],
                [':statusID', $this->statusID]
            ];

            $result = DatabaseOperations::updateDB($sql, $args);

            if ($result === true || (is_numeric($result) && $result > 0)) {
                $this->status = $status;
                return true;
            }
            throw new ApiException('Updating friendship status yielded no result', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update friendship status ' . $e->getMessage(), 500);
        }
    }

    public function updateInitiator($newInitiator)
    {
        try {
            $sql = "UPDATE " . $this->status_table . "
                    SET
                        initiator = :newInitiator
                    WHERE
                        statusID = :statusID";

            $args = [
                [':newInitiator', $newInitiator],
                [':statusID', $this->statusID]
            ];

            $result = DatabaseOperations::updateDB($sql, $args);

            if ($result === true || (is_numeric($result) && $result > 0)) {
                $this->initiator = $newInitiator;
                return true;
            }
            return false;
            throw new ApiException('Updating friendship initiator yielded no result', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update friendship initiator ' . $e->getMessage(), 500);
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
