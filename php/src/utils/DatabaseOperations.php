<?php

class DatabaseOperations
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public static function fetchFromDB($dbConn, $query, $args)
    {
        $stmt = $dbConn->prepare($query);

        for ($i = 0; $i < count($args); $i++) {
            $stmt->bindParam($args[$i][0], $args[$i][1]);
        }
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function insertIntoDB($dbConn, $query, $args)
    {
        $stmt = $dbConn->prepare($query);

        for ($i = 0; $i < count($args); $i++) {
            $stmt->bindParam($args[$i][0], $args[$i][1]);
        }
        $stmt->execute();

        $rowCount = $stmt->rowCount();
        $lastInsertId = $dbConn->lastInsertId();

        return [$rowCount, $lastInsertId]; // Return both rowCount and lastInsertId as an array
    }

    public static function updateDB($dbConn, $query, $args)
    {
        $stmt = $dbConn->prepare($query);

        for ($i = 0; $i < count($args); $i++) {
            $stmt->bindParam($args[$i][0], $args[$i][1]);
        }
        $stmt->execute();

        return $stmt->rowCount();
    }
}
