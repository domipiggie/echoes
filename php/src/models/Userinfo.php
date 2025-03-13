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
            $query = "SELECT friendshipID, user1ID, user2ID, status
            FROM friendship INNER JOIN friendshipStatus ON friendship.statusID = friendshipStatus.statusID
                WHERE (friendship.user1ID = :user OR friendship.user2ID = :user);";

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
