<?php
class Channel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createFriendshipChannel($friendshipId)
    {
        try {
            if ($this->doesFriendshipChannelExists($friendshipId)) {
                return false;
            }

            $sql = "INSERT INTO channel_list
                    SET
                        friendshipID = :friendshipID";

            $args = [
                [':friendshipID', $friendshipId]
            ];

            $results = DatabaseOperations::insertIntoDB($this->db, $sql, $args);

            if ($results) {
                return $results[1];
            }
            throw new ApiException('Failed to create channel for friendship', 500);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create channel for friendship ' . $e->getMessage(), 500);
        }
    }

    public function createGroupChannel($userIds, $groupName, $groupPicture = null, $ownerId)
    {
        try {
            $sql = "INSERT INTO group_info
                    SET
                        name = :name,
                        picture = :picture,
                        ownerID = :ownerID";

            $args = [
                [':name', $groupName],
                [':picture', $groupPicture],
                [':ownerID', $ownerId]
            ];

            $groupResults = DatabaseOperations::insertIntoDB($this->db, $sql, $args);
            
            if (!$groupResults) {
                throw new ApiException('Failed to create group chat', 500);
            }
            
            $groupId = $groupResults[1];
            
            $sql = "INSERT INTO channel_list
                    SET
                        friendshipID = :friendshipID,
                        id = :id";

            $args = [
                [':friendshipID', null],
                [':id', $groupId]
            ];

            $results = DatabaseOperations::insertIntoDB($this->db, $sql, $args);

            if ($results) {
                $channelId = $results[1];
                $this->addUsersToChannel($channelId, $userIds);
                return $channelId;
            }

            throw new ApiException('Failed to create channel for group', 500);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create channel for group ' . $e->getMessage(), 500);
        }
    }

    public function addUsersToChannel($channelId, $userIds)
    {
        try {
            $sql = "INSERT INTO channel_access
                    SET
                        channelID = :channelID,
                        userID = :userID";

            foreach ($userIds as $userId) {
                $args = [
                    [':channelID', $channelId],
                    [':userID', $userId]
                ];

                $results = DatabaseOperations::insertIntoDB($this->db, $sql, $args);

                if (!$results) {
                    throw new ApiException('Failed to add users to channel', 500);
                }
            }
            return true;
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to add users to channel ' . $e->getMessage(), 500);
        }
    }

    public function removeUsersFromChannel($channelId, $userIds)
    {
        try {
            $sql = "DELETE FROM channel_access
                    WHERE
                        channelID = :channelID AND
                        userID = :userID";

            foreach ($userIds as $userId) {
                $args = [
                    [':channelID', $channelId],
                    [':userID', $userId]
                ];

                $results = DatabaseOperations::updateDB($this->db, $sql, $args);

                if ($results === false) {
                    throw new ApiException('Failed to remove users from channel', 500);
                }
            }
            return true;
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to remove users from channel ' . $e->getMessage(), 500);
        }
    }

    public function doesFriendshipChannelExists($friendshipId)
    {
        try {
            $sql = "SELECT * FROM channel_list
                    WHERE
                        friendshipID = :friendshipID";

            $args = [
                [':friendshipID', $friendshipId]
            ];

            $result = DatabaseOperations::fetchFromDB($this->db, $sql, $args);


            if (count($result) > 0) {
                return true;
            }
            return false;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t check if friendship exists', 500);
        }
    }

    public function getGroupInfo($groupId)
    {
        try {
            $sql = "SELECT g.*, u.username, u.displayName, u.profilePicture 
                    FROM group_info g
                    JOIN user u ON g.ownerID = u.userID
                    WHERE g.id = :id";

            $args = [
                [':id', $groupId]
            ];

            $result = DatabaseOperations::fetchFromDB($this->db, $sql, $args);

            if (count($result) > 0) {
                return $result[0];
            }
            return false;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get group information', 500);
        }
    }

    public function updateGroupInfo($groupId, $name = null, $picture = null)
    {
        try {
            $updates = [];
            $args = [[':id', $groupId]];
            
            if ($name !== null) {
                $updates[] = "name = :name";
                $args[] = [':name', $name];
            }
            
            if ($picture !== null) {
                $updates[] = "picture = :picture";
                $args[] = [':picture', $picture];
            }
            
            if (empty($updates)) {
                return true;
            }
            
            $sql = "UPDATE group_info SET " . implode(", ", $updates) . " WHERE id = :id";
            
            $result = DatabaseOperations::updateDB($this->db, $sql, $args);
            
            return $result;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t update group information: ' . $e->getMessage(), 500);
        }
    }
    
    public function isGroupOwner($userId, $groupId)
    {
        try {
            $sql = "SELECT ownerID FROM group_info WHERE id = :id";
            
            $args = [
                [':id', $groupId]
            ];
            
            $result = DatabaseOperations::fetchFromDB($this->db, $sql, $args);
            
            if (count($result) > 0 && $result[0]['ownerID'] == $userId) {
                return true;
            }
            
            return false;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to check group ownership: ' . $e->getMessage(), 500);
        }
    }
}
