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

            ResponseHandler::success($response);
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

            ResponseHandler::success($result);
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

            ResponseHandler::success($result);
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

            ResponseHandler::success($result);
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

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get group channel list ' . $e->getMessage(), 500);
        }
    }
    
    public function handleUpdateUserProfile($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
                throw new ApiException('Invalid method', 405);
            }
            
            if ((!isset($data['username']) || empty($data['username'])) && 
                (!isset($data['email']) || empty($data['email']))) {
                throw new ApiException('At least one field (username or email) must be provided', 400);
            }
            
            $userData = JWTTools::validateToken();
            $userId = $userData->id;
            
            $user = new User($this->dbConn);
            if (!$user->loadFromID($userId)) {
                throw new ApiException('User not found', 404);
            }
            
            $updated = false;
            $response = [];
            
            if (isset($data['username']) && !empty($data['username'])) {
                $user->updateUsername($data['username']);
                $updated = true;
                $response['username'] = $data['username'];
            }

            if (isset($data['email']) && !empty($data['email'])) {
                $user->updateEmail($data['email']);
                $updated = true;
                $response['email'] = $data['email'];
            }
            
            if (!$updated) {
                throw new ApiException('No fields were updated', 400);
            }
            
            $accessToken = JWTTools::generateAccessToken($user->getUserID(), $user->getUsername(), $user->getEmail());
            
            $response['access_token'] = $accessToken['token'];
            $response['token_type'] = 'Bearer';
            $response['expires_in'] = $accessToken['expires_in'];
            $response['message'] = 'Profile updated successfully';
            
            ResponseHandler::success($response);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update user profile: ' . $e->getMessage(), 500);
        }
    }
}
