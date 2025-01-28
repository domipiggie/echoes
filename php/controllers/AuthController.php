<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

class AuthController {
    private $conn;
    private $user;

    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
    }

    private function generateJWT($userId, $email) {
        $issuedAt = time();
        $notBefore = $issuedAt;
        
        $token = array(
            "iss" => JWT_ISSUER,
            "aud" => JWT_AUDIENCE,
            "iat" => $issuedAt,
            "nbf" => $notBefore,
            "exp" => $issuedAt+JWT_EXPIRATION_TIME,
            "jti" => base64_encode(random_bytes(16)),
            "data" => array(
                "id" => $userId,
                "email" => $email
            )
        );

        return JWT::encode($token, JWT_SECRET_KEY, JWT_ALGORITHM);
    }

    public function login($data) {
        if (!isset($data['email']) || !isset($data['password'])) return array("message" => "You must enter an email and a password!");

        $this->user->email = $data['email'];
        
        if($this->user->emailExists() && 
           password_verify($data['password'], $this->user->password)) {
            
            $jwt = $this->generateJWT($this->user->id, $this->user->email);
            
            return array(
                "status" => "success",
                "message" => "Login successful",
                "token" => $jwt,
                "expires_in" => 3600,
                "token_type" => "Bearer"
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
        if (!isset($data['email']) || !isset($data['password'])) return array("message" => "You must enter an email and a password!");
        
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        
        // check if e-mail already in use
        if ($this->user->emailExists()) return array("message" => "E-mail already in use!");

        // create user
        if ($this->user->create()) return array("message" => "User was created.");

        // default
        return array("message" => "Unable to create user.");
    }
}