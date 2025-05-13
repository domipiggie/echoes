<?php
class Message
{
    private $table_name = "message";

    public function createMessage($channelID, $userID, $content, $type = 'text', $replyTo = null)
    {
        try {
            $sql = "INSERT INTO " . $this->table_name . "
            (channelID, userID, content, type, replyTo, sent_at)
            VALUES (:channelID, :userID, :content, :type, :replyTo, NOW())";

            $args = [
                [':channelID', $channelID],
                [':userID', $userID],
                [':content', $content],
                [':type', $type],
                [':replyTo', $replyTo]
            ];

            $result = DatabaseOperations::insertIntoDB($sql, $args);

            if ($result) {
                $messageID = $result[1];

                $messageData = [
                    'messageID' => $messageID,
                    'channelID' => $channelID,
                    'userID' => $userID,
                    'content' => $content,
                    'type' => $type,
                    'replyTo' => $replyTo,
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

            $directUsers = DatabaseOperations::fetchFromDB($sql, $args);
            $directUserIds = array_column($directUsers, 'userID');

            $sql = "SELECT f.user1ID, f.user2ID FROM channel_list cl
                    INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
                    WHERE cl.channelID = :channelID";

            $args = [
                [':channelID', $channelID]
            ];

            $friendshipResults = DatabaseOperations::fetchFromDB($sql, $args);

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

            $result = DatabaseOperations::fetchFromDB($sql, $args);

            if (count($result) > 0) {
                return true;
            }

            $sql = "SELECT friendshipID FROM channel_list 
                    WHERE channelID = :channelID";

            $args = [
                [':channelID', $channelID]
            ];

            $result = DatabaseOperations::fetchFromDB($sql, $args);

            if (count($result) === 0) {
                return false;
            }

            if (!$result[0]['friendshipID']) {
                return false;
            }

            $sql = "SELECT 1 FROM channel_list cl
                    INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
                    INNER JOIN friendshipstatus fs ON f.statusID = fs.statusID
                    WHERE cl.channelID = :channelID
                    AND (f.user1ID = :userID OR f.user2ID = :userID)
                    AND fs.status = 1
                    LIMIT 1";

            $args = [
                [':channelID', $channelID],
                [':userID', $userID]
            ];

            $result = DatabaseOperations::fetchFromDB($sql, $args);

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

            $result = DatabaseOperations::fetchFromDB($sql, $args);

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
            $offset = (int)$offset;
            $limit = (int)$limit;
            
            $sql = "SELECT m.messageID, m.channelID, m.userID, m.content, m.type, m.sent_at, m.replyTo
                    FROM " . $this->table_name . " m
                    WHERE m.channelID = :channelID
                    ORDER BY m.sent_at DESC
                    LIMIT $offset, $limit";

            $args = [
                [':channelID', $channelID]
            ];

            $results = DatabaseOperations::fetchFromDB($sql, $args);

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

            $db = (new Database())->getConnection();
            $db->beginTransaction();

            $sql = "UPDATE " . $this->table_name . " SET replyTo = NULL WHERE replyTo = :messageId";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':messageId', $messageId);
            $stmt->execute();

            $sql = "DELETE FROM " . $this->table_name . " WHERE messageID = :messageId AND userID = :userId";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':messageId', $messageId);
            $stmt->bindParam(':userId', $userId);
            $result = $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $db->commit();
                return true;
            }

            $db->rollBack();
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
                    SET content = :content
                    WHERE messageID = :messageId AND userID = :userId";

            $args = [
                [':content', $newContent],
                [':messageId', $messageId],
                [':userId', $userId]
            ];

            $result = DatabaseOperations::updateDB($sql, $args);

            return $result > 0;

            throw new ApiException("Failed to update message", 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to edit message: ' . $e->getMessage(), 500);
        }
    }

    public function getChannelIdFromMessageId($messageId)
    {
        try {
            $sql = "SELECT channelID FROM ". $this->table_name. "
                    WHERE messageID = :messageId";
                    
            $args = [
                [':messageId', $messageId]
            ];

            $result = DatabaseOperations::fetchFromDB($sql, $args);

            if (count($result) > 0) {
                return $result[0]['channelID'];
            }

            return null;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode()); 
        } catch (Exception $e) {
            throw new ApiException('Failed to get channel ID from message ID: '. $e->getMessage(), 500); 
        }
    }
}
