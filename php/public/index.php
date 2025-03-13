<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//Controllers
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/FriendshipController.php';
require_once '../src/controllers/ChannelController.php';
require_once '../src/controllers/UserinfoController.php';
require_once '../src/controllers/MessageController.php';
//Models
require_once '../src/models/User.php';
require_once '../src/models/RefreshToken.php';
require_once '../src/models/Friendship.php';
require_once '../src/models/FriendshipStatus.php';
require_once '../src/models/Message.php';
require_once '../src/models/Userinfo.php';
//Middleware
require_once '../src/middleware/AuthMiddleware.php';
//Config
require_once '../src/config/database.php';
require_once '../src/config/core.php';
//Other
require_once '../src/exceptions/ApiException.php';
require_once '../src/utils/ErrorHandler.php';

// Set error handling
set_exception_handler([ErrorHandler::class, 'handleError']);

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new AuthController($db);
    $friendship = new FriendshipController($db);
    $channel = new ChannelController($db);
    $message = new MessageController($db);

    $request_method = $_SERVER["REQUEST_METHOD"];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);
    $data = json_decode(file_get_contents("php://input"), true);

    // Routes
    switch ($uri[1]) {
        case "auth":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }

            switch ($uri[2]) {
                case "login":
                    $auth->handleLogin($data);
                    break;
                case "register":
                    $auth->handleRegister($data);
                    break;
                case "refresh":
                    $auth->handleRefresh($data);
                    break;
                case "logout":
                    $auth->handleLogout($data);
                    break;
                default:
                    throw new ApiException('Invalid route', 404);
                    break;
            }
            break;

        case "friend":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }

            switch ($uri[2]) {
                case "add":
                    $result = $friendship->handleAddFriend($data);
                    $channel->createFriendshipChannel($result['friendshipID']);
                    break;
                case "accept":
                    $friendship->handleAcceptFriend($data);
                    break;
                case "deny":
                    $friendship->handleDeclineFriend($data);
                    break;
                case "revoke":
                    //TODO: create friend request revoke logic
                    break;
                case "block":
                    //TODO: create blocking functionality
                    break;
                case "unblock":
                    //TODO: create unblock functionality
                    break;
                default:
                    throw new ApiException('Invalid route', 404);
                    break;
            }
            break;

        case "usrinfo":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }

            switch ($uri[2]) {
                case "friendlist":
                    UserinfoController::handleGetFriendList($db);
                    break;
                case "userdata":
                    UserinfoController::handleGetUserInfo($uri[3], $db);
                    break;
                case "channellist":
                    $channel->handleGetChannelList();
                    break;
                default:
                    throw new ApiException('Invalid route', 404);
                    break;
            }
            break;

        case "message":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }

            switch ($uri[2]) {
                case "send":
                    $message->handleSendMessage($data);
                    break;
                case "get":
                    $message->handleGetMessages($_GET);
                    break;
                default:
                    throw new ApiException('Invalid route', 404);
                    break;
            }
            break;

        default:
            throw new ApiException('Invalid route', 404);
            break;
    }
} catch (PDOException $e) {
    throw new ApiException('Database error: ' . $e->getMessage(), 500);
} catch (ApiException $apie) {
    throw new ApiException($apie->getMessage(), $apie->getStatusCode());
} catch (Exception $e) {
    throw new ApiException($e->getMessage(), 500);
}
