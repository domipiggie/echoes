<?php

class ErrorHandler
{
    public static function handleError($exception)
    {
        $statusCode = ($exception instanceof ApiException)
            ? $exception->getStatusCode()
            : 500;

        ResponseHandler::error($exception->getMessage(), $statusCode);
    }
}
