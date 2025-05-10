<?php

class FileMiddleware
{
    public static function uploadFile($userID, $file, $dbConn)
    {
        try {
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                $errorMessage = self::getFileUploadErrorMessage($file['error']);
                throw new ApiException($errorMessage, 400);
            }

            $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
            if ($file['size'] > $maxFileSize) {
                throw new ApiException('File size exceeds the limit of 10MB', 400);
            }

            $fileName = basename($file['name']);
            $fileSize = $file['size'];
            $fileType = $file['type'];
            $fileTmpPath = $file['tmp_name'];

            $uniqueName = uniqid() . '_' . $fileName;
            
            $uploadDir = __DIR__ . '/../../uploads/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $uniqueName;

            if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
                throw new ApiException('Failed to move uploaded file', 500);
            }

            $fileModel = new File($dbConn);
            $fileID = $fileModel->createFile($userID, $fileName, $uniqueName, $fileSize);

            return [
                'fileID' => $fileID,
                'fileName' => $fileName,
                'size' => $fileSize,
                'uploadedAt' => date('Y-m-d H:i:s')
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('File upload failed: ' . $e->getMessage(), 500);
        }
    }

    public static function getUserFiles($userID, $dbConn)
    {
        try {
            $fileModel = new File($dbConn);
            return $fileModel->getUserFiles($userID);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get user files: ' . $e->getMessage(), 500);
        }
    }

    public static function getFileByUniqueName($uniqueName, $dbConn)
    {
        try {
            $fileModel = new File($dbConn);
            $fileInfo = $fileModel->getFileByUniqueName($uniqueName);
            
            // Get the file path
            $filePath = __DIR__ . '/../../uploads/' . $fileInfo['unique_name'];
            
            if (!file_exists($filePath)) {
                throw new ApiException('File not found on server', 404);
            }
            
            return [
                'filePath' => $filePath,
                'fileName' => $fileInfo['file_name'],
                'fileType' => mime_content_type($filePath),
                'fileSize' => $fileInfo['size']
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get file: ' . $e->getMessage(), 500);
        }
    }

    public static function deleteFile($fileID, $userID, $dbConn)
    {
        try {
            $fileModel = new File($dbConn);
            
            // Get file info first to get the unique name
            $fileInfo = $fileModel->getFileById($fileID);
            
            if ($fileInfo['userID'] != $userID) {
                throw new ApiException('You do not have permission to delete this file', 403);
            }
            
            // Delete from database
            $fileModel->deleteFile($fileID, $userID);
            
            // Delete the physical file
            $filePath = __DIR__ . '/../../uploads/' . $fileInfo['unique_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return ['message' => 'File deleted successfully'];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to delete file: ' . $e->getMessage(), 500);
        }
    }

    private static function getFileUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    public static function getTotalUserFileSize($userID, $dbConn)
    {
        try {
            $fileModel = new File($dbConn);
            $totalSize = $fileModel->getTotalUserFileSize($userID);
            
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $size = $totalSize;
            $unitIndex = 0;
            
            while ($size >= 1024 && $unitIndex < count($units) - 1) {
                $size /= 1024;
                $unitIndex++;
            }
            
            return [
                'total_size_bytes' => $totalSize,
                'total_size_formatted' => round($size, 2) . ' ' . $units[$unitIndex],
                'file_count' => count($fileModel->getUserFiles($userID))
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get total file size: ' . $e->getMessage(), 500);
        }
    }
}