<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$rootDir = dirname(__DIR__);

//Controllers
require_once $rootDir . '/src/controllers/AuthController.php';
require_once $rootDir . '/src/controllers/UserinfoController.php';
require_once $rootDir . '/src/controllers/MessageController.php';
require_once $rootDir . '/src/controllers/FileController.php';
require_once $rootDir . '/src/controllers/ProfilePictureController.php';
//Models
require_once $rootDir . '/src/models/User.php';
require_once $rootDir . '/src/models/RefreshToken.php';
require_once $rootDir . '/src/models/Userinfo.php';
require_once $rootDir . '/src/models/Message.php';
require_once $rootDir . '/src/models/File.php';
require_once $rootDir . '/src/models/Group.php';
//Middleware
require_once $rootDir . '/src/middleware/AuthMiddleware.php';
require_once $rootDir . '/src/middleware/UserinfoMiddleware.php';
require_once $rootDir . '/src/middleware/MessageMiddleware.php';
require_once $rootDir . '/src/middleware/FileMiddleware.php';
//Config
require_once $rootDir . '/src/config/database.php';
require_once $rootDir . '/src/config/core.php';
//Other
require_once $rootDir . '/src/exceptions/ApiException.php';
require_once $rootDir . '/src/utils/ErrorHandler.php';
require_once $rootDir . '/src/utils/ResponseHandler.php';
require_once $rootDir . '/src/utils/JWTTools.php';
require_once $rootDir . '/src/utils/DatabaseOperations.php';

set_exception_handler([ErrorHandler::class, 'handleError']);

try {
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

            $auth = new AuthController();

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

        case "userInfo":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }

            $userInfo = new UserinfoController();

            switch ($uri[2]) {
                case "id":
                    if (!isset($uri[3])) {
                        throw new ApiException('Provide a userid', 400);
                    }

                    $userInfo->handleGetUserInfo($uri[3]);
                    break;
                case "username":
                    if (!isset($uri[3])) {
                        throw new ApiException('Provide a username', 400);
                    }

                    $userInfo->handleSearchUser($uri[3]);
                    break;
                default:
                    throw new ApiException('Invalid route', 404);
                    break;
            }
            break;
            
        case "files":
            $fileController = new FileController();
            
            if (!isset($uri[2])) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $fileController->handleUploadFile();
                } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $fileController->handleGetUserFiles();
                } else {
                    throw new ApiException('Invalid method', 405);
                }
            } else if ($uri[2] === 'size') {
                $fileController->handleGetTotalFileSize();
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $fileController->handleServeFile($uri[2]);
                } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    $fileController->handleDeleteFile($uri[2]);
                } else {
                    throw new ApiException('Invalid method', 405);
                }
            }
            break;

        case "userData":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }

            $userInfo = new UserinfoController();

            switch ($uri[2]) {
                case "friendList":
                    $userInfo->handleGetFriendList();
                    break;
                case "friendChannels":
                    $userInfo->handleGetFriendChannelList();
                    break;
                case "groupChannels":
                    $userInfo->handleGetGroupChannelList();
                    break;
                case "updateProfile":
                    $userInfo->handleUpdateUserProfile($data);
                    break;
                default:
                    throw new ApiException('Invalid route', 404);
                    break;
            }
            break;

        case "messages":
            if (!isset($uri[2])) {
                throw new ApiException('Provide a channel ID', 400);
            }

            $messageController = new MessageController();

            $messageController->handleGetChannelMessages($uri[2]);
            break;

        case "pfp":
            if (!isset($uri[2])) {
                throw new ApiException('Invalid route', 404);
            }
            
            $profilePictureController = new ProfilePictureController();
            
            switch ($uri[2]) {
                case "user":
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $profilePictureController->handleUserProfilePictureUpload();
                    } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($uri[3])) {
                        $profilePictureController->handleServeUserProfilePicture($uri[3]);
                    } else {
                        throw new ApiException('Invalid method or missing user ID', 400);
                    }
                    break;
                case "group":
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($uri[3])) {
                        $profilePictureController->handleGroupProfilePictureUpload($uri[3]);
                    } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($uri[3])) {
                        $profilePictureController->handleServeGroupProfilePicture($uri[3]);
                    } else {
                        throw new ApiException('Invalid method or missing group ID', 400);
                    }
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
