<?php
class User
{
    private $dbConn;

    private $table_name = "user";

    private $userID;
    private $username;
    private $displayName;
    private $email;
    private $passwordHash;
    private $profilePicture;


    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function createUser($username, $email, $password)
    {
        $hashedPassword = hash('sha256', $password);
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    email = :email,
                    username = :username,
                    displayName = :displayName,
                    password = :password";

        $dbStmt = $this->dbConn->prepare($query);
        $dbStmt->bindParam(':email', $email);
        $dbStmt->bindParam(':username', $username);
        $dbStmt->bindParam(':displayName', $username);
        $dbStmt->bindParam(':password', $hashedPassword);

        try {
            $this->dbConn->beginTransaction();

            if ($dbStmt->execute()) {
                $this->userID = $this->dbConn->lastInsertId();
                $this->username = $username;
                $this->email = $email;
                $this->passwordHash = $hashedPassword;
                $this->dbConn->commit();
                return true;
            }
        } catch (PDOException $e) {
            $this->dbConn->rollBack();
            throw new ApiException('Failed to create user', 500);
        } catch (Exception $e) {
            throw new ApiException('Failed to create user', 500);
        }
    }

    public function loadFromID($id)
    {
        try {
            $query = "SELECT * FROM user WHERE userID = ?";
            $stmt = $this->dbConn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                throw new ApiException('User not found', 404);
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->userID = $row['userID'];
            $this->email = $row['email'];
            $this->username = $row['username'];
            $this->passwordHash = $row['password'];
            return true;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to load user', 500);
        }
    }

    public function loadFromEmail($email)
    {
        try {
            $query = "SELECT userID, username, email, password
                FROM " . $this->table_name . "
                WHERE email = :email
                LIMIT 0,1";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new ApiException('User not found', 404);
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->userID = $row['userID'];
            $this->email = $row['email'];
            $this->username = $row['username'];
            $this->passwordHash = $row['password'];
            return true;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to load user', 500);
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
            $query = "SELECT email
                    FROM " . $this->table_name . "
                    WHERE email = ?
                    LIMIT 0,1";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(1, $email);
            $stmt->execute();

            $num = $stmt->rowCount();

            return $num > 0;
        } catch (Exception $e) {
            throw new ApiException('Failed to check if email exists', 500);
        }
    }

    public function usernameExists($username)
    {
        try {
            $query = "SELECT username
                    FROM " . $this->table_name . "
                    WHERE username = ?
                    LIMIT 0,1";

            $stmt = $this->dbConn->prepare($query);
            $stmt->bindParam(1, $username);
            $stmt->execute();

            $num = $stmt->rowCount();

            return $num > 0;
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
}
