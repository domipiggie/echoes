<?php
class RefreshToken
{
    private $dbConn;
    private $table_name = "refresh_token";

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function create($userID)
    {
        try {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

            $query = "INSERT INTO " . $this->table_name . "
                        (userID, token, expires_at)
                    VALUES
                        (:userID, :token, :expires_at)";

            $args = [
                [':userID', $userID],
                [':token', $token],
                [':expires_at', $expires_at]
            ];

            $results = DatabaseOperations::insertIntoDB($this->dbConn, $query, $args);

            if (count($results) > 0 && $results[0] > 0) {
                return [
                    'token' => $token,
                    'expires_at' => $expires_at
                ];
            }

            throw new ApiException("Failed to create refresh token", 500);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function validate($token)
    {
        try {
            $query = "SELECT userID, expires_at, revoked FROM " . $this->table_name . "
                     WHERE
                        token = :token
                        LIMIT 1";

            $args = [
                [':token', $token]
            ];

            $results = DatabaseOperations::fetchFromDB($this->dbConn, $query, $args);

            if (count($results) > 0) {
                $row = $results[0];

                if ($row['revoked']) {
                    throw new ApiException("Refresh token has been revoked", 401);
                }

                if (strtotime($row['expires_at']) < time()) {
                    throw new ApiException("Refresh token has expired", 401);
                }

                return $row['userID'];
            }
            throw new ApiException("Invalid refresh token", 401);
        } catch (ApiException $apie) {
            throw new ApiException($apie->getMessage(), $apie->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }

    public function revoke($token)
    {
        try {
            $query = "UPDATE " . $this->table_name . "
            SET
                revoked = true
            WHERE
                token = :token";

            $args = [
                [':token', $token]
            ];

            $results = DatabaseOperations::updateDB($this->dbConn, $query, $args);

            return $results === true || (is_numeric($results) && $results > 0);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), 500);
        }
    }
}
