<?php
class ProfilePictureController
{
    public function handleUserProfilePictureUpload()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            if (!isset($_FILES['file'])) {
                throw new ApiException('No file uploaded', 400);
            }

            $result = FileMiddleware::uploadUserProfilePicture($user->id, $_FILES['file']);

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to upload profile picture: ' . $e->getMessage(), 500);
        }
    }

    public function handleServeUserProfilePicture($userID)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($userID) || empty($userID)) {
                throw new ApiException('User ID is required', 400);
            }

            $fileInfo = FileMiddleware::getUserProfilePicture($userID);
            
            while (ob_get_level()) ob_end_clean();
            
            header('Content-Type: ' . $fileInfo['fileType']);
            header('Content-Disposition: inline; filename="' . $fileInfo['fileName'] . '"');
            header('Content-Length: ' . $fileInfo['fileSize']);
            header('Cache-Control: no-cache, must-revalidate');
            
            readfile($fileInfo['filePath']);
            exit;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to serve profile picture: ' . $e->getMessage(), 500);
        }
    }

    public function handleGroupProfilePictureUpload($groupID)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            if (!isset($_FILES['file'])) {
                throw new ApiException('No file uploaded', 400);
            }

            if (!isset($groupID) || empty($groupID)) {
                throw new ApiException('Group ID is required', 400);
            }

            $result = FileMiddleware::uploadGroupProfilePicture($groupID, $user->id, $_FILES['file']);

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to upload group profile picture: ' . $e->getMessage(), 500);
        }
    }

    public function handleServeGroupProfilePicture($groupID)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($groupID) || empty($groupID)) {
                throw new ApiException('Group ID is required', 400);
            }

            $fileInfo = FileMiddleware::getGroupProfilePicture($groupID);
            
            while (ob_get_level()) ob_end_clean();
            
            header('Content-Type: ' . $fileInfo['fileType']);
            header('Content-Disposition: inline; filename="' . $fileInfo['fileName'] . '"');
            header('Content-Length: ' . $fileInfo['fileSize']);
            header('Cache-Control: no-cache, must-revalidate');

            readfile($fileInfo['filePath']);
            exit;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to serve group profile picture: ' . $e->getMessage(), 500);
        }
    }
}