<?php
class Friendship
{
    private $friendship_table = 'friendship';
    private $dbConn;
    private $user1ID;
    private $user2ID;
    private $friendshipStatus;


    public function __construct($db, $user1ID, $user2ID)
    {
        $this->dbConn = $db;
        $this->user1ID = $user1ID;
        $this->user2ID = $user2ID;
    }

    public function doesFriendshipExist()
    {
        try {
            $query = "SELECT * FROM " . $this->friendship_table . "
                WHERE
                    user1ID = :user1ID AND user2ID = :user2ID
                    OR
                    user1ID = :user2ID AND user2ID = :user1ID";

            $stmt = $this->dbConn->prepare($query);

            $stmt->bindParam(':user1ID', $this->user1ID);
            $stmt->bindParam(':user2ID', $this->user2ID);

            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                if (!isset($this->friendshipStatus)) {
                    $this->friendshipStatus = new FriendshipStatus($this->dbConn);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $this->friendshipStatus->loadFromDB($row['statusID']);
                }
                return true;
            }
            return false;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t check if friendship exists', 500);
        }
    }

    public function sendFriendRequest()
    {
        try {
            if ($this->doesFriendshipExist()) {
                throw new ApiException('Friendship already exists!', 400);
            }

            $this->friendshipStatus = new FriendshipStatus($this->dbConn);
            $this->friendshipStatus->createNewEntry($this->user1ID);

            $query = "INSERT INTO " . $this->friendship_table . "
                SET
                    user1ID = :user1ID,
                    user2ID = :user2ID,
                    statusID = :statusID";

            $stmt = $this->dbConn->prepare($query);

            $this->user1ID = htmlspecialchars(strip_tags($this->user1ID));
            $this->user2ID = htmlspecialchars(strip_tags($this->user2ID));

            $stmt->bindParam(':user1ID', $this->user1ID);
            $stmt->bindParam(':user2ID', $this->user2ID);
            $statusid = $this->friendshipStatus->getStatusID();
            $stmt->bindParam(':statusID', $statusid);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t prepare friend request', 500);
        }

        try {
            $this->dbConn->beginTransaction();

            if ($stmt->execute()) {
                $id = $this->dbConn->lastInsertId();
                $this->dbConn->commit();
                
                $this->notifyFriendRequest($id);
                
                return array("message" => "Successfully added friend!", "friendshipID" => $id);
            } else {
                throw new ApiException('Couldn\'t send friend request', 500);
            }
        } catch (PDOException $e) {
            $this->dbConn->rollBack();
            throw new ApiException('Couldn\'t send friend request', 500);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t send friend request', 500);
        }
    }

    private function notifyFriendRequest($friendshipID)
    {
        try {
            $query = "SELECT 
                f.friendshipID, 
                f.statusID,
                fs.status,
                f.user1ID as initiator,
                u1.username as initiatorUsername,
                u1.displayName as initiatorDisplayName,
                u1.profilePicture as initiatorProfilePicture,
                f.user2ID as recipient
            FROM friendship f
            INNER JOIN friendshipStatus fs ON f.statusID = fs.statusID
            INNER JOIN user u1 ON f.user1ID = u1.userID
            WHERE f.friendshipID = :friendshipID";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(':friendshipID', $friendshipID);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return;
            }
            
            $friendshipData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $notificationData = [
                'type' => 'friend_request',
                'friendshipID' => $friendshipData['friendshipID'],
                'initiator' => [
                    'userID' => $friendshipData['initiator'],
                    'username' => $friendshipData['initiatorUsername'],
                    'displayName' => $friendshipData['initiatorDisplayName'],
                    'profilePicture' => $friendshipData['initiatorProfilePicture']
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $notificationDir = __DIR__ . '/../WebSocket/notifications';
            if (!is_dir($notificationDir)) {
                mkdir($notificationDir, 0755, true);
            }
            
            $notificationFile = $notificationDir . '/' . uniqid() . '.json';
            file_put_contents($notificationFile, json_encode([
                'type' => 'friend_request',
                'data' => $notificationData,
                'recipients' => [$friendshipData['recipient']]
            ]));
            
        } catch (Exception $e) {
            error_log('Failed to send friend request notification: ' . $e->getMessage()); 
        }
    }

    public function declineFriendRequest()
    {
        try {
            if (!$this->doesFriendshipExist()) {
                throw new ApiException('Friendship doesn\'t exist!', 400);
            }

            if ($this->friendshipStatus->getStatus() != 0) {
                throw new ApiException('Friendship isn\'t peinding!', 400);
            }
            
            $this->friendshipStatus->updateStatus(-1);
            return true;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t deny friendship' . $e->getMessage() . $e->getLine() . $e->getFile(), 500);
        }
    }

    public function acceptFriendRequest($user)
    {
        try {
            if (!$this->doesFriendshipExist()) {
                throw new ApiException('Friendship does not exist!', 400);
            }

            if ($this->friendshipStatus->getStatus() == 1) {
                throw new ApiException('Friendship already accepted!', 400);
            }

            if ($this->friendshipStatus->getInitiator() == $user) {
                throw new ApiException('You are the initiator of this friendship!', 400);
            }

            if ($this->friendshipStatus->updateStatus(1)) {
                return true;
            } else {
                throw new ApiException('Friendship not accepted!', 500);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t accept friendship', 500);
        }
    }

    //getters
    public function getUser1ID()
    {
        return $this->user1ID;
    }

    public function getUser2ID()
    {
        return $this->user2ID;
    }

    public function getFriendshipStatus()
    {
        return $this->friendshipStatus;
    }
}
