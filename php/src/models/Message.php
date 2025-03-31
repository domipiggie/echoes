<?php
class Message
{
    private $dbConn;
    private $table_name = "message";

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function createMessage($channelID, $userID, $content)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . "
            (channelID, userID, content, sent_at)
            VALUES (:channelID, :userID, :content, NOW())";

            $stmt = $this->dbConn->prepare($query);

            $stmt->bindParam(":channelID", $channelID);
            $stmt->bindParam(":userID", $userID);
            $stmt->bindParam(":content", $content);

            if ($stmt->execute()) {
                $messageID = $this->dbConn->lastInsertId();

                $messageData = [
                    'messageID' => $messageID,
                    'channelID' => $channelID,
                    'userID' => $userID,
                    'content' => $content,
                    'sent_at' => date('Y-m-d H:i:s')
                ];

                $accessibleUsers = $this->getUsersWithChannelAccess($channelID);

                $this->notifyNewMessage($messageData, $channelID, $accessibleUsers);

                return $messageID;
            }

            throw new ApiException("Failed to send message", 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t send message', 500);
        }
    }

    private function getUsersWithChannelAccess($channelID)
    {
        try {
            $query = "SELECT userID FROM channel_access WHERE channelID = :channelID";
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->execute();
            $directUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $query = "SELECT f.user1ID, f.user2ID FROM channel_list cl
                      INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
                      WHERE cl.channelID = :channelID";
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->execute();

            $friendshipUsers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $friendshipUsers[] = $row['user1ID'];
                $friendshipUsers[] = $row['user2ID'];
            }

            return array_unique(array_merge($directUsers, $friendshipUsers));
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get users with channel access', 500);
        }
    }

    private function notifyNewMessage($messageData, $channelID, $accessibleUsers)
    {
        try {
            global $messageNotifier;

            if (isset($messageNotifier)) {
                $messageNotifier->notifyNewMessage($messageData, $channelID, $accessibleUsers);
            } else {
                $data = json_encode([
                    'messageData' => $messageData,
                    'channelID' => $channelID,
                    'accessibleUsers' => $accessibleUsers
                ]);

                $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/json',
                        'content' => $data
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ];

                $context = stream_context_create($options);
                
                $isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);
                
                $notifyUrl = $isLocalhost 
                    ? 'http://localhost/notify' 
                    : 'https://localhost/notify';
                    
                $result = @file_get_contents($notifyUrl, false, $context);

                error_log("Sent notification request for users " . implode(',', $accessibleUsers) . " about new message in channel $channelID");
                if ($result === false) {
                    error_log("Failed to send notification request: " . error_get_last()['message']);
                }
            }
        } catch (Exception $e) {
            error_log("Failed to send WebSocket notification: " . $e->getMessage());
        }
    }

    public function hasChannelAccess($userID, $channelID)
    {
        try {
            // First check direct channel access
            $query = "SELECT 1 FROM channel_access 
            WHERE userID = :userID 
            AND channelID = :channelID 
            LIMIT 1";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":userID", $userID);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return true;
            }

            $query = "SELECT friendshipID FROM channel_list 
            WHERE channelID = :channelID";
            
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !$result['friendshipID']) {
                $query = "SELECT 1 FROM channel_list 
                WHERE channelID = :channelID";
                
                $stmt = $this->dbConn->prepare($query);
                $stmt->bindParam(":channelID", $channelID);
                $stmt->execute();
                
                return $stmt->rowCount() > 0;
            }
            
            $query = "SELECT 1 FROM channel_list cl
            INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
            INNER JOIN friendshipStatus fs ON f.statusID = fs.statusID
            WHERE cl.channelID = :channelID
            AND (f.user1ID = :userID OR f.user2ID = :userID)
            AND fs.status = 1
            LIMIT 1";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->bindParam(":userID", $userID);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t check channel access', 500);
        }
    }

    public function getChannelMessages($channelID, $offset = 0, $limit = 20)
    {
        try {
            $query = "SELECT m.messageID, m.channelID, m.userID, m.content, m.sent_at
            FROM " . $this->table_name . " m
            WHERE m.channelID = :channelID
            ORDER BY m.sent_at DESC
            LIMIT :offset, :limit";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get channel messages', 500);
        }
    }

    public function deleteMessage($messageId, $userId)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE id = :messageId AND sender_id = :userId";
            
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":messageId", $messageId);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                throw new ApiException("Message not found or you don't have permission to delete it", 403);
            }
            
            $query = "DELETE FROM " . $this->table_name . " 
                     WHERE id = :messageId AND sender_id = :userId";
            
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":messageId", $messageId);
            $stmt->bindParam(":userId", $userId);
            
            if ($stmt->execute()) {
                return true;
            }
            
            throw new ApiException("Failed to delete message", 500);
        } catch (Exception $e) {
            if ($e instanceof ApiException) {
                throw $e;
            }
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function editMessage($messageId, $userId, $newContent)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE id = :messageId AND userID = :userId";
            
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":messageId", $messageId);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                throw new ApiException("Message not found or you don't have permission to edit it", 403);
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET content = :content, edited_at = NOW()
                     WHERE id = :messageId AND userID = :userId";
            
            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":content", $newContent);
            $stmt->bindParam(":messageId", $messageId);
            $stmt->bindParam(":userId", $userId);
            
            if ($stmt->execute()) {
                $query = "SELECT channelID FROM " . $this->table_name . " WHERE id = :messageId";
                $stmt = $this->dbConn->prepare($query);
                $stmt->bindParam(":messageId", $messageId);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $channelID = $result['channelID'];
                    $accessibleUsers = $this->getUsersWithChannelAccess($channelID);
                    
                    global $messageNotifier;
                    if (isset($messageNotifier)) {
                        $messageData = [
                            'messageID' => $messageId,
                            'channelID' => $channelID,
                            'userID' => $userId,
                            'content' => $newContent,
                            'edited_at' => date('Y-m-d H:i:s')
                        ];
                        $messageNotifier->notifyMessageEdited($messageData, $channelID, $accessibleUsers);
                    }
                }
                
                return true;
            }
            
            throw new ApiException("Failed to update message", 500);
        } catch (Exception $e) {
            if ($e instanceof ApiException) {
                throw $e;
            }
            throw new ApiException($e->getMessage(), 500);
        }
    }
}
