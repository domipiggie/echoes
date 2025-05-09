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
        $sql = "SELECT ownerID FROM group_list WHERE groupID = :groupID";
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
}