<?php
class Group
{
    private $db;
    private $logger;

    public function __construct($db, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function isGroupOwner($userId, $groupId)
    {
        $sql = "SELECT ownerID FROM group_info WHERE id = :groupID";
        $args = [
            [':groupID', $groupId]
        ];
        
        $result = \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
        
        if (count($result) == 0) {
            return false;
        }
        
        return $result[0]['ownerID'] == $userId;
    }

    public function getGroupChannelId($groupId)
    {
        $sql = "SELECT channelID FROM channel_list WHERE groupID = :groupID";
        $args = [
            [':groupID', $groupId]
        ];
        
        $result = \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
        
        if (count($result) == 0) {
            throw new \WebSocketException(
                "Group channel not found",
                "CHANNEL_NOT_FOUND",
                404
            );
        }
        
        return $result[0]['channelID'];
    }

    public function getGroupInfo($groupId)
    {
        $channel = new \Channel($this->db);
        return $channel->getGroupInfo($groupId);
    }

    public function checkUserInGroup($userId, $channelId)
    {
        $sql = "SELECT userID FROM channel_access WHERE channelID = :channelID AND userID = :userID";
        $args = [
            [':channelID', $channelId],
            [':userID', $userId]
        ];
        
        $userCheck = \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
        
        if (count($userCheck) == 0) {
            throw new \WebSocketException(
                "User is not a member of this group",
                "USER_NOT_IN_GROUP",
                404
            );
        }
        
        return true;
    }

    public function removeUserFromGroup($userId, $channelId)
    {
        $sql = "DELETE FROM channel_access WHERE channelID = :channelID AND userID = :userID";
        $args = [
            [':channelID', $channelId],
            [':userID', $userId]
        ];
        
        \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
        return true;
    }

    public function getGroupMembers($channelId, $excludeUserId = null)
    {
        $sql = "SELECT userID FROM channel_access WHERE channelID = :channelID";
        $args = [
            [':channelID', $channelId]
        ];
        
        if ($excludeUserId) {
            $sql .= " AND userID != :excludeUserID";
            $args[] = [':excludeUserID', $excludeUserId];
        }
        
        return \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
    }

    public function getMembersForNotification($channelId, $userId, $senderId)
    {
        $sql = "SELECT userID FROM channel_access WHERE channelID = :channelID AND userID != :userID AND userID != :senderID";
        $args = [
            [':channelID', $channelId],
            [':userID', $userId],
            [':senderID', $senderId]
        ];
        
        return \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
    }

    public function validateFriendship($senderId, $userId)
    {
        $friendship = new \Friendship($this->db, $senderId, $userId);
        
        if (!$friendship->doesFriendshipExist() || $friendship->getFriendshipStatus() != 1) {
            throw new \WebSocketException(
                "You must be friends with the user to add them to the group",
                "INVALID_FRIENDSHIP",
                400
            );
        }
    }

    public function createGroupWithValidatedUsers($validUserIds, $groupName, $groupPicture, $senderId)
    {
        $channel = new \Channel($this->db);
        $channelId = $channel->createGroupChannel($validUserIds, $groupName, $groupPicture, $senderId);

        if (!$channelId) {
            throw new \WebSocketException(
                "Failed to create group channel",
                "GROUP_CREATION_FAILED",
                500
            );
        }
        
        return $channelId;
    }

    public function getGroupIdFromChannel($channelId)
    {
        $sql = "SELECT groupID FROM channel_list WHERE channelID = :channelID";
        $args = [
            [':channelID', $channelId]
        ];
        
        $result = \DatabaseOperations::fetchFromDB($this->db, $sql, $args);
        
        if (count($result) == 0) {
            throw new \WebSocketException(
                "Group not found for this channel",
                "GROUP_NOT_FOUND",
                404
            );
        }
        
        if ($result[0]['groupID'] === null) {
            throw new \WebSocketException(
                "This channel is not associated with a group",
                "NOT_A_GROUP_CHANNEL",
                400
            );
        }
        
        return $result[0]['groupID'];
    }

    public function setProfilePicture($profilePicture, $groupID, $dbConn)
    {
        try {
            $query = "UPDATE group_info
                    SET
                        picture = :profilePicture
                    WHERE
                        id = :id";
            
            $args = [
                [':profilePicture', $profilePicture],
                [':id', $groupID]
            ];
            
            $result = DatabaseOperations::updateDB($dbConn, $query, $args);
            
            if ($result > 0) {
                return true;
            }
            
            throw new ApiException('Failed to update profile picture', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update profile picture: ' . $e->getMessage(), 500);
        }
    }

    public function transferOwnership($groupId, $newOwnerId)
    {
        try {
            $sql = "UPDATE group_info SET ownerID = :newOwnerId WHERE id = :groupId";
            $args = [
                [':newOwnerId', $newOwnerId],
                [':groupId', $groupId]
            ];
            
            $result = \DatabaseOperations::updateDB($this->db, $sql, $args);
            
            if ($result <= 0) {
                throw new \WebSocketException(
                    "Failed to transfer group ownership",
                    "TRANSFER_OWNERSHIP_FAILED",
                    500
                );
            }
            
            return true;
        } catch (\Exception $e) {
            throw new \WebSocketException(
                "Failed to transfer group ownership: " . $e->getMessage(),
                "TRANSFER_OWNERSHIP_FAILED",
                500
            );
        }
    }
    
    public function deleteGroup($groupId, $channelId)
    {
        try {
            $this->db->beginTransaction();
            
            $sql = "DELETE FROM messages WHERE channelID = :channelID";
            $args = [
                [':channelID', $channelId]
            ];
            \DatabaseOperations::updateDB($this->db, $sql, $args);
            
            $sql = "DELETE FROM channel_access WHERE channelID = :channelID";
            $args = [
                [':channelID', $channelId]
            ];
            \DatabaseOperations::updateDB($this->db, $sql, $args);
            
            $sql = "DELETE FROM channel_list WHERE channelID = :channelID";
            $args = [
                [':channelID', $channelId]
            ];
            \DatabaseOperations::updateDB($this->db, $sql, $args);
            
            $sql = "DELETE FROM group_info WHERE id = :groupID";
            $args = [
                [':groupID', $groupId]
            ];
            \DatabaseOperations::updateDB($this->db, $sql, $args);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \WebSocketException(
                "Failed to delete group: " . $e->getMessage(),
                "DELETE_GROUP_FAILED",
                500
            );
        }
    }
}