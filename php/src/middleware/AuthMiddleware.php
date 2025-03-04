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
            throw new Exception('Invalid token format');
        }
    }

    private static function validateTokenClaims($decoded)
    {
        // Verify issuer
        if (!isset($decoded->iss) || $decoded->iss !== JWT_ISSUER) {
            throw new SignatureInvalidException('Invalid token issuer');
        }

        // Verify audience
        if (!isset($decoded->aud) || $decoded->aud !== JWT_AUDIENCE) {
            throw new SignatureInvalidException('Invalid token audience');
        }

        // Check if token was issued in the future
        if (!isset($decoded->iat) || $decoded->iat > time()) {
            throw new Exception('Invalid token issue time');
        }

        // Check if token is expired
        if (!isset($decoded->exp) || $decoded->exp < time()) {
            throw new ExpiredException('Token has expired');
        }

        // Verify token hasn't been used before its nbf time
        if (isset($decoded->nbf) && $decoded->nbf > time()) {
            throw new Exception('Token not yet valid');
        }
    }

    public static function validateToken()
    {
        try {
            $headers = self::getAuthorizationHeader();

            if (!$headers) {
                throw new Exception('Authorization header not found');
            }

            if (!preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                throw new Exception('Token not found in Bearer header');
            }

            $token = $matches[1];

            // Validate token format
            self::validateTokenFormat($token);

            // Decode token
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));

            // Validate token claims
            self::validateTokenClaims($decoded);

            return $decoded->data;

        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "Token has expired",
                "error_type" => "token_expired"
            ]);
            exit();
        } catch (SignatureInvalidException $e) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid token signature",
                "error_type" => "invalid_signature"
            ]);
            exit();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
                "error_type" => "token_invalid"
            ]);
            exit();
        }
    }

    public static function validateAuthData($data)
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid e-mail format.");
        }

        if (strlen($data['password']) < 6) {
            throw new LengthException("Password too short.");
        }
    }
}