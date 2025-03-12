<?php

class ErrorHandler
{
    public static function handleError($exception)
    {
        $statusCode = ($exception instanceof ApiException) 
            ? $exception->getStatusCode() 
            : 500;

        http_response_code($statusCode);
        echo json_encode([
            'error' => true,
            'message' => $exception->getMessage(),
            'code' => $statusCode
        ]);
        exit;
    }
}