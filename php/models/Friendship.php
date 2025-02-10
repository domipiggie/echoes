<?php
class Friendship
{
    private $conn;
    private $request_table = 'friendship_request';
    private $friendship_table = 'friendship';

    public $user1ID;
    public $user2ID;
    public $isPending;
    public $isBlock;


    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function doesFriendrequestExist()
    {
        $query = "SELECT * FROM " . $this->request_table . "
                WHERE
                    user1ID = :user1ID AND
                    user2ID = :user2ID";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user1ID', $this->user1ID);
        $stmt->bindParam(':user2ID', $this->user2ID);

        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function sendFriendRequest()
    {
        if ($this->doesFriendrequestExist()) return false;

        $query = "INSERT INTO " . $this->request_table . "
                SET
                    user1ID = :user1ID,
                    user2ID = :user2ID";

        $stmt = $this->conn->prepare($query);

        $this->user1ID = htmlspecialchars(strip_tags($this->user1ID));
        $this->user2ID = htmlspecialchars(strip_tags($this->user2ID));

        $stmt->bindParam(':user1ID', $this->user1ID);
        $stmt->bindParam(':user2ID', $this->user2ID);

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
}
?>