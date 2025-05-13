<?php
class FileController
{
    public function handleUploadFile()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            if (!isset($_FILES['file'])) {
                throw new ApiException('No file uploaded', 400);
            }

            $result = FileMiddleware::uploadFile($user->id, $_FILES['file']);

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to upload file: ' . $e->getMessage(), 500);
        }
    }

    public function handleGetUserFiles()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            $result = FileMiddleware::getUserFiles($user->id);

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get user files: ' . $e->getMessage(), 500);
        }
    }

    public function handleServeFile($uniqueName)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($uniqueName) || empty($uniqueName)) {
                throw new ApiException('File name is required', 400);
            }

            $fileInfo = FileMiddleware::getFileByUniqueName($uniqueName);
            
            // Set appropriate headers for file download
            header('Content-Type: ' . $fileInfo['fileType']);
            header('Content-Disposition: inline; filename="' . $fileInfo['fileName'] . '"');
            header('Content-Length: ' . $fileInfo['fileSize']);
            
            // Output the file and exit
            readfile($fileInfo['filePath']);
            exit;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to serve file: ' . $e->getMessage(), 500);
        }
    }

    public function handleDeleteFile($fileID)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'DELETE') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($fileID) || empty($fileID)) {
                throw new ApiException('File ID is required', 400);
            }

            $user = JWTTools::validateToken();

            $result = FileMiddleware::deleteFile($fileID, $user->id);

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to delete file: ' . $e->getMessage(), 500);
        }
    }

    public function handleGetTotalFileSize()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            $result = FileMiddleware::getTotalUserFileSize($user->id);

            ResponseHandler::success($result);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get total file size: ' . $e->getMessage(), 500);
        }
    }
}