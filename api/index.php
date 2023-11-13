<?php

/**
 * Declaring strict types in our PHP files will force our methods
 * to accept variables only of the exact type they are declared. 
 * Otherwise it will throw a TypeError.
 */
declare(strict_types=1);

// Including the bootstrap file for necessary configurations and autoloading.
require __DIR__ . "/bootstrap.php";

// Importing necessary classes
use src\controllers\AppointmentController;
use src\models\Appointment;

// To analize the URL and extract the path
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Breaking down the path into its components using '/'
$parts = explode("/" ,$path);

/**
 * NOTE: The indices of the array parts will change according to the URL
 * e.g.
 * /folder1/folder2/folder3/Asimov-Challenge/api/appointment
 */

// If exists an Id we take it if not, we set null to Id variable
$id = $parts[7] ?? null;

// If does not exists appointment word in the URL we finishing the script
if ($parts[6] != "appointment") {
    echo json_encode(["message" => "Unauthorized endpoint. 'appointment' is the only one accepted"]);
    http_response_code(404);
    exit;
}

// Instantiate the Appointment model
$appointmentModel = new Appointment;

// Instantiate the AppointmentController and pass the Appointment model
$appointmentController = new AppointmentController($appointmentModel);

// Process the incoming request based on HTTP method and ID
$appointmentController->processRequest($_SERVER["REQUEST_METHOD"], $id);