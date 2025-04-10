<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$rootDir = dirname(__DIR__);

//Controllers
require_once $rootDir. '/src/controllers/AuthController.php';
//Models
require_once $rootDir. '/src/models/User.php';
require_once $rootDir . '/src/models/RefreshToken.php';
//Middleware
require_once $rootDir. '/src/middleware/AuthMiddleware.php';
//Config
require_once $rootDir . '/src/config/database.php';
require_once $rootDir . '/src/config/core.php';
//Other
require_once $rootDir . '/src/exceptions/ApiException.php';
require_once $rootDir . '/src/utils/ErrorHandler.php';
require_once $rootDir . '/src/utils/JWTTools.php';
require_once $rootDir . '/src/utils/DatabaseOperations.php';

// Set error handling
set_exception_handler([ErrorHandler::class, 'handleError']);

try {
    $database = new Database();
    $db = $database->getConnection();

    $request_method = $_SERVER["REQUEST_METHOD"];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);
    $data = json_decode(file_get_contents("php://input"), true);

    $auth = new AuthController($db);

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
