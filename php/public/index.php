<?php
require __DIR__ . '/../vendor/autoload.php';

use echoes\Auth\JWTHandler;

$jwtHandler = new JWTHandler();

// Example: Generate a token
$userData = [
    'user_id' => 123,
    'email' => 'user@example.com'
];

try {
    // Generate token
    $token = $jwtHandler->generateToken($userData);
    echo "Generated Token: " . $token . "\n\n";
    
    // Validate token
    $decoded = $jwtHandler->validateToken($token);
    echo "Decoded Token Data:\n";
    print_r($decoded);
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}