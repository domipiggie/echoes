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
        if ($friendshipId == null)
            throw new Exception("Friendship id not provided!");

        $query = "INSERT INTO channel_list
                    SET
                        friendshipID = :friendshipID";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':friendshipID', $friendshipId);

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

    public function handleGetChannelList()
    {

        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        $user = AuthMiddleware::validateToken();
        $channels = array("friendshipChannels" => array(), "groupChannels" => array());

        $sql = "SELECT channelID, user1ID, user2ID FROM `channel_list`
                INNER JOIN `friendship`
                ON
                    `channel_list`.`friendshipID` = `friendship`.`friendshipID`
                WHERE
                    `friendship`.`user1ID` = :userID OR `friendship`.`user2ID` = :userID;";

        $dbStmt = $this->db->prepare($sql);
        $dbStmt->bindParam(':userID', $user->id);
        $dbStmt->execute();

        if ($dbStmt->rowCount() > 0) {

            while ($row = $dbStmt->fetch(PDO::FETCH_ASSOC)) {
                $channels["friendshipChannels"][] = array("channelID" => $row['channelID'], "user1ID" => $row['user1ID'], "user2ID" => $row['user2ID']);
            }
        }

        $sql = "SELECT ca.channelID, ca.userID FROM channel_access ca
                WHERE 
                    ca.channelID IN (
                    SELECT cl.channelID FROM channel_list cl
                    JOIN channel_access ca
                    ON
                        cl.channelID = ca.channelID
                    WHERE 
                        ca.userID = :userID
                );";

        $dbStmt = $this->db->prepare($sql);
        $dbStmt->bindParam(':userID', $user->id);
        $dbStmt->execute();

        if ($dbStmt->rowCount() > 0) {
            $prevId = null;

            while ($row = $dbStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row["channelID"] !== $prevId) {
                    $prevId = $row['channelID'];
                    $channels["groupChannels"][] = array("channelID" => $row['channelID'], "users" => array($row["userID"]));
                } else {
                    $channels["groupChannels"][count($channels["groupChannels"]) - 1]["users"][] = $row['userID'];
                }
            }
        }

        echo json_encode($channels);
    }
}
?>