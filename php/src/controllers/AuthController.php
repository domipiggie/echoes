<?php

class AuthController
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
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

            JWTTools::validateAuthData($data);

            echo json_encode(AuthMiddleware::register($this->dbConn, $data));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to register ' . $e->getMessage(), 500);
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

            JWTTools::validateAuthData($data);

            echo json_encode(AuthMiddleware::login($this->dbConn, $data));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to log in ' . $e->getMessage(), 500);
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

            echo json_encode(AuthMiddleware::refresh($this->dbConn, $data['refresh_token']));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Faild to generate refresh token ' . $e->getMessage(), 500);
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

            echo json_encode(AuthMiddleware::logout($this->dbConn, $data['refresh_token']));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to log out ' . $e->getMessage(), 500);
        }
    }
}
