<?php
class UserinfoController
{
    public static function handleGetUserInfo($id, $dbConn)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($id)) {
                throw new ApiException('Missing required fields', 400);
            }

            $user = new User($dbConn);
            $user->loadFromID($id);

            $response = array("userID" => $user->getUserID(), "username" => $user->getUsername(), "displayName" => $user->getDisplayName(), "profilePicture" => $user->getProfilePicture());
            echo json_encode($response);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get user info', 500);
        }
    }
    
    public static function handleGetFriendList($dbConn)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method!', 405);
            }

            $user = AuthMiddleware::validateToken();
            $userInfo = new Userinfo($dbConn);
            $result = $userInfo->getFriendList($user);
            echo json_encode($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get friend list', 500);
        }
    }
}
