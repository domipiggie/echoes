<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

class AuthController
{
    private $conn;
    private $user;
    private $refreshToken;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->user = new User($db);
        $this->refreshToken = new RefreshToken($db);
    }

    private function generateAccessToken($userId, $email)
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
                "id" => $userId,
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

        $this->user->email = $data['email'];

        if (
            $this->user->emailExists() &&
            password_verify($data['password'], $this->user->password)
        ) {

            $accessToken = $this->generateAccessToken($this->user->id, $this->user->email);
            $refreshToken = $this->refreshToken->create($this->user->id);

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
        if (!isset($data['email']) || !isset($data['password']))
            return array("message" => "You must enter an email and a password!");

        $this->user->email = $data['email'];
        $this->user->password = $data['password'];

        // check if e-mail already in use
        if ($this->user->emailExists())
            return array("message" => "E-mail already in use!");

        // create user
        if ($this->user->create())
            return array("message" => "User was created.");

        // default
        return array("message" => "Unable to create user.");
    }

    public function refresh($refreshToken)
    {
        try {
            $userId = $this->refreshToken->validate($refreshToken);

            $this->user->id = $userId;
            $userDetails = $this->user->getById();

            if (!$userDetails) {
                throw new Exception("User not found");
            }

            $accessToken = $this->generateAccessToken($userId, $userDetails['email']);

            $this->refreshToken->revoke($refreshToken);
            $newRefreshToken = $this->refreshToken->create($userId);

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
}