<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../src/config/database.php';
require_once '../src/config/core.php';
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/FriendshipController.php';
require_once '../src/controllers/ChannelController.php';
require_once '../src/middleware/AuthMiddleware.php';
require_once '../src/models/User.php';
require_once '../src/models/RefreshToken.php';
require_once '../src/models/Friendship.php';
require_once '../src/models/FriendshipStatus.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$friendship = new FriendshipController($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$data = json_decode(file_get_contents("php://input"), true);

// Routes
switch ($uri[1]) {
    case "auth":
        isUriSet($uri[2]);

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
                noRouteFound();
                break;
        }
        break;

    case "friend":
        isUriSet($uri[2]);

        switch ($uri[2]) {
            case "add":
                $friendship->handleAddFriend($data);
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
                noRouteFound();
                break;
        }
        break;
    
    case "usrinfo":
        isUriSet($uri[2]);

        switch ($uri[2]) {
            case "friendlist":
                $friendship->handleGetFriendList();
                break;
            default:
                noRouteFound();
                break;
        }
        break;
    
    default:
        noRouteFound();
        break;
}

function isUriSet($uri)
{
    if (!isset($uri)) {
        http_response_code(404);
        echo json_encode(array("message" => "Route not found."));
        die;
    }
}

function noRouteFound()
{
    http_response_code(404);
    echo json_encode(array("message" => "Route not found."));
}