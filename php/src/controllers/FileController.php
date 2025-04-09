<?php

class FileController
{
    private $db;
    private $fileModel;
    private $uploadDir = __DIR__ . '../../../public/uploads/';

    public function __construct($db)
    {
        $this->db = $db;
        $this->fileModel = new File($db);
    }

    public function handleFileUpload($files)
    {
        if (!isset($files['file'])) {
            throw new ApiException('No file uploaded', 400);
        }

        $user = 1;

        $uploadedFile = $files['file'];
        $userId = $user;
        $originalName = $uploadedFile['name'];
        $size = $uploadedFile['size'];

        $uniqueName = uniqid() . '_' . basename($originalName);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $targetFilePath = $this->uploadDir . $uniqueName;
        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFilePath)) {
            if ($this->fileModel->saveFile($userId, $originalName, $uniqueName, $size)) {
                echo json_encode(['message' => 'File uploaded and saved successfully']);
            } else {
                throw new ApiException('Failed to save file record', 500);
            }
        } else {
            throw new ApiException('Failed to move uploaded file', 500);
        }
    }

    public function getFileById($fileId)
    {
        $file = $this->fileModel->getFileById($fileId);

        if (!$file) {
            throw new ApiException('File not found', 404);
        }

        $filePath = $this->uploadDir . $file['unique_name'];

        if (!file_exists($filePath)) {
            throw new ApiException('File not found on server', 404);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }
}