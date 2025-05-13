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

    private static function executeQuery($sql, $args = [], $fetchMode = null)
    {
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($sql);

        foreach (self::sanitize($args) as $arg) {
            $stmt->bindParam($arg[0], $arg[1]);
        }

        $result = $stmt->execute();
        
        $returnValue = null;
        if ($fetchMode === 'fetch') {
            $returnValue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else if ($fetchMode === 'insert') {
            $rowCount = $stmt->rowCount();
            $lastId = $conn->lastInsertId();
            $returnValue = [$rowCount, $lastId, $result];
        } else if ($fetchMode === 'update') {
            $returnValue = $stmt->rowCount();
        }
        
        $stmt = null;
        $conn = null;
        
        return $returnValue;
    }

    public static function fetchFromDB($sql, $args = [])
    {
        return self::executeQuery($sql, $args, 'fetch');
    }

    public static function insertIntoDB($sql, $args = [])
    {
        return self::executeQuery($sql, $args, 'insert');
    }

    public static function updateDB($sql, $args = [])
    {
        return self::executeQuery($sql, $args, 'update');
    }
}
