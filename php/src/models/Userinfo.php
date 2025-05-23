<?php
class Userinfo
{
    public function getFriendList($user)
    {
        try {
            $sql = "SELECT f.friendshipID, f.statusID,fs.status,
                    CASE 
                        WHEN f.user1ID = :user THEN f.user2ID
                        ELSE f.user1ID
                    END as friendID, fs.initiator as initiator, u.username, u.displayName, u.profilePicture
                    FROM friendship f
                    INNER JOIN friendshipstatus fs
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

            $results = DatabaseOperations::fetchFromDB($sql, $args);

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
                        u2.profilePicture as user2_profilePicture, m.content, m.userID as lastMessageUserID,
                        u3.username as lastMessageUsername
                    FROM `channel_list` cl
                    INNER JOIN
                        `friendship` f
                    ON
                        cl.`friendshipID` = f.`friendshipID`
                    INNER JOIN
                        `friendshipstatus` fs
                    ON
                        f.`statusID` = fs.`statusID`
                    INNER JOIN
                        `user` u1
                    ON
                        f.`user1ID` = u1.`userID`
                    INNER JOIN
                        `user` u2
                    ON
                        f.`user2ID` = u2.`userID`
                    LEFT JOIN
                        (SELECT m1.* 
                         FROM message m1
                         INNER JOIN (
                             SELECT channelID, MAX(sent_at) as sent_at 
                             FROM message 
                             GROUP BY channelID
                         ) m2 ON m1.channelID = m2.channelID AND m1.sent_at = m2.sent_at) m
                    ON
                        cl.channelID = m.channelID
                    LEFT JOIN
                        `user` u3
                    ON
                        m.userID = u3.userID
                    WHERE
                        (f.`user1ID` = :userID OR f.`user2ID` = :userID)
                        AND fs.`status` = 1";

            $args = [
                [':userID', $user]
            ];

            $friendshipResults = DatabaseOperations::fetchFromDB($sql, $args);

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
                    ),
                    "lastMessage" => $row['content'],
                    "lastMessageUserID" => $row['lastMessageUserID'],
                    "lastMessageUsername" => $row['lastMessageUsername']
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
                    cl.groupID, gi.name as groupName, gi.picture as groupPicture, gi.ownerID as groupOwnerID,
                    m.content, m.userID as lastMessageUserID, u2.username as lastMessageUsername
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
                    LEFT JOIN
                        (SELECT m1.* 
                         FROM message m1
                         INNER JOIN (
                             SELECT channelID, MAX(sent_at) as sent_at 
                             FROM message 
                             GROUP BY channelID
                         ) m2 ON m1.channelID = m2.channelID AND m1.sent_at = m2.sent_at) m
                    ON
                        ca.channelID = m.channelID
                    LEFT JOIN
                        user u2
                    ON
                        m.userID = u2.userID
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

            $groupResults = DatabaseOperations::fetchFromDB($sql, $args);

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
                        "groupOwnerID" => $row['groupOwnerID'],
                        "lastMessage" => $row['content'],
                        "lastMessageUserID" => $row['lastMessageUserID'],
                        "lastMessageUsername" => $row['lastMessageUsername'],
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
