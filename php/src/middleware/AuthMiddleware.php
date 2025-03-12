<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\SignatureInvalidException;

class AuthMiddleware
{
    private static function getAuthorizationHeader()
    {
        $headers = null;

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    private static function validateTokenFormat($token)
    {
        // JWT format check (3 parts separated by dots)
        if (!preg_match('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token)) {
            throw new ApiException('Invalid token format', 401);
        }
    }

    private static function validateTokenClaims($decoded)
    {
        // Verify issuer
        if (!isset($decoded->iss) || $decoded->iss !== JWT_ISSUER) {
            throw new ApiException('Invalid token issuer', 401);
        }

        // Verify audience
        if (!isset($decoded->aud) || $decoded->aud !== JWT_AUDIENCE) {
            throw new ApiException('Invalid token audience', 401);
        }

        // Check if token was issued in the future
        if (!isset($decoded->iat) || $decoded->iat > time()) {
            throw new ApiException('Invalid token issue time', 401);
        }

        // Check if token is expired
        if (!isset($decoded->exp) || $decoded->exp < time()) {
            throw new ApiException('Token has expired', 401);
        }

        // Verify token hasn't been used before its nbf time
        if (isset($decoded->nbf) && $decoded->nbf > time()) {
            throw new ApiException('Token not yet valid', 401);
        }
    }

    public static function validateToken()
    {
        try {
            $headers = self::getAuthorizationHeader();

            if (!$headers) {
                throw new ApiException('Authorization header not found', 401);
            }

            if (!preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                throw new ApiException('Token not found in Bearer header', 401);
            }

            $token = $matches[1];

            // Validate token format
            self::validateTokenFormat($token);

            // Decode token
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));

            // Validate token claims
            self::validateTokenClaims($decoded);

            return $decoded->data;

        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public static function validateAuthData($data)
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Invalid e-mail format', 400);
        }

        if (strlen($data['password']) < 6) {
            throw new ApiException('Password too short', 400);
        }
    }
}