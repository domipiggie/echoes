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
            $query = "SELECT * FROM " . $this->friendship_table . "
                WHERE
                    user1ID = :user1ID AND user2ID = :user2ID
                    OR
                    user1ID = :user2ID AND user2ID = :user1ID";

            $stmt = $this->dbConn->prepare($query);

            $stmt->bindParam(':user1ID', $this->user1ID);
            $stmt->bindParam(':user2ID', $this->user2ID);

            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                if (!isset($this->friendshipStatus)) {
                    $this->friendshipStatus = new FriendshipStatus($this->dbConn);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $this->friendshipStatus->loadFromDB($row['statusID']);
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function sendFriendRequest()
    {
        try {
            if ($this->doesFriendshipExist()) {
                throw new ApiException('Friendship already exists!', 400);
            }

            $this->friendshipStatus = new FriendshipStatus($this->dbConn);
            $this->friendshipStatus->createNewEntry($this->user1ID);

            $query = "INSERT INTO " . $this->friendship_table . "
                SET
                    user1ID = :user1ID,
                    user2ID = :user2ID,
                    statusID = :statusID";

            $stmt = $this->dbConn->prepare($query);

            $this->user1ID = htmlspecialchars(strip_tags($this->user1ID));
            $this->user2ID = htmlspecialchars(strip_tags($this->user2ID));

            $stmt->bindParam(':user1ID', $this->user1ID);
            $stmt->bindParam(':user2ID', $this->user2ID);
            $stmt->bindParam(':statusID', $this->friendshipStatus->getStatusID());
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }

        try {
            $this->dbConn->beginTransaction();

            if ($stmt->execute()) {
                $id = $this->dbConn->lastInsertId();
                $this->dbConn->commit();
                return array("message" => "Successfully added friend!", "friendshipID" => $id);
            } else {
                throw new ApiException('Couldn\'t send friend request', 500);
            }
        } catch (PDOException $e) {
            $this->dbConn->rollBack();
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function declineFriendRequest()
    {
        try {
            if ($this->doesFriendshipExist()) {
                throw new ApiException('Friendship already exists!', 400);
            }

            $query = "DELETE FROM " . $this->friendship_table . "
            WHERE
                user1ID = :user1ID AND user2ID = :user2ID
                OR
                user1ID = :user2ID AND user2ID = :user1ID";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(':user1ID', $this->user1ID);
            $stmt->bindParam(':user2ID', $this->user2ID);

            if ($stmt->execute()) {
                $this->friendshipStatus->removeEntry();
                return true;
            } else {
                throw new ApiException('Couldn\'t deny friendship', 500);
            }
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
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

            if ($this->friendshipStatus->getInitiator() != $user) {
                throw new ApiException('You are not the initiator of this friendship!', 400);
            }

            if ($this->friendshipStatus->updateStatus(1)) {
                return true;
            } else {
                throw new ApiException('Friendship not accepted!', 500);
            }
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function getFriendList($user)
    {
        try {

            $query = "SELECT friendshipID, user1ID, user2ID, status
            FROM friendship INNER JOIN friendshipStatus ON friendship.statusID = friendshipStatus.statusID
                WHERE friendshipStatus.status = 1 AND (friendship.user1ID = :user OR friendship.user2ID = :user);";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(':user', $user->id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
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
