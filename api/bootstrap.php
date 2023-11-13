<?php 

// Initializes the Composer autoloader to autoload classes.
require dirname(__DIR__) . "/vendor/autoload.php";

// Sets a user-defined error handler function to manage errors.
set_error_handler('src\libraries\ErrorHandler::handleError');

// Sets a user-defined exception handler function to manage exceptions.
set_exception_handler('src\libraries\ErrorHandler::handleException');

// Loads environment variables from the .env file.
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Set the timezone 
date_default_timezone_set($_ENV["TIMEZONE"]);

// Sets the response header to JSON format with UTF-8 encoding.
header("Content-type: application/json; charset=UTF-8");