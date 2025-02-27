<?php
require_once '../src/middleware/FriendshipMiddleware.php';
class FriendshipController
{
    private $friendship;
    private $db;
    private $channel;

    public function __construct($db)
    {
        $this->friendship = new Friendship($db);
        $this->channel = new ChannelController($db);
        $this->db = $db;
    }

    public function sendFriendRequest($user, $data)
    {
        if (!isset($data['userID'])) {
            return array("message" => "UserID not provided!");
        }

        $this->friendship->user1ID = $user->id;
        $this->friendship->user2ID = $data['userID'];

        if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
            return array(
                "status" => "UserIDs can't match."
            );
        }
        $id = $this->friendship->sendFriendRequest();
        if ($id != false) {
            $this->channel->createFriendshipChannel($id);
            return array(
                "status" => "Successfully added friend!"
            );
        } else {
            return array(
                "status" => "Error while adding friend!"
            );
        }
    }

    public function declineFriendRequest($user, $data)
    {
        if (!isset($data['userID'])) {
            return array("message" => "UserID not provided!");
        }

        $this->friendship->user1ID = $user->id;
        $this->friendship->user2ID = $data['userID'];

        if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
            return array("status" => "UserIDs can't match.");
        }

        if ($this->friendship->declineFriendRequest()) {
            return array(
                "status" => "Successfully denied friend request!"
            );
        } else {
            return array(
                "status" => "Error while denying friend request!"
            );
        }
    }

    public function acceptFriendRequest($user, $data)
    {
        if (!isset($data['userID'])) {
            return array("message" => "UserID not provided!");
        }

        $this->friendship->user1ID = $user->id;
        $this->friendship->user2ID = $data['userID'];

        if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
            return array("status" => "UserIDs can't match.");
        }

        if ($this->friendship->acceptFriendRequest($user->id)) {
            return array(
                "status" => "Successfully accepted friend request!"
            );
        } else {
            return array(
                "status" => "Error while accepting friend request!"
            );
        }
    }

    public function getFriendList($user)
    {
        $query = "SELECT friendshipID, user1ID, user2ID, status
                    FROM friendship INNER JOIN friendshipStatus ON friendship.statusID = friendshipStatus.statusID
                        WHERE friendshipStatus.status = 1;";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function handleAddFriend($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        $user = AuthMiddleware::validateToken();
        $result = $this->sendFriendRequest($user, $data);
        echo json_encode($result);
    }

    public function handleDeclineFriend($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        $user = AuthMiddleware::validateToken();
        $result = $this->declineFriendRequest($user, $data);
        echo json_encode($result);
    }

    public function handleAcceptFriend($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        $user = AuthMiddleware::validateToken();
        $result = $this->acceptFriendRequest($user, $data);
        echo json_encode($result);
    }

    public function handleGetFriendList()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        $user = AuthMiddleware::validateToken();
        $result = $this->getFriendList($user);
        echo json_encode($result);
    }
}
?>