<?php
class Friendship
{
    private $friendship_table = 'friendship';
    private $conn;
    public $user1ID;
    public $user2ID;
    public $friendshipStatus;


    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function doesFriendshipExist()
    {
        $query = "SELECT * FROM " . $this->friendship_table . "
                WHERE
                    user1ID = :user1ID AND user2ID = :user2ID
                    OR
                    user1ID = :user2ID AND user2ID = :user1ID";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user1ID', $this->user1ID);
        $stmt->bindParam(':user2ID', $this->user2ID);

        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            if (!isset($this->friendshipStatus)) {
                $this->friendshipStatus = new FriendshipStatus($this->conn);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->friendshipStatus->loadFromRow($row);
            }
            return true;
        }
        return false;
    }

    public function sendFriendRequest()
    {
        if ($this->doesFriendshipExist())
            return false;

        $this->friendshipStatus = new FriendshipStatus($this->conn);
        $this->friendshipStatus->createNewEntry($this->user1ID);

        $query = "INSERT INTO " . $this->friendship_table . "
                SET
                    user1ID = :user1ID,
                    user2ID = :user2ID,
                    statusID = :statusID";

        $stmt = $this->conn->prepare($query);

        $this->user1ID = htmlspecialchars(strip_tags($this->user1ID));
        $this->user2ID = htmlspecialchars(strip_tags($this->user2ID));

        $stmt->bindParam(':user1ID', $this->user1ID);
        $stmt->bindParam(':user2ID', $this->user2ID);
        $stmt->bindParam(':statusID', $this->friendshipStatus->statusID);

        try {
            $this->conn->beginTransaction();

            if ($stmt->execute()) {
                $this->conn->commit();
                return true;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
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

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1ID', $this->user1ID);
        $stmt->bindParam(':user2ID', $this->user2ID);

        if ($stmt->execute()) {
            $this->friendshipStatus->removeEntry();
            return true;
        }
        return false;
    }

    public function acceptFriendRequest()
    {
        if (!$this->doesFriendshipExist())
            return false;

        if ($this->friendshipStatus->updateStatus(1)) {
            return true;
        }
        return false;
    }
}
?>