<?php
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;

class AuthController
{
    private $user;
    private $refreshToken;

    public function __construct($dbConn)
    {
        $this->user = new User($dbConn);
        $this->refreshToken = new RefreshToken($dbConn);
    }

    private function generateAccessToken($userID, $username, $email)
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

    public function login($data)
    {
        try {
            if (
                $this->user->loadFromEmail($data['email']) &&
                hash_equals(hash('sha256', $data['password']), $this->user->getPasswordHash())
            ) {
                $accessToken = $this->generateAccessToken($this->user->getUserID(), $this->user->getUsername(), $this->user->getEmail());
                $refreshToken = $this->refreshToken->create($this->user->getUserID());

                return array(
                    "status" => "success",
                    "message" => "Login successful",
                    "access_token" => $accessToken['token'],
                    "token_type" => "Bearer",
                    "expires_in" => $accessToken['expires_in'],
                    "refresh_token" => $refreshToken['token']
                );
            } else {
                throw new ApiException('Invalid password', 401);
            }
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Login failed', 500);
        }
    }

    public function register($data)
    {
        try {
            if ($this->user->emailExists($data['email'])) {
                throw new ApiException('Email already in use', 400);
            }

            if ($this->user->usernameExists($data['username'])) {
                throw new ApiException('Username already in use', 400);
            }

            $this->user->createUser($data['username'], $data['email'], $data['password']);

            return array("message" => "User was created.");
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create user', 500);
        }
    }

    public function refresh($refreshToken)
    {
        try {
            $userID = $this->refreshToken->validate($refreshToken);

            if (!$this->user->loadFromID($userID)) {
                throw new ApiException("User not found", 401);
            }

            $accessToken = $this->generateAccessToken($userID, $this->user->getUsername(), $this->user->getEmail());

            $this->refreshToken->revoke($refreshToken);
            $newRefreshToken = $this->refreshToken->create($userID);

            return array(
                "status" => "success",
                "access_token" => $accessToken['token'],
                "token_type" => "Bearer",
                "expires_in" => $accessToken['expires_in'],
                "refresh_token" => $newRefreshToken['token']
            );
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to refresh token', 500);
        }
    }

    public function logout($refreshToken)
    {
        try {
            $this->refreshToken->revoke($refreshToken);
            return array(
                "status" => "success",
                "message" => "Successfully logged out"
            );
        } catch (Exception $e) {
            throw new ApiException('Failed to logout', 500);
        }
    }

    public function handleRegister($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                throw new ApiException('Missing required fields', 400);
            }

            AuthMiddleware::validateAuthData($data);

            echo json_encode($this->register($data));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to register', 500);
        }
    }

    public function handleLogin($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }
            if (!isset($data['email']) || !isset($data['password'])) {
                throw new ApiException('Missing required fields', 400);
            }

            AuthMiddleware::validateAuthData($data);

            echo json_encode($this->login($data));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to log in', 500);
        }
    }

    public function handleRefresh($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }
            if (!isset($data['refresh_token'])) {
                throw new ApiException('Missing required fields', 400);
            }

            echo json_encode($this->refresh($data['refresh_token']));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Faild to generate refresh token', 500);
        }
    }

    public function handleLogout($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }
            if (!isset($data['refresh_token'])) {
                throw new ApiException('Missing required fields', 400);
            }

            echo json_encode($this->logout($data['refresh_token']));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to log out', 500);
        }
    }
}
