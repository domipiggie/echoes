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

    public function createGroupChannel($useIds)
    {
        try {
           $sql = "INSERT INTO channel_list
                    SET
                        friendshipID = :friendshipID";

            $args = [
                [':friendshipID', null]
            ];

            $results = DatabaseOperations::insertIntoDB($this->db, $sql, $args);

            if ($results) {
                $channelId = $results[1];
                $this->addUsersToChannel($channelId, $useIds);
                return $channelId;
            }

            throw new ApiException('Failed to create channel for group', 500);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode()); 
        } catch (Exception $e) {
            throw new ApiException('Failed to create channel for group '. $e->getMessage(), 500); 
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
            throw new ApiException('Failed to add users to channel '. $e->getMessage(), 500); 
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
}
