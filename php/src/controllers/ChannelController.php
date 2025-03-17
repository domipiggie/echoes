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

            // Modified query to include user details for friendship channels
            $sql = "SELECT 
                cl.channelID, 
                f.user1ID, 
                f.user2ID,
                u1.username as user1_username,
                u1.displayName as user1_displayName,
                u1.profilePicture as user1_profilePicture,
                u2.username as user2_username,
                u2.displayName as user2_displayName,
                u2.profilePicture as user2_profilePicture
            FROM `channel_list` cl
            INNER JOIN `friendship` f ON cl.`friendshipID` = f.`friendshipID`
            INNER JOIN `user` u1 ON f.`user1ID` = u1.`userID`
            INNER JOIN `user` u2 ON f.`user2ID` = u2.`userID`
            WHERE
                f.`user1ID` = :userID OR f.`user2ID` = :userID;";

            $dbStmt = $this->db->prepare($sql);
            $dbStmt->bindParam(':userID', $user->id);
            $dbStmt->execute();

            if ($dbStmt->rowCount() > 0) {
                while ($row = $dbStmt->fetch(PDO::FETCH_ASSOC)) {
                    $channels["friendshipChannels"][] = array(
                        "channelID" => $row['channelID'], 
                        "user1" => array(
                            "id" => $row['user1ID'],
                            "username" => $row['user1_username'],
                            "displayName" => $row['user1_displayName'],
                            "profilePicture" => $row['user1_profilePicture']
                        ),
                        "user2" => array(
                            "id" => $row['user2ID'],
                            "username" => $row['user2_username'],
                            "displayName" => $row['user2_displayName'],
                            "profilePicture" => $row['user2_profilePicture']
                        )
                    );
                }
            }

            // Modified query to include user details for group channels
            $sql = "SELECT 
                ca.channelID, 
                ca.userID,
                u.username,
                u.displayName,
                u.profilePicture
            FROM channel_access ca
            INNER JOIN user u ON ca.userID = u.userID
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
                    $userData = array(
                        "id" => $row['userID'],
                        "username" => $row['username'],
                        "displayName" => $row['displayName'],
                        "profilePicture" => $row['profilePicture']
                    );
                    
                    if ($row["channelID"] !== $prevId) {
                        $prevId = $row['channelID'];
                        $channels["groupChannels"][] = array(
                            "channelID" => $row['channelID'], 
                            "users" => array($userData)
                        );
                    } else {
                        $channels["groupChannels"][count($channels["groupChannels"]) - 1]["users"][] = $userData;
                    }
                }
            }

            echo json_encode($channels);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get channel list'.$e->getMessage(), 500);
        }
    }
}
