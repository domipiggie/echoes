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
    }

    public function sendFriendRequest()
    {
        if ($this->doesFriendshipExist())
            return false;

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

        try {
            $this->dbConn->beginTransaction();

            if ($stmt->execute()) {
                $id = $this->dbConn->lastInsertId();
                $this->dbConn->commit();
                return array("message"=>"Successfully added friend!", "friendshipID"=>$id);
            }
        } catch (PDOException $e) {
            $this->dbConn->rollBack();
            return false;
        }
        return false;
    }

    public function declineFriendRequest()
    {
        if (!$this->doesFriendshipExist())
            return false;
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
        }
        return false;
    }

    public function acceptFriendRequest($user)
    {
        if (!$this->doesFriendshipExist())
            return false;

        if (!$this->friendshipStatus->getInitiator() == $user)
            return false;

        if ($this->friendshipStatus->updateStatus(1)) {
            return true;
        }
        return false;
    }

    public function getFriendList($user)
    {
        $query = "SELECT friendshipID, user1ID, user2ID, status
                    FROM friendship INNER JOIN friendshipStatus ON friendship.statusID = friendshipStatus.statusID
                        WHERE friendshipStatus.status = 1 AND (friendship.user1ID = :user OR friendship.user2ID = :user);";
        
        $stmt = $this->dbConn->prepare($query);
        $stmt->bindParam(':user',$user->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
?>