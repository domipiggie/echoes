<?php
require_once '../middleware/FriendshipMiddleware.php';
class FriendshipController
{
    private $friendship;

    public function __construct($db)
    {
        $this->friendship = new Friendship($db);
    }

    public function sendFriendRequest($user, $data)
    {
        if (!isset($data['userID'])) {
            return array("message" => "UserID not provided!");
        }
        
        $this->friendship->user1ID = $user->id;
        $this->friendship->user2ID = $data['userID'];

        if (!FriendshipMiddleware::validateUserIDs($this->friendship)){
            return array(
                "status"=>"UserIDs can't match."
            );
        }

        if ($this->friendship->sendFriendRequest()) {
            return array(
                "status"=>"Successfully added friend!"
            );
        } else {
            return array(
                "status"=>"Error while adding friend!"
            );
        }
    }
}
?>