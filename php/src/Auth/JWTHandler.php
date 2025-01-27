<?php
namespace echoes\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler
{
    private $config;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/jwt_config.php';
    }
    
    public function generateToken(array $userData): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->config['expire'];
        
        $payload = [
            'iss' => $this->config['issuer'],
            'aud' => $this->config['audience'],
            'iat' => $issuedAt,
            'exp' => $expire,
            'data' => $userData
        ];
        
        return JWT::encode($payload, $this->config['key'], $this->config['algorithm']);
    }
    
    public function validateToken(string $token)
    {
        try {
            return JWT::decode($token, new Key($this->config['key'], $this->config['algorithm']));
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }
}