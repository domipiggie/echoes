<?php
class Userinfo
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function getFriendList($user)
    {
        try {
            $query = "SELECT 
                f.friendshipID, 
                f.statusID,
                fs.status,
                CASE 
                    WHEN f.user1ID = :user THEN f.user2ID
                    ELSE f.user1ID
                END as friendID,
                f.user1ID as initiator,
                u.username,
                u.displayName,
                u.profilePicture
            FROM friendship f
            INNER JOIN friendshipStatus fs ON f.statusID = fs.statusID
            INNER JOIN user u ON (
                CASE 
                    WHEN f.user1ID = :user THEN f.user2ID
                    ELSE f.user1ID
                END = u.userID
            )
            WHERE (f.user1ID = :user OR f.user2ID = :user)";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(':user', $user->id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Couldn\'t get friend list', 500);
        }
    }
}
