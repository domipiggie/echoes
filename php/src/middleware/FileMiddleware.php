<?php

class FileMiddleware
{
    private static $USER_PROFILE_DIR = '/../../uploads/userProfiles/';
    private static $GROUP_PROFILE_DIR = '/../../uploads/groupProfiles/';
    private static $PROFILE_PIC_MAX_SIZE = 5 * 1024 * 1024; // 5MB
    
    public static function uploadFile($userID, $file)
    {
        try {
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                $errorMessage = self::getFileUploadErrorMessage($file['error']);
                throw new ApiException($errorMessage, 400);
            }

            $maxFileSize = 100 * 1024 * 1024; // 100MB in bytes
            if ($file['size'] > $maxFileSize) {
                throw new ApiException('File size exceeds the limit of 100MB', 400);
            }

            $maxTotalStorage = 1000 * 1024 * 1024; // 1000MB in bytes
            $fileModel = new File();
            $currentTotalSize = $fileModel->getTotalUserFileSize($userID);
            
            if (($currentTotalSize + $file['size']) > $maxTotalStorage) {
                throw new ApiException('Upload would exceed your storage limit of 1000MB. Please delete some files first.', 400);
            }

            $fileName = basename($file['name']);
            $fileSize = $file['size'];
            $fileType = $file['type'];
            $fileTmpPath = $file['tmp_name'];

            $sanitizedFileName = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '_', $fileName);
            $uniqueName = uniqid() . '_' . $sanitizedFileName;
            
            $uploadDir = __DIR__ . '/../../uploads/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $uniqueName;

            if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
                throw new ApiException('Failed to move uploaded file', 500);
            }

            $fileID = $fileModel->createFile($userID, $fileName, $uniqueName, $fileSize);

            return [
                'fileID' => $fileID,
                'fileName' => $fileName,
                'uniqueName' => $uniqueName,
                'size' => $fileSize,
                'uploadedAt' => date('Y-m-d H:i:s')
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('File upload failed: ' . $e->getMessage(), 500);
        }
    }

    public static function getUserFiles($userID)
    {
        try {
            $fileModel = new File();
            return $fileModel->getUserFiles($userID);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get user files: ' . $e->getMessage(), 500);
        }
    }

    public static function getFileByUniqueName($uniqueName)
    {
        try {
            $fileModel = new File();
            $fileInfo = $fileModel->getFileByUniqueName($uniqueName);
            
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

    public static function deleteFile($fileID, $userID)
    {
        try {
            $fileModel = new File();
            
            $fileInfo = $fileModel->getFileById($fileID);
            
            if ($fileInfo['userID'] != $userID) {
                throw new ApiException('You do not have permission to delete this file', 403);
            }
            
            $fileModel->deleteFile($fileID, $userID);
            
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

    public static function getTotalUserFileSize($userID)
    {
        try {
            $fileModel = new File();
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
    
    public static function uploadUserProfilePicture($userID, $file)
    {
        try {
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                $errorMessage = self::getFileUploadErrorMessage($file['error']);
                throw new ApiException($errorMessage, 400);
            }
            
            $fileInfo = getimagesize($file['tmp_name']);
            if ($fileInfo === false) {
                throw new ApiException('Uploaded file is not an image', 400);
            }
            
            if ($file['size'] > self::$PROFILE_PIC_MAX_SIZE) {
                throw new ApiException('Profile picture size exceeds the limit of 5MB', 400);
            }
            
            $fileTmpPath = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            
            $fileName = $userID . '.jpg';
            
            $uploadDir = __DIR__ . self::$USER_PROFILE_DIR;
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $fileName;
            
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
                throw new ApiException('Failed to move uploaded file', 500);
            }
            
            $timestamp = time();
            $user = new User();
            $user->loadFromID($userID);
            $user->setProfilePicture('/pfp/user/' . $userID . '?v=' . $timestamp);
            
            return [
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'profilePicture' => '/pfp/user/' . $userID . '?v=' . $timestamp
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Profile picture upload failed: ' . $e->getMessage(), 500);
        }
    }
    
    public static function getUserProfilePicture($userID)
    {
        try {
            $filePath = __DIR__ . self::$USER_PROFILE_DIR . $userID . '.jpg';
            
            if (!file_exists($filePath)) {
                $filePath = __DIR__ . self::$USER_PROFILE_DIR . 'default.jpg';
                
                if (!file_exists($filePath)) {
                    throw new ApiException('Profile picture not found', 404);
                }
            }
            
            return [
                'filePath' => $filePath,
                'fileName' => $userID . '.jpg',
                'fileType' => mime_content_type($filePath),
                'fileSize' => filesize($filePath)
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get profile picture: ' . $e->getMessage(), 500);
        }
    }
    
    public static function uploadGroupProfilePicture($channelID, $userID, $file)
    {
        try {
            $groupModel = new Group();
            $groupID = $groupModel->getGroupIdFromChannel($channelID);
            $isOwner = $groupModel->isGroupOwner($userID, $groupID);
            
            if (!$isOwner) {
                throw new ApiException('Only the group owner can change the group profile picture', 403);
            }
            
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                $errorMessage = self::getFileUploadErrorMessage($file['error']);
                throw new ApiException($errorMessage, 400);
            }
            
            $fileInfo = getimagesize($file['tmp_name']);
            if ($fileInfo === false) {
                throw new ApiException('Uploaded file is not an image', 400);
            }
            
            if ($file['size'] > self::$PROFILE_PIC_MAX_SIZE) {
                throw new ApiException('Profile picture size exceeds the limit of 5MB', 400);
            }
            
            $fileTmpPath = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            
            $fileName = $groupID . '.jpg';
            
            $uploadDir = __DIR__ . self::$GROUP_PROFILE_DIR;
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $fileName;
            
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
                throw new ApiException('Failed to move uploaded file', 500);
            }
            
            $timestamp = time();
            $groupModel->setProfilePicture('/pfp/group/' . $groupID . '?v=' . $timestamp, $groupID);
            
            return [
                'success' => true,
                'message' => 'Group profile picture uploaded successfully',
                'profilePicture' => '/pfp/group/' . $groupID . '?v=' . $timestamp
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Group profile picture upload failed: ' . $e->getMessage(), 500);
        }
    }
    
    public static function getGroupProfilePicture($groupID)
    {
        try {
            $filePath = __DIR__ . self::$GROUP_PROFILE_DIR . $groupID . '.jpg';
            
            if (!file_exists($filePath)) {
                $filePath = __DIR__ . self::$GROUP_PROFILE_DIR . 'default.jpg';
                
                if (!file_exists($filePath)) {
                    throw new ApiException('Group profile picture not found', 404);
                }
            }
            
            return [
                'filePath' => $filePath,
                'fileName' => $groupID . '.jpg',
                'fileType' => mime_content_type($filePath),
                'fileSize' => filesize($filePath)
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get group profile picture: ' . $e->getMessage(), 500);
        }
    }
}