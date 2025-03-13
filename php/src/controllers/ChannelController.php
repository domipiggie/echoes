<?php
class ChannelController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createFriendshipChannel($friendshipId)
    {
        try {
            if ($friendshipId == null) {
                throw new ApiException('Missing required fields', 400);
            }

            $query = "INSERT INTO channel_list
                    SET
                        friendshipID = :friendshipID";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':friendshipID', $friendshipId);

            try {
                $this->db->beginTransaction();

                if ($stmt->execute()) {
                    $this->db->commit();
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw new ApiException('Database error: ' . $e->getMessage(), 500);
            }
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create channel for friendship', 500);
        }
    }

    public function handleGetChannelList()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method!', 400);
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
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get channel list', 500);
        }
    }
}
