<?php

class AuthController
{
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

            ResponseHandler::success(AuthMiddleware::register($data));
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

            ResponseHandler::success(AuthMiddleware::login($data));
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

            ResponseHandler::success(AuthMiddleware::refresh($data['refresh_token']));
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

            ResponseHandler::success(AuthMiddleware::logout($data['refresh_token']));
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to log out ' . $e->getMessage(), 500);
        }
    }
}
