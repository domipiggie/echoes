<?php
$rootDir = dirname(dirname(dirname(__FILE__)));
require_once $rootDir . '/vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;

class JWTTools
{
    private static function getAuthorizationHeader()
    {
        try {
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
        } catch (Exception $e) {
            throw new ApiException('Failed to get authorization header', 500);
        }
    }

    private static function validateTokenFormat($token)
    {
        try {
            // JWT format check (3 parts separated by dots)
            if (!preg_match('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token)) {
                throw new ApiException('Invalid token format', 401);
            }
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to validate token format', 500);
        }
    }

    private static function validateTokenClaims($decoded)
    {
        try {
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
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to validate token claims', 500);
        }
    }

    public static function validateToken($token = null)
    {
        try {
            if (is_null($token)) {
                $headers = self::getAuthorizationHeader();

                if (!$headers) {
                    throw new ApiException('Authorization header not found', 401);
                }

                if (!preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                    throw new ApiException('Token not found in Bearer header', 401);
                }

                $token = $matches[1];
            }

            // Validate token format
            self::validateTokenFormat($token);

            // Decode token
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));

            // Validate token claims
            self::validateTokenClaims($decoded);

            return $decoded->data;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (ExpiredException $expe) {
            throw new ApiException('Token has expired', 401);
        } catch (Exception $e) {
            throw new ApiException('Failed to validate token', 500);
        }
    }

    public static function validateAuthData($data)
    {
        try {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ApiException('Invalid e-mail format', 400);
            }

            if (strlen($data['password']) < 6) {
                throw new ApiException('Password too short', 400);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to validate authentication data', 500);
        }
    }

    public static function generateAccessToken($userID, $username, $email)
    {
        try {
            $issuedAt = time();
            $expirationTime = $issuedAt + JWT_EXPIRATION_TIME;

            $token = array(
                "iss" => JWT_ISSUER,
                "aud" => JWT_AUDIENCE,
                "iat" => $issuedAt,
                "nbf" => $issuedAt,
                "exp" => $expirationTime,
                "jti" => base64_encode(random_bytes(16)),
                "data" => array(
                    "id" => $userID,
                    "username" => $username,
                    "email" => $email
                )
            );

            return [
                'token' => JWT::encode($token, JWT_SECRET_KEY, JWT_ALGORITHM),
                'expires_in' => JWT_EXPIRATION_TIME
            ];
        } catch (Exception $e) {
            throw new ApiException('Failed to create access token', 500);
        }
    }
}
?>