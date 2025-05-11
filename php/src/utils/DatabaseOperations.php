<?php

class DatabaseOperations
{
    private static function executeWithRetry($db, $callback, $maxRetries = 3)
    {
        $retries = 0;
        while ($retries < $maxRetries) {
            try {
                return $callback($db);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 2006 && $retries < $maxRetries - 1) {
                    $retries++;
                    if ($db instanceof DatabaseConnectionManager) {
                        $db->getConnection();
                    }
                    continue;
                }
                throw $e;
            }
        }
    }
    
    public static function fetchFromDB($db, $sql, $args = [])
    {
        return self::executeWithRetry($db, function($db) use ($sql, $args) {
            $conn = ($db instanceof DatabaseConnectionManager) ? $db->getConnection() : $db;
            $stmt = $conn->prepare($sql);
            
            foreach ($args as $arg) {
                $stmt->bindParam($arg[0], $arg[1]);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }
    
    public static function insertIntoDB($db, $sql, $args = [])
    {
        return self::executeWithRetry($db, function($db) use ($sql, $args) {
            $conn = ($db instanceof DatabaseConnectionManager) ? $db->getConnection() : $db;
            $stmt = $conn->prepare($sql);
            
            foreach ($args as $arg) {
                $stmt->bindParam($arg[0], $arg[1]);
            }
            
            $result = $stmt->execute();
            $rowCount = $stmt->rowCount();
            $lastId = $conn->lastInsertId();
            
            return [$rowCount, $lastId, $result];
        });
    }
    
    public static function updateDB($db, $sql, $args = [])
    {
        return self::executeWithRetry($db, function($db) use ($sql, $args) {
            $conn = ($db instanceof DatabaseConnectionManager) ? $db->getConnection() : $db;
            $stmt = $conn->prepare($sql);
            
            foreach ($args as $arg) {
                $stmt->bindParam($arg[0], $arg[1]);
            }
            
            $result = $stmt->execute();
            return $stmt->rowCount();
        });
    }
}
