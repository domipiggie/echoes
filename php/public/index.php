<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';
require_once '../config/core.php';
require_once '../controllers/AuthController.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../models/User.php';
require_once '../models/RefreshToken.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);

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
            echo json_encode(array('message' => 'Invalid method.'));
        }
        break;

    case 'login':
        if ($request_method == "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $auth->login($data);
            echo json_encode($result);
        } else {
            echo json_encode(array('message' => 'Invalid method.'));
        }
        break;

    case 'protected':
        if ($request_method == "GET") {
            $user = AuthMiddleware::validateToken();
            echo json_encode(array(
                "message" => "Access granted.",
                "user" => $user
            ));
        } else {
            echo json_encode(array('message' => 'Invalid method.'));
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
            echo json_encode(array('message' => 'Invalid method.'));
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
            echo json_encode(array('message' => 'Invalid method.'));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Route not found."));
        break;
}