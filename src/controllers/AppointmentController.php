<?php

namespace src\controllers;

use DateTime;
use src\models\Appointment;

/**
 * Appointment Controller class
 * 
 * Handle the requests related to appointments
 */
class AppointmentController
{

    public function __construct(
        private Appointment $apmtModel = new Appointment
    )
    {}

    /**
     * Proccess the request according to the HTML 
     * method (GET, PATCH, POST, DELETE)
     *
     * @param string $method the HTML method
     * @param string|null $id the appointment id (GET, PATCH, DELETE)
     * @return void
     */
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

    /**
     * Get all appointments
     *
     * @return void
     */
    public function index()
    {
        echo json_encode($this->apmtModel->getAppointments());
    }

    /**
     * Get one appointment
     *
     * @param string $id the appointment id
     * @return void
     */
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

    /**
     * Create an appointment
     *
     * @return void
     */
    public function create(): void
    {
        // Get data
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate empty fields
        $validateFields = $this->validateFields($data);

        if ($validateFields === false) {
            return;
        }

        // Validate date format
        $validate_date = $this->validateDate($data["date"]);

        if ($validate_date === false) {
            return;
        }

        // Validate time format
        $validateTime = $this->validateTime($data["start_time"]);

        if ($validateTime === false) {
            return;
        }

        // Validate cross hours
        $crossHours = $this->apmtModel->knowIfTimeIsValid($data);

        if (count($crossHours) !== 0) {
            $this->respondCrossHours();
            return;
        }

        // Create appointment
        $response = $this->apmtModel->createAppointment($data);

        $this->respondCreated($response);
    }

    /**
     * Update an appointment
     *
     * @param string $id the appointment id
     * @return void
     */
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

    /**
     * Delete an appointment
     *
     * @param string $id the appointment id
     * @return void
     */
    public function destroy(string $id): void
    {


        $rows = $this->apmtModel->deleteAppointment($id);

        echo json_encode([
            "message" => "Appointment deleted successfully",
            "rows" => $rows
        ]);
    }

    /**
     * Response for when html methods are not allowed.
     *
     * @param string $allowed_methods methods that are allowed.
     * @return void
     */
    public function respondMethodNotAllowed(string $allowed_methods): void 
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    /**
     * Response to know when an appointment was successfully created.
     *
     * @param integer $id the appointment id
     * @return void
     */
    public function respondCreated(int $id): void
    {
        http_response_code(200);
        echo json_encode(["message" => "Appointment created successfully", "id" => $id]);
    }

    /**
     * Response to know when the date format is incorret.
     *
     * @return void
     */
    public function respondDateFormatIsNotCorrect(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "Incorrect date format. The correct format is 'Y-m-d'"]);
    }

    /**
     * Response to know when the time format is incorrect.
     *
     * @return void
     */
    public function respondTimeFormatIsNotCorrect(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "Incorrect time format. The correct format is 'H:m'"]);
    }

    /**
     * Response to know when the time is invalid.
     *
     * @return void
     */
    public function respondTimeIsNotValid(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "The time must be into the accepted hours. From {$_ENV['START_TIME']} to {$_ENV['END_TIME']}"]);
    }

    /**
     * Response to know when the appointment intersects 
     * with other appointments
     *
     * @return void
     */
    public function respondCrossHours(): void
    {
        http_response_code(409);
        echo json_encode(["message" => "This appointment conflict with an existing appointment."]);
    }

    /**
     * Response to know when an appointment was not found.
     *
     * @param string $id the appointment id
     * @return void
     */
    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Appointment with ID $id not found"]);
    }

    /**
     * Response to know which fields are empty.
     *
     * @param array $dataErrors
     * @return void
     */
    public function respondEmptyFields(array $dataErrors): void
    {
        http_response_code(409);
        
        $arrResponse = [];

        foreach($dataErrors as $err) {

            if ($err !== null) {
                array_push($arrResponse, $err);
            }
        }

        echo json_encode(["message" => "Empty fields.", "empty_fields" => $arrResponse]);
    }

    /**
     * Validate the format date
     *
     * @param string $date 
     * @return boolean
     */
    public function validateDate(string $date): bool
    {
        $result = DateTime::createFromFormat('Y-m-d', $date);

        if ($result === false) {
            $this->respondDateFormatIsNotCorrect();
            return false;
        } 

        return true;
    }

    /**
     * Validate the format time
     *
     * @param string $time
     * @return boolean
     */
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

    /**
     * Validate if the fields are empty
     *
     * @param array $data
     * @return boolean
     */
    public function validateFields(array $data): bool
    {

        $dataErrors = [
            "dateErrors" => null,
            "timeErrors" => null
        ];

        if ( empty($data["date"]) ) {
            $dataErrors["dateErrors"] = "Date field is empty.";
        }  

        if ( empty($data["start_time"]) ) {
            $dataErrors["timeErrors"] = "Start_time field is empty.";
        }

        if ( !isset($dataErrors["dateErrors"]) && !isset($dataErrors["timeErrors"]) ) {
            return true;
        }

        return false;
    }

}