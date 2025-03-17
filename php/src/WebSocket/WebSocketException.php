<?php

namespace WebSocket;

class WebSocketException extends \Exception
{
    private $errorCode;
    private $errorType;
    private $closeConnection;

    public function __construct($message, $errorCode = 1000, $errorType = 'error', $closeConnection = false)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->errorType = $errorType;
        $this->closeConnection = $closeConnection;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorType()
    {
        return $this->errorType;
    }
    
    public function shouldCloseConnection()
    {
        return $this->closeConnection;
    }

    public function toArray()
    {
        return [
            'type' => $this->errorType,
            'code' => $this->errorCode,
            'message' => $this->getMessage()
        ];
    }
}