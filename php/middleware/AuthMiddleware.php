<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthMiddleware
{
    public static function validateToken()
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            try {
                $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
                $decoded = JWT::decode($token, new Key(SECRET_KEY, ALGORITHM));
                return $decoded->data;
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(array(
                    "message" => "Access denied.",
                    "error" => $e->getMessage()
                ));
                exit();
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Access denied."));
            exit();
        }
    }
}