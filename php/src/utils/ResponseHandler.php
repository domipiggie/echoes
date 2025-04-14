<?php

class ResponseHandler
{
    public static function sendJson($data, int $statusCode = 200, array $headers = [])
    {
        http_response_code($statusCode);
        
        header('Content-Type: application/json; charset=UTF-8');
        
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        
        echo json_encode($data);
        exit;
    }

    public static function success($data = null, int $statusCode = 200, array $headers = [])
    {
        $response = [
            'success' => true,
            'data' => $data
        ];
        
        self::sendJson($response, $statusCode, $headers);
    }
    
    public static function error($message, int $statusCode = 400, array $headers = [])
    {
        $response = [
            'error' => true,
            'message' => $message,
            'code' => $statusCode
        ];
        
        self::sendJson($response, $statusCode, $headers);
    }
}