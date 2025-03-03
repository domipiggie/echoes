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
    }

    public function login($data)
    {
        if (!isset($data['email']) || !isset($data['password']))
            return array("message" => "You must enter an email and a password!");

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
        }

        return array(
            "status" => "error",
            "message" => "Invalid credentials"
        );
    }

    public function register($data)
    {
        // check for email and password in request body
        if (!isset($data['email']) || !isset($data['username']) || !isset($data['password']))
            return array("message" => "You must enter an email, username and password!");

        // check if e-mail already in use
        if ($this->user->emailExists())
            return array("message" => "E-mail already in use!");

        // check if username already in use
        if ($this->user->usernameExists())
            return array("message" => "Username already in use!");

        // create user
        if ($this->user->createUser($data['username'], $data['email'], $data['password']))
            return array("message" => "User was created.");

        // default
        return array("message" => "Unable to create user.");
    }

    public function refresh($refreshToken)
    {
        try {
            $userID = $this->refreshToken->validate($refreshToken);

            if (!$this->user->loadFromID($userID)) {
                throw new Exception("User not found");
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

        } catch (Exception $e) {
            return array(
                "status" => "error",
                "message" => $e->getMessage()
            );
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
            return array(
                "status" => "error",
                "message" => "Error during logout"
            );
        }
    }

    public function handleRegister($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        try {
            AuthMiddleware::validateAuthData($data);
        } catch (Exception $e) {
            echo json_encode(['message' => $e->getMessage()]);
            exit();
        }

        echo json_encode($this->register($data));
    }

    public function handleLogin($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        try {
            AuthMiddleware::validateAuthData($data);
        } catch (Exception $e) {
            echo json_encode(['message' => $e->getMessage()]);
            exit();
        }

        echo json_encode($this->login($data));
    }

    public function handleRefresh($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        if (isset($data['refresh_token'])) {
            $result = $this->refresh($data['refresh_token']);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Refresh token is required"));
        }
    }

    public function handleLogout($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['message' => 'Invalid method!']);
            exit();
        }

        if (isset($data['refresh_token'])) {
            $result = $this->logout($data['refresh_token']);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Refresh token is required"));
        }
    }
}