<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

use src\controllers\AppointmentController;
use src\models\Appointment;

// To analize the URL and extract the path
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/" ,$path);

// If exists an Id we take it if not, we set null to Id variable
$id = $parts[7] ?? null;

// If does not exists appointment word in the URL we finishing the script
if ($parts[6] != "appointment") {
    echo json_encode(["message" => "Unauthorized endpoint. 'appointment' is the only one accepted"]);
    http_response_code(404);
    exit;
}

$appointmentModel = new Appointment;

$appointmentController = new AppointmentController($appointmentModel);

$appointmentController->processRequest($_SERVER["REQUEST_METHOD"], $id);