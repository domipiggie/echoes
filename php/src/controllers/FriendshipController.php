<?php
require_once '../src/middleware/FriendshipMiddleware.php';
class FriendshipController
{
    private $friendship;
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function sendFriendRequest($user, $data)
    {
        if (!isset($data['userID'])) {
            return array("message" => "UserID not provided!");
        }

        $this->friendship = new Friendship($this->dbConn, $user->id, $data["userID"]);

        if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
            return array(
                "status" => "UserIDs can't match."
            );
        }

        $id = $this->friendship->sendFriendRequest();

        if ($id != false) {
            return $id;
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

        $this->friendship = new Friendship($this->dbConn, $user->id, $data["userID"]);

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

        $this->friendship = new Friendship($this->dbConn, $user->id, $data["userID"]);

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

    public function handleAddFriend($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        $user = AuthMiddleware::validateToken();
        $result = $this->sendFriendRequest($user, $data);
        echo $result;
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

        $this->friendship = new Friendship($this->dbConn, null, null);

        $user = AuthMiddleware::validateToken();
        $result = $this->friendship->getFriendList($user);
        echo json_encode($result);
    }
}
?>