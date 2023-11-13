<?php 

namespace src\libraries;

use ErrorException;
use Throwable;

/**
 * Class ErrorHandler
 * 
 * A class to handle errors and exceptions and customize their handling.
 */
class ErrorHandler
{
    /**
     * Handles PHP errors and converts them into ErrorException.
     *
     * @param int $errno The level of the error raised.
     * @param string $errstr The error message.
     * @param string $errfile The filename that the error was raised in.
     * @param int $errline The line number the error was raised at.
     * @return void
     * @throws ErrorException
     */
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline): void
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    /**
     * Handles uncaught exceptions and returns a formatted JSON response.
     *
     * @param Throwable $exception The uncaught exception.
     * @return void
     */
    public static function handleException(Throwable $exception): void
    {
        // Sets a 500 Internal Server Error response code for exceptions.
        http_response_code(500);
        
        // Formats and outputs the exception details as a JSON response.
        echo json_encode([
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }
}