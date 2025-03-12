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
        try {
            $this->friendship = new Friendship($this->dbConn, $user->id, $data["userID"]);

            if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
                throw new ApiException('UserIDs can\'t match.', 400);
            }

            $id = $this->friendship->sendFriendRequest();

            if ($id != false) {
                return $id;
            } else {
                throw new ApiException('Error while sending friend request', 500);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function declineFriendRequest($user, $data)
    {
        try {
            $this->friendship = new Friendship($this->dbConn, $user->id, $data["userID"]);

            if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
                throw new ApiException('UserIDs can\'t match.', 400);
            }

            if ($this->friendship->declineFriendRequest()) {
                return array(
                    "status" => "Successfully denied friend request!"
                );
            } else {
                throw new ApiException('Error while denying friend request', 500);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), 500);
        }
        if (!isset($data['userID'])) {
            return array("message" => "UserID not provided!");
        }
    }

    public function acceptFriendRequest($user, $data)
    {
        try {
            $this->friendship = new Friendship($this->dbConn, $user->id, $data["userID"]);

            if (!FriendshipMiddleware::validateUserIDs($this->friendship)) {
                throw new ApiException('UserIDs can\'t match.', 400);
            }

            if ($this->friendship->acceptFriendRequest($user->id)) {
                return array(
                    "status" => "Successfully accepted friend request!"
                );
            } else {
                throw new ApiException('Error while accepting friend request', 500);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function handleAddFriend($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD']!= 'POST') {
                throw new ApiException('Invalid method!', 405);
            }

            if (!isset($data['userID'])) {
                throw new ApiException('Missing required fields', 400);
            }

            $user = AuthMiddleware::validateToken();

            if ($data['userID'] === $user->id) {
                throw new ApiException('Cannot add yourself as friend', 400);
            }

            $result = $this->sendFriendRequest($user, $data);
            echo json_encode($result);
            return $result;
        } catch (ApiException $e) {
            echo json_encode(['message' => $e->getMessage()]);
        }
    }

    public function handleDeclineFriend($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD']!= 'PUT') {
                throw new ApiException('Invalid method!', 405);
            }

            if (!isset($data['userID'])) {
                throw new ApiException('Missing required fields', 400);
            }

            $user = AuthMiddleware::validateToken();
            $result = $this->declineFriendRequest($user, $data);
            echo json_encode($result);
        } catch (ApiException $e) {
            echo json_encode(['message' => $e->getMessage()]);
        }
    }

    public function handleAcceptFriend($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD']!= 'PUT') {
                throw new ApiException('Invalid method!', 405);
            }

            if (!isset($data['userID'])) {
                throw new ApiException('Missing required fields', 400);
            }

            $user = AuthMiddleware::validateToken();
            $result = $this->acceptFriendRequest($user, $data);
            echo json_encode($result);
        } catch (ApiException $e) {
            echo json_encode(['message' => $e->getMessage()]);
        }
    }

    public function handleGetFriendList()
    {
        try {
            if ($_SERVER['REQUEST_METHOD']!= 'GET') {
                throw new ApiException('Invalid method!', 405);
            }

            $user = AuthMiddleware::validateToken();
            $result = $this->friendship->getFriendList($user);
            echo json_encode($result);
        } catch (ApiException $e) {
            echo json_encode(['message' => $e->getMessage()]);
        }
    }
}
