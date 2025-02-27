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
    case 'register':
        $auth->handleRegister($data);
        break;

    case 'login':
        $auth->handleLogin($data);
        break;

    case 'protected':
        if ($request_method == "GET") {
            $user = AuthMiddleware::validateToken();
            echo json_encode(array(
                "message" => "Access granted.",
                "user" => $user->id
            ));
        } else {
            invalidMethodResponse();
        }
        break;

    case 'refresh':
        $auth->handleRefresh($data);
        break;

    case 'logout':
        $auth->handleLogout($data);
        break;

    case 'addfriend':
        $friendship->handleAddFriend($data);
        break;

    case 'declinefriend':
        $friendship->handleDeclineFriend($data);
        break;

    case 'acceptfriend':
        $friendship->handleAcceptFriend($data);
        break;
    
    case 'getFriendList':
        $friendship->handleGetFriendList();
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Route not found."));
        break;
}

function invalidMethodResponse()
{
    http_response_code(405);
    echo json_encode(array('message' => 'Invalid method.'));
}