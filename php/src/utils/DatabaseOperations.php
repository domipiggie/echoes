
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

    private static function desanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = self::desanitize($value);
                } else if (is_string($value)) {
                    $data[$key] = htmlspecialchars_decode($value, ENT_QUOTES);
                }
            }
        } else if (is_string($data)) {
            $data = htmlspecialchars_decode($data, ENT_QUOTES);
        }
        return $data;
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
            $returnValue = self::desanitize($returnValue);
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
    
    public static function desanitizeData($data)
    {
        return self::desanitize($data);
    }
}