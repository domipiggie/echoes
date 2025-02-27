<?php
class ChannelController
{
    private $db;
    private $channel;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createFriendshipChannel($friendshipId)
    {
        if ($friendshipId == null) throw new Exception("Friendship id not provided!");
        
        $query = "INSERT INTO channel_list
                    SET
                        friendshipID = :friendshipID";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':friendshipID',$friendshipId);

        try {
            $this->db->beginTransaction();

            if ($stmt->execute()) {
                $this->db->commit();
                return false;
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
        return false;
    }
}
?>