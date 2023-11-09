<?php 

require dirname(__DIR__) . "/vendor/autoload.php";

set_error_handler('src\libraries\ErrorHandler::handleError');
set_exception_handler('src\libraries\ErrorHandler::handleException');

// To load env variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header("Content-type: application/json; charset=UTF-8");