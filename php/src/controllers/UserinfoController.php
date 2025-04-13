<?php
class UserinfoController
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function handleGetUserInfo($userId)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($userId) || empty($userId)) {
                throw new ApiException('Missing required fields', 400);
            }

            $response = UserinfoMiddleware::getUserInfo($userId, $this->dbConn);

            echo json_encode($response);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get user info', 500);
        }
    }

    public function handleSearchUser($username)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($username) || empty($username)) {
                throw new ApiException('Username parameter is required', 400);
            }

            $result = UserinfoMiddleware::searchUser($username, $this->dbConn);

            if (!$result) {
                throw new ApiException('User not found', 404);
            }

            echo $result;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to search for user', 500);
        }
    }

    public function handleGetFriendList()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method!', 405);
            }

            $user = JWTTools::validateToken();

            $result = UserinfoMiddleware::getFriendList($user->id, $this->dbConn);

            echo json_encode($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get friend list', 500);
        }
    }

    public function handleGetFriendChannelList()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            $result = UserinfoMiddleware::getFriendChannelList($user->id, $this->dbConn);

            echo json_encode($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get friend channel list ' . $e->getMessage(), 500);
        }
    }

    public function handleGetGroupChannelList()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            $result = UserinfoMiddleware::getGroupChannelList($user->id, $this->dbConn);

            echo json_encode($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get group channel list ' . $e->getMessage(), 500);
        }
    }
}
