<?php

class AuthMiddleware
{
    public static function login($db, $data)
    {
        $user = new User($db);
        $refreshToken = new RefreshToken($db);

        try {
            if (
                $user->loadFromEmail($data['email']) &&
                hash_equals(hash('sha256', $data['password']), $user->getPasswordHash())
            ) {
                $accessToken = JWTTools::generateAccessToken($user->getUserID(), $user->getUsername(), $user->getEmail());
                $refreshToken = $refreshToken->create($user->getUserID());

                return array(
                    "status" => "success",
                    "message" => "Login successful",
                    "userID" => $user->getUserID(), // Added user ID to the response
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
            throw new ApiException('Login failed ' . $e->getMessage(), 500);
        }
    }

    public static function register($db, $data)
    {
        $user = new User($db);

        try {
            if ($user->emailExists($data['email'])) {
                throw new ApiException('Email already in use', 400);
            }

            if ($user->usernameExists($data['username'])) {
                throw new ApiException('Username already in use', 400);
            }

            $user->createUser($data['username'], $data['email'], $data['password']);

            return array("message" => "User was created.");
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create user' . $e->getMessage(), 500);
        }
    }

    public static function refresh($db, $refreshTokenString)
    {
        $user = new User($db);
        $refreshTokenObj = new RefreshToken($db);

        try {
            $userID = $refreshTokenObj->validate($refreshTokenString);

            if (!$user->loadFromID($userID)) {
                throw new ApiException("User not found", 401);
            }

            $accessToken = JWTTools::generateAccessToken($userID, $user->getUsername(), $user->getEmail());

            $refreshTokenObj->revoke($refreshTokenString);
            $newRefreshToken = $refreshTokenObj->create($userID);

            return array(
                "status" => "success",
                "userID" => $userID, // Added user ID to the response
                "access_token" => $accessToken['token'],
                "token_type" => "Bearer",
                "expires_in" => $accessToken['expires_in'],
                "refresh_token" => $newRefreshToken['token']
            );
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to refresh token ' . $e->getMessage(), 500);
        }
    }

    public static function logout($db, $refreshToken)
    {
        $refreshTokenObj = new RefreshToken($db);

        try {
            $refreshTokenObj->revoke($refreshToken);
            return array(
                "status" => "success",
                "message" => "Successfully logged out"
            );
        } catch (Exception $e) {
            throw new ApiException('Failed to logout ' . $e->getMessage(), 500);
        }
    }
}
