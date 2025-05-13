<?php

class DatabaseOperations
{
    private static function sanitize($args)
    {
        $newArgs = [];
        foreach ($args as $arg) {
            if ($arg[1] == null) {
                $newArgs[] = $arg;
            } else {
                $newArgs[] = [$arg[0], htmlspecialchars($arg[1])];
            }
        }
        return $newArgs;
    }

    public static function fetchFromDB($sql, $args = [])
    {
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($sql);

        foreach (self::sanitize($args) as $arg) {
            $stmt->bindParam($arg[0], $arg[1]);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insertIntoDB($sql, $args = [])
    {
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($sql);

        foreach (self::sanitize($args) as $arg) {
            $stmt->bindParam($arg[0], $arg[1]);
        }

        $result = $stmt->execute();
        $rowCount = $stmt->rowCount();
        $lastId = $conn->lastInsertId();

        return [$rowCount, $lastId, $result];
    }

    public static function updateDB($sql, $args = [])
    {
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($sql);

        foreach (self::sanitize($args) as $arg) {
            $stmt->bindParam($arg[0], $arg[1]);
        }

        $result = $stmt->execute();
        return $stmt->rowCount();
    }
}
