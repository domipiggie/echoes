<?php
class Message
{
    private $dbConn;
    private $table_name = "message";

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function createMessage($channelID, $userID, $content, $type = 'text')
    {
        try {
            $sql = "INSERT INTO " . $this->table_name . "
            (channelID, userID, content, type, sent_at)
            VALUES (:channelID, :userID, :content, :type, NOW())";

            $args = [
                [':channelID', $channelID],
                [':userID', $userID],
                [':content', $content],
                [':type', $type]
            ];

            $result = DatabaseOperations::insertIntoDB($this->dbConn, $sql, $args);

            if ($result) {
                $messageID = $result[1];

                $messageData = [
                    'messageID' => $messageID,
                    'channelID' => $channelID,
                    'userID' => $userID,
                    'content' => $content,
                    'type' => $type,
                    'sent_at' => date('Y-m-d H:i:s')
                ];

                return $messageData;
            }

            throw new ApiException("Failed to send message", 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t send message ' . $e->getMessage(), 500);
        }
    }

    public function getUsersWithChannelAccess($channelID)
    {
        try {
            $sql = "SELECT userID FROM channel_access WHERE channelID = :channelID";

            $args = [
                [':channelID', $channelID]
            ];

            $directUsers = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);
            $directUserIds = array_column($directUsers, 'userID');

            $sql = "SELECT f.user1ID, f.user2ID FROM channel_list cl
                    INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
                    WHERE cl.channelID = :channelID";

            $args = [
                [':channelID', $channelID]
            ];

            $friendshipResults = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            $friendshipUsers = [];
            foreach ($friendshipResults as $row) {
                $friendshipUsers[] = $row['user1ID'];
                $friendshipUsers[] = $row['user2ID'];
            }

            return array_unique(array_merge($directUserIds, $friendshipUsers));
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get users with channel access: ' . $e->getMessage(), 500);
        }
    }

    public function hasChannelAccess($userID, $channelID)
    {
        try {
            $sql = "SELECT 1 FROM channel_access 
                    WHERE userID = :userID 
                    AND channelID = :channelID 
                    LIMIT 1";

            $args = [
                [':userID', $userID],
                [':channelID', $channelID]
            ];

            $result = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            if (count($result) > 0) {
                return true;
            }

            $sql = "SELECT friendshipID FROM channel_list 
                    WHERE channelID = :channelID";

            $args = [
                [':channelID', $channelID]
            ];

            $result = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            if (count($result) === 0) {
                return false;
            }

            if (!$result[0]['friendshipID']) {
                return true;
            }

            $sql = "SELECT 1 FROM channel_list cl
                    INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
                    INNER JOIN friendshipStatus fs ON f.statusID = fs.statusID
                    WHERE cl.channelID = :channelID
                    AND (f.user1ID = :userID OR f.user2ID = :userID)
                    AND fs.status = 1
                    LIMIT 1";

            $args = [
                [':channelID', $channelID],
                [':userID', $userID]
            ];

            $result = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            return count($result) > 0;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t check channel access: ' . $e->getMessage(), 500);
        }
    }

    public function hasMessageOwnership($userId, $messageId)
    {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE messageID = :messageId AND userID = :userId";

            $args = [
                [':messageId', $messageId],
                [':userId', $userId]
            ];

            $result = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            return count($result) > 0;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t check message ownership: ' . $e->getMessage(), 500);
        }
    }

    public function getChannelMessages($channelID, $offset = 0, $limit = 20)
    {
        try {
            $sql = "SELECT m.messageID, m.channelID, m.userID, m.content, m.type, m.sent_at
                    FROM " . $this->table_name . " m
                    WHERE m.channelID = :channelID
                    ORDER BY m.sent_at DESC
                    LIMIT :offset, :limit";

            $args = [
                [':channelID', $channelID],
                [':offset', $offset],
                [':limit', $limit]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $sql, $args);

            return $results;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get channel messages: ' . $e->getMessage(), 500);
        }
    }

    public function deleteMessage($messageId, $userId)
    {
        try {
            if (!$this->hasMessageOwnership($userId, $messageId)) {
                throw new ApiException("Message not found or you don't have permission to delete it", 403);
            }

            $sql = "DELETE FROM " . $this->table_name . " 
                    WHERE messageID = :messageId AND userID = :userId";

            $args = [
                [':messageId', $messageId],
                [':userId', $userId]
            ];

            $result = DatabaseOperations::updateDB($this->dbConn, $sql, $args);

            if ($result) {
                return true;
            }

            throw new ApiException("Failed to delete message", 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to delete message: ' . $e->getMessage(), 500);
        }
    }

    public function editMessage($messageId, $userId, $newContent)
    {
        try {
            if (!$this->hasMessageOwnership($userId, $messageId)) {
                throw new ApiException("Message not found or you don't have permission to edit it", 403);
            }

            $sql = "UPDATE " . $this->table_name . " 
                    SET content = :content, edited_at = NOW()
                    WHERE messageID = :messageId AND userID = :userId";

            $args = [
                [':content', $newContent],
                [':messageId', $messageId],
                [':userId', $userId]
            ];

            $result = DatabaseOperations::updateDB($this->dbConn, $sql, $args);

            return $result > 0;

            throw new ApiException("Failed to update message", 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to edit message: ' . $e->getMessage(), 500);
        }
    }
}
