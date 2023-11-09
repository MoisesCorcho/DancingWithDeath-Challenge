<?php

namespace src\controllers;

use DateTime;
use src\models\Appointment;

class AppointmentController
{

    public function __construct(
        private Appointment $apmtModel = new Appointment
    )
    {}

    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {

            if ($method === "GET") {
                $this->index();
            } else if ($method === "POST") {
                $this->create();
            } else {
                $this->respondMethodNotAllowed("GET, POST");
                return;
            }

        } else {

            switch ($method) {
                case "GET":
                    $this->get($id);
                break;
                case "PATCH":
                    $this->update($id);
                break;
                case "DELETE":
                    $this->destroy($id);
                break;
                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }

    public function index()
    {
        echo json_encode($this->apmtModel->getAppointments());
    }

    public function get(string $id): void
    {
        $appointment = $this->apmtModel->getAppointment($id);
        
        if ($appointment === false) {
            $this->respondNotFound($id);
            return;
        }

        echo json_encode([
            "message" => "Appointment found successfully", 
            "data" => $appointment
        ]);
    }

    public function create(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $validate_date = $this->validateDate($data["date"]);

        if ($validate_date === false) {
            return;
        }

        $validateTime = $this->validateTime($data["start_time"]);

        if ($validateTime === false) {
            return;
        }

        $crossHours = $this->apmtModel->knowIfTimeIsValid($data);

        if (count($crossHours) !== 0) {
            $this->respondCrossHours();
            return;
        }

        $response = $this->apmtModel->createAppointment($data);

        $this->respondCreated($response);
    }

    public function update(string $id): void
    {
        $appointment = $this->apmtModel->getAppointment($id);
        
        if ($appointment === false) {
            $this->respondNotFound($id);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $rows = $this->apmtModel->updateAppointment($data, $id);

        echo json_encode([
            "message" => "Appointment updated successfully",
            "rows" => $rows
        ]);
    }

    public function destroy(string $id): void
    {


        $rows = $this->apmtModel->deleteAppointment($id);

        echo json_encode([
            "message" => "Appointment deleted successfully",
            "rows" => $rows
        ]);
    }

    public function respondMethodNotAllowed(string $allowed_methods): void 
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    public function respondCreated(int $id): void
    {
        http_response_code(200);
        echo json_encode(["message" => "Appointment created successfully", "id" => $id]);
    }

    public function respondDateFormatIsNotCorrect(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "Incorrect date format. The correct format is 'Y-m-d'"]);
    }

    public function respondTimeFormatIsNotCorrect(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "Incorrect time format. The correct format is 'H:m'"]);
    }

    public function respondTimeIsNotValid(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "The time must be into the accepted hours. From {$_ENV['START_TIME']} to {$_ENV['END_TIME']}"]);
    }

    public function respondCrossHours(): void
    {
        http_response_code(409);
        echo json_encode(["message" => "This appointment conflict with an existing appointment."]);
    }

    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Appointment with ID $id not found"]);
    }

    public function validateDate(string $date): bool
    {
        $result = DateTime::createFromFormat('Y-m-d', $date);

        if ($result === false) {
            $this->respondDateFormatIsNotCorrect();
            return false;
        } 

        return true;
    }

    public function validateTime(string $time): bool
    {
        if (!preg_match("/^\d{2}:\d{2}$/", $time)) {
            $this->respondTimeFormatIsNotCorrect();
            return false;
        }

        if ($time < $_ENV["START_TIME"] || $time > $_ENV["END_TIME"]) {
            $this->respondTimeIsNotValid();
            return false;
        }

        return true;
    }

}