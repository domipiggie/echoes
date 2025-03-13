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
                return $this->dbConn->lastInsertId();
            }

            throw new ApiException("Failed to send message", 500);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function hasChannelAccess($userID, $channelID)
    {
        try {
            // Check direct channel access first
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

            // Check friendship-based access
            $query = "SELECT 1 FROM channel_list cl
            INNER JOIN friendship f ON cl.friendshipID = f.friendshipID
            WHERE cl.channelID = :channelID
            AND (f.user1ID = :userID OR f.user2ID = :userID)
            LIMIT 1";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(":channelID", $channelID);
            $stmt->bindParam(":userID", $userID);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
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
            throw new ApiException($e->getMessage(), 500);
        }
    }
}
