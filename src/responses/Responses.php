<?php

namespace src\responses;

/**
 * Class Responses
 * 
 * A collection of static methods to generate standardized responses for API endpoints.
 */
class Responses
{

    /**
     * Response for when html methods are not allowed.
     *
     * @param string $allowed_methods methods that are allowed.
     * @return void
     */
    public static function respondMethodNotAllowed(string $allowed_methods): void 
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
    public static function respondCreated(int $id): void
    {
        http_response_code(200);
        echo json_encode(["message" => "Appointment created successfully", "id" => $id]);
    }

    /**
     * Response to know when the date format is incorret.
     *
     * @return void
     */
    public static function respondDateFormatIsNotCorrect(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "Incorrect date format. The correct format is 'Y-m-d'"]);
    }

    /**
     * Response to know when the time format is incorrect.
     *
     * @return void
     */
    public static function respondTimeFormatIsNotCorrect(): void
    {
        http_response_code(400);
        echo json_encode(["message" => "Incorrect time format. The correct format is 'H:m'"]);
    }

    /**
     * Response to know when the time is invalid.
     *
     * @return void
     */
    public static function respondTimeIsNotValid(): void
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
    public static function respondCrossHours(): void
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
    public static function respondNotFound(string $id): void
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
    public static function respondEmptyFields(array $dataErrors): void
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
     * Response to know if the appointment to be created has an old date.
     *
     * @return void
     */
    public static function respondExpiredDate(): void
    {
        http_response_code(409);
        echo json_encode(["message" => "You can not create an appointment with a date before the current date."]);
    }

    /**
     * Response to know if the appointment to be created has an old time.
     *
     * @return void
     */
    public static function respondExpiredTime(): void
    {
        http_response_code(409);
        echo json_encode(["message" => "You can not create an appointment with a time before the current time."]);
    }

    /**
     * Response to know if the date of the appointment is saturday or sunday
     *
     * @return void
     */
    public static function respondDateIsWeekend(): void
    {
        http_response_code(409);
        echo json_encode(["message" => "You can not create an appointment on weekends."]);
    }

}