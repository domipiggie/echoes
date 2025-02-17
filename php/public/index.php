<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    exit();
}

require_once '../src/config/database.php';
require_once '../src/config/core.php';
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/FriendshipController.php';
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

// Routes
switch ($uri[1]) {
    case 'register':
        if ($request_method == "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $auth->register($data);
            echo json_encode($result);
        } else {
            invalidMethodResponse();
        }
        break;

    case 'login':
        if ($request_method == "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $auth->login($data);
            echo json_encode($result);
        } else {
            invalidMethodResponse();
        }
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
        if ($request_method == "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['refresh_token'])) {
                $result = $auth->refresh($data['refresh_token']);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Refresh token is required"));
            }
        } else {
            invalidMethodResponse();
        }
        break;

    case 'logout':
        if ($request_method == "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['refresh_token'])) {
                $result = $auth->logout($data['refresh_token']);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Refresh token is required"));
            }
        } else {
            invalidMethodResponse();
        }
        break;

    case 'addfriend':
        if ($request_method == "POST") {
            $user = AuthMiddleware::validateToken();
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $friendship->sendFriendRequest($user, $data);
            echo json_encode($result);
        } else {
            invalidMethodResponse();
        }
        break;

    case 'declinefriend':
        if ($request_method == "POST") {
            $user = AuthMiddleware::validateToken();
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $friendship->declineFriendRequest($user, $data);
            echo json_encode($result);
        } else {
            invalidMethodResponse();
        }
        break;

    case 'acceptfriend':
        if ($request_method == "POST") {
            $user = AuthMiddleware::validateToken();
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $friendship->acceptFriendRequest($user, $data);
            echo json_encode($result);
        } else {
            invalidMethodResponse();
        }
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