<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

class AuthController
{
    private $conn;
    private $user;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->user = new User($db);
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

    public function login($data)
    {
        // check for email and password in request body
        if (!isset($data['email']) || !isset($data['password'])) return array("message" => "You must enter an email and a password!");
        
        $this->user->email = $data['email'];

        if (
            $this->user->emailExists() &&
            password_verify($data['password'], $this->user->password)
        ) {

            $token = array(
                "iss" => ISSUER,
                "aud" => AUDIENCE,
                "iat" => time(),
                "exp" => time() + (60 * 60),
                "data" => array(
                    "id" => $this->user->id,
                    "email" => $this->user->email
                )
            );

            $jwt = JWT::encode($token, SECRET_KEY, ALGORITHM);

            return array(
                "message" => "Successful login.",
                "token" => $jwt
            );
        }
        return array("message" => "Login failed.");
    }
}