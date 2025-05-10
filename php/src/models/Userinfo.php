<?php
class Userinfo
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function getFriendList($user)
    {
        try {
            $sql = "SELECT f.friendshipID, f.statusID,fs.status,
                    CASE 
                        WHEN f.user1ID = :user THEN f.user2ID
                        ELSE f.user1ID
                    END as friendID, fs.initiator as initiator, u.username, u.displayName, u.profilePicture
                    FROM friendship f
                    INNER JOIN friendshipStatus fs
                        ON f.statusID = fs.statusID
                    INNER JOIN user u
                        ON (
                            CASE 
                            WHEN f.user1ID = :user THEN f.user2ID
                            ELSE f.user1ID
                            END = u.userID)
                        WHERE (f.user1ID = :user OR f.user2ID = :user)";

            $args = [
                [':user', $user]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            return $results;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get friend list', 500);
        }
    }

    public function getFriendChannels($user)
    {
        try {
            $channels = [];

            $sql = "SELECT cl.channelID, f.user1ID, f.user2ID, u1.username as user1_username, u1.displayName as user1_displayName,
                        u1.profilePicture as user1_profilePicture, u2.username as user2_username, u2.displayName as user2_displayName,
                        u2.profilePicture as user2_profilePicture FROM `channel_list` cl
                    INNER JOIN
                        `friendship` f
                    ON
                        cl.`friendshipID` = f.`friendshipID`
                    INNER JOIN
                        `user` u1
                    ON
                        f.`user1ID` = u1.`userID`
                    INNER JOIN
                        `user` u2
                    ON
                        f.`user2ID` = u2.`userID`
                    WHERE
                        f.`user1ID` = :userID OR f.`user2ID` = :userID";

            $args = [
                [':userID', $user]
            ];

            $friendshipResults = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            foreach ($friendshipResults as $row) {
                $channels[] = array(
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
            return $channels;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get friend channel list: ' . $e->getMessage(), 500);
        }
    }

    public function getGroupChannels($user)
    {
        try {
            $channels = [];
            $channelsMap = [];

            $sql = "SELECT ca.channelID, ca.userID, u.username, u.displayName, u.profilePicture, 
                    cl.groupID, gi.name as groupName, gi.picture as groupPicture 
                    FROM channel_access ca
                    INNER JOIN
                        user u
                    ON
                        ca.userID = u.userID
                    INNER JOIN
                        channel_list cl
                    ON
                        ca.channelID = cl.channelID
                    LEFT JOIN
                        group_info gi
                    ON
                        cl.groupID = gi.id
                    WHERE 
                        ca.channelID IN (
                            SELECT cl.channelID FROM channel_list cl
                            JOIN channel_access ca
                            ON
                                cl.channelID = ca.channelID
                            WHERE 
                                ca.userID = :userID
                        )";

            $args = [
                [':userID', $user]
            ];

            $groupResults = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            foreach ($groupResults as $row) {
                $userData = array(
                    "id" => $row['userID'],
                    "username" => $row['username'],
                    "displayName" => $row['displayName'],
                    "profilePicture" => $row['profilePicture']
                );

                $channelID = $row['channelID'];
                
                if (!isset($channelsMap[$channelID])) {
                    $channelsMap[$channelID] = [
                        "channelID" => $channelID,
                        "groupID" => $row['groupID'],
                        "groupName" => $row['groupName'],
                        "groupPicture" => $row['groupPicture'],
                        "users" => []
                    ];
                    $channels[] = &$channelsMap[$channelID];
                }
                
                $channelsMap[$channelID]["users"][] = $userData;
            }
            
            return $channels;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get group channels: ' . $e->getMessage(), 500);
        }
    }
}
