<?php
class Friendship
{
    private $friendship_table = 'friendship';
    private $dbConn;
    private $user1ID;
    private $user2ID;
    private $friendshipStatus;


    public function __construct($db, $user1ID, $user2ID)
    {
        $this->dbConn = $db;
        $this->user1ID = $user1ID;
        $this->user2ID = $user2ID;
    }

    public function doesFriendshipExist()
    {
        try {
            $sql = "SELECT * FROM " . $this->friendship_table . "
                    WHERE
                        user1ID = :user1ID AND user2ID = :user2ID
                        OR
                        user1ID = :user2ID AND user2ID = :user1ID";

            $args = [
                [':user1ID', $this->user1ID],
                [':user2ID', $this->user2ID]
            ];

            $result = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            echo json_encode($result);
            echo $this->user2ID;
            if (count($result) > 0) {
                if (!isset($this->friendshipStatus)) {
                    $this->friendshipStatus = new FriendshipStatus($this->dbConn);
                    $this->friendshipStatus->loadFromDB($result[0]['statusID']);
                }
                return true;
            }
            return false;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t check if friendship exists', 500);
        }
    }

    public function sendFriendRequest()
    {
        try {
            if ($this->doesFriendshipExist()) {
                if ($this->friendshipStatus->getStatus() == -1) {
                    return $this->friendshipStatus->updateStatus(0);
                }
                throw new ApiException('Friendship already exists with status: ' . $this->friendshipStatus->getStatus(), 400);
            }

            $this->friendshipStatus = new FriendshipStatus($this->dbConn);
            $statusID = $this->friendshipStatus->createNewEntry($this->user1ID);

            $sql = "INSERT INTO " . $this->friendship_table . " (user1ID, user2ID, statusID) 
                    VALUES
                        (:user1ID, :user2ID, :statusID)";

            $args = [
                [':user1ID', $this->user1ID],
                [':user2ID', $this->user2ID],
                [':statusID', $statusID]
            ];

            $result = DatabaseOperations::insertIntoDB($this->dbConn, $sql, $args);

            return $result[1];
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t send friend request: ' . $e->getMessage(), 500);
        }
    }

    public function declineFriendRequest()
    {
        try {
            if (!$this->doesFriendshipExist()) {
                throw new ApiException('Friendship doesn\'t exist!', 400);
            }

            if ($this->friendshipStatus->getStatus() != 0) {
                throw new ApiException('Friendship isn\'t peinding!', 400);
            }

            $this->friendshipStatus->updateStatus(-1);
            return true;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t deny friendship' . $e->getMessage() . $e->getLine() . $e->getFile(), 500);
        }
    }

    public function acceptFriendRequest($user)
    {
        try {
            if (!$this->doesFriendshipExist()) {
                throw new ApiException('Friendship does not exist!', 400);
            }

            if ($this->friendshipStatus->getStatus() == 1) {
                throw new ApiException('Friendship already accepted!', 400);
            }

            if ($this->friendshipStatus->getInitiator() == $user) {
                throw new ApiException('You are the initiator of this friendship!', 400);
            }

            if ($this->friendshipStatus->updateStatus(1)) {
                return true;
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t accept friendship', 500);
        }
    }

    //getters
    public function getUser1ID()
    {
        return $this->user1ID;
    }

    public function getUser2ID()
    {
        return $this->user2ID;
    }

    public function getFriendshipStatus()
    {
        return $this->friendshipStatus;
    }
}
