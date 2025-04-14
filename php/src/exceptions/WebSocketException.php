<?php

class WebSocketException extends Exception
{
    protected $code;
    protected $statusCode;
    protected $errorType;

    public function __construct($message, $code = 'INTERNAL_ERROR', $statusCode = 500, $errorType = 'error')
    {
        $this->code = $code;
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
        
        parent::__construct($message, 0);
    }

    public function getErrorCode()
    {
        return $this->code;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getErrorType()
    {
        return $this->errorType;
    }

    public function getErrorResponse()
    {
        return [
            'type' => $this->errorType,
            'message' => $this->getMessage(),
            'code' => $this->code,
            'status_code' => $this->statusCode
        ];
    }
}