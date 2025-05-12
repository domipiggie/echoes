<?php
class File
{
    private $dbConn;
    private $table_name = "file";

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function createFile($userID, $fileName, $uniqueName, $size)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        userID = :userID,
                        file_name = :fileName,
                        unique_name = :uniqueName,
                        size = :size,
                        uploaded_at = NOW()";

            $args = [
                [':userID', $userID],
                [':fileName', $fileName],
                [':uniqueName', $uniqueName],
                [':size', $size]
            ];

            $results = DatabaseOperations::insertIntoDB($this->dbConn, $query, $args);
            
            if (count($results) > 0 && $results[0] > 0) {
                return $results[1]; // Return the file ID
            }
            
            throw new ApiException('Failed to insert file into database', 500);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create file: ' . $e->getMessage(), 500);
        }
    }

    public function getFileById($fileID)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . "
                WHERE
                    fileID = :fileID";

            $args = [
                [':fileID', $fileID]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $query, $args);

            if (count($results) === 0) {
                throw new ApiException('File not found', 404);
            }

            return $results[0];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get file: ' . $e->getMessage(), 500);
        }
    }

    public function getFileByUniqueName($uniqueName)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . "
                WHERE
                    unique_name = :uniqueName";

            $args = [
                [':uniqueName', $uniqueName]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $query, $args);

            if (count($results) === 0) {
                throw new ApiException('File not found', 404);
            }

            return $results[0];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get file: ' . $e->getMessage(), 500);
        }
    }

    public function getUserFiles($userID)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . "
                WHERE
                    userID = :userID
                ORDER BY uploaded_at DESC";

            $args = [
                [':userID', $userID]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $query, $args);
            return $results;
        } catch (Exception $e) {
            throw new ApiException('Failed to get user files: ' . $e->getMessage(), 500);
        }
    }

    public function deleteFile($fileID, $userID)
    {
        try {
            $query = "DELETE FROM " . $this->table_name . "
                WHERE
                    fileID = :fileID AND userID = :userID";

            $args = [
                [':fileID', $fileID],
                [':userID', $userID]
            ];

            $result = DatabaseOperations::updateDB($this->dbConn, $query, $args);
            
            if ($result > 0) {
                return true;
            }
            
            throw new ApiException('File not found or you do not have permission to delete it', 404);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to delete file: ' . $e->getMessage(), 500);
        }
    }

    public function getTotalUserFileSize($userID)
    {
        try {
            $query = "SELECT SUM(size) as total_size FROM " . $this->table_name . "
                WHERE
                    userID = :userID";

            $args = [
                [':userID', $userID]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $query, $args);

            if (count($results) === 0 || $results[0]['total_size'] === null) {
                return 0; // No files or sum is null
            }

            return (int)$results[0]['total_size'];
        } catch (Exception $e) {
            throw new ApiException('Failed to get total file size: ' . $e->getMessage(), 500);
        }
    }
}