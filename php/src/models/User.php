<?php
class User
{
    private $table_name = "user";

    private $userID;
    private $username;
    private $displayName;
    private $email;
    private $passwordHash;
    private $profilePicture;

    // CREATE
    public function createUser($username, $email, $password)
    {
        $hashedPassword = hash('sha256', $password);
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    email = :email,
                    username = :username,
                    displayName = :displayName,
                    password = :password";

        $args = [
            [':email', $email],
            [':username', $username],
            [':displayName', $username],
            [':password', $hashedPassword]
        ];

        try {
            $results = DatabaseOperations::insertIntoDB($query, $args);
            if (count($results) > 0 && $results[0] > 0) {
                $this->userID = $results[1];
                $this->username = $username;
                $this->email = $email;
                $this->passwordHash = $hashedPassword;
                return true;
            }
            throw new ApiException('Failed to insert user into database', 500);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to create user' . $e->getMessage(), 500);
        }
    }

    // READ
    public function loadFromID($id)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . "
                WHERE
                    userID = :userID";

            $args = [
                [':userID', $id]
            ];

            $results = DatabaseOperations::fetchFromDB($query, $args);

            if (count($results) === 0) {
                throw new ApiException('User not found', 404);
            }

            $row = $results[0];

            $this->userID = $row['userID'];
            $this->username = $row['username'];
            $this->displayName = $row['displayName'];
            $this->email = $row['email'];
            $this->passwordHash = $row['password'];
            $this->profilePicture = $row['profilePicture'];

            return true;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to load user ' . $e->getMessage(), 500);
        }
    }
    public function loadFromEmail($email)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . "
                WHERE
                    email = :email
                    LIMIT 0,1";

            $args = [
                [':email', $email]
            ];

            $results = DatabaseOperations::fetchFromDB($query, $args);

            if (count($results) === 0) {
                throw new ApiException('User not found', 404);
            }

            $row = $results[0];

            $this->userID = $row['userID'];
            $this->username = $row['username'];
            $this->displayName = $row['displayName'];
            $this->email = $row['email'];
            $this->passwordHash = $row['password'];
            $this->profilePicture = $row['profilePicture'];

            return true;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to load user ' . $e->getMessage(), 500);
        }
    }
    public function loadFromUsername($username)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                    WHERE
                        username = :username 
                        LIMIT 0,1";

            $args = [
                [':username', $username]
            ];

            $results = DatabaseOperations::fetchFromDB($query, $args);

            if (count($results) === 0) {
                throw new ApiException('User not found', 404);
            }

            $row = $results[0];

            $this->userID = $row['userID'];
            $this->username = $row['username'];
            $this->displayName = $row['displayName'];
            $this->email = $row['email'];
            $this->passwordHash = $row['password'];
            $this->profilePicture = $row['profilePicture'];

            return true;
        } catch (Exception $e) {
            throw new ApiException('Failed to search for user ' . $e->getMessage(), 500);
        }
    }

    //misc methods
    public function isUserFullyLoaded()
    {
        if (isset($this->userID) && isset($this->username) && isset($this->email) && isset($this->passwordHash))
            return true;
        return false;
    }

    public function emailExists($email)
    {
        try {
            $query = "SELECT email FROM " . $this->table_name . "
                    WHERE
                        email = :email
                        LIMIT 0,1";

            $args = [
                [':email', $email]
            ];

            $results = DatabaseOperations::fetchFromDB($query, $args);

            return count($results) > 0;
        } catch (Exception $e) {
            throw new ApiException('Failed to check if email exists', 500);
        }
    }

    public function usernameExists($username)
    {
        try {
            $query = "SELECT username FROM " . $this->table_name . "
                    WHERE
                        username = :username
                        LIMIT 0,1";

            $args = [
                [':username', $username]
            ];

            $results = DatabaseOperations::fetchFromDB($query, $args);

            return count($results) > 0;
        } catch (Exception $e) {
            throw new ApiException('Failed to check if username exists', 500);
        }
    }

    //getters
    public function getUserID()
    {
        return $this->userID;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    public function getProfilePicture()
    {
        return $this->profilePicture;
    }
    
    public function setProfilePicture($profilePicture)
    {
        try {
            $query = "UPDATE " . $this->table_name . "
                    SET
                        profilePicture = :profilePicture
                    WHERE
                        userID = :userID";
            
            $args = [
                [':profilePicture', $profilePicture],
                [':userID', $this->userID]
            ];
            
            $result = DatabaseOperations::updateDB($query, $args);
            
            if ($result > 0) {
                $this->profilePicture = $profilePicture;
                return true;
            }
            
            throw new ApiException('Failed to update profile picture', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update profile picture: ' . $e->getMessage(), 500);
        }
    }
    
    public function updateUsername($username)
    {
        try {
            if ($this->usernameExists($username) && $username !== $this->username) {
                throw new ApiException('Username already in use', 400);
            }
            
            $query = "UPDATE " . $this->table_name . "
                    SET
                        username = :username
                    WHERE
                        userID = :userID";
            
            $args = [
                [':username', $username],
                [':userID', $this->userID]
            ];
            
            $result = DatabaseOperations::updateDB($query, $args);
            
            if ($result > 0) {
                $this->username = $username;
                return true;
            }
            
            throw new ApiException('Failed to update username', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update username: ' . $e->getMessage(), 500);
        }
    }
    
    public function updateEmail($email)
    {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ApiException('Invalid email format', 400);
            }
            
            if ($this->emailExists($email) && $email !== $this->email) {
                throw new ApiException('Email already in use', 400);
            }
            
            $query = "UPDATE " . $this->table_name . "
                    SET
                        email = :email
                    WHERE
                        userID = :userID";
            
            $args = [
                [':email', $email],
                [':userID', $this->userID]
            ];
            
            $result = DatabaseOperations::updateDB($query, $args);
            
            if ($result > 0) {
                $this->email = $email;
                return true;
            }
            
            throw new ApiException('Failed to update email', 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to update email: ' . $e->getMessage(), 500);
        }
    }
}
