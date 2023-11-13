<?php

namespace src\controllers;

use src\models\Appointment;
use src\responses\Responses;
use src\validations\Validations;

/**
 * Appointment Controller class
 * 
 * Handle the requests related to appointments
 */
class AppointmentController
{

    /**
     * Constructor for the AppointmentController
     *
     * @param Appointment $apmtModel The Appointment model instance
     */
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
                Responses::respondMethodNotAllowed("GET, POST");
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
                    Responses::respondMethodNotAllowed("GET, PATCH, DELETE");
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
            Responses::respondNotFound($id);
            return;
        }

        echo json_encode([
            "message" => "Appointment found successfully.", 
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
        // Extracts input data from the request body.
        $data = json_decode(file_get_contents("php://input"), true);

        // Call validations
        $validations = $this->validations($data);

        // Returns if any validation fails.
        if ($validations === false) {
            return;
        }

        // Create appointment
        $response = $this->apmtModel->createAppointment($data);

        Responses::respondCreated($response);
    }

    /**
     * Update an appointment
     *
     * @param string $id the appointment id
     * @return void
     */
    public function update(string $id): void
    {
        // Get the appointment.
        $appointment = $this->apmtModel->getAppointment($id);
        
        // If the appointment does not exists.
        if ($appointment === false) {
            Responses::respondNotFound($id);
            return;
        }

        // Extracts input data from the request body.
        $data = json_decode(file_get_contents("php://input"), true);

        // Call validations
        $validations = $this->validations($data);

        // Returns if any validation fails.
        if ($validations === false) {
            return;
        }

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
        // Get the appointment.
        $appointment = $this->apmtModel->getAppointment($id);
        
        // If the appointment does not exists.
        if ($appointment === false) {
            Responses::respondNotFound($id);
            return;
        }

        $rows = $this->apmtModel->deleteAppointment($id);

        echo json_encode([
            "message" => "Appointment deleted successfully",
            "rows" => $rows
        ]);
    }

    /**
     * Perform validations on the provided data.
     *
     * @param array $data The data to be validated
     * @return bool Indicates if all validations pass
     */
    public function validations(array $data): bool
    {
        // Validate empty fields
        $validateFields = Validations::validateFields($data);

        if ($validateFields === false) {
            return false;
        }

        // Validate date format
        $validate_date = Validations::validateDate($data["date"]);

        if ($validate_date === false) {
            return false;
        }

        // Validate time format
        $validateTime = Validations::validateTime($data["start_time"]);

        if ($validateTime === false) {
            return false;
        }

        // Validate cross hours
        $crossHours = $this->apmtModel->knowIfTimeIsValid($data);

        if (count($crossHours) !== 0) {
            Responses::respondCrossHours();
            return false;
        }

        // Validate that the date and time have not expired
        $dateTimeExpired = Validations::validateTimeAndDateAreNotInThePast($data);

        if ($dateTimeExpired === false) {
            return false;
        }

        // Validate if the date is saturday or sunday
        $dateIsNotWeekend = Validations::validateIsNotWeekend($data);

        if ($dateIsNotWeekend === false) {
            return false;
        }

        return true;
    }

}