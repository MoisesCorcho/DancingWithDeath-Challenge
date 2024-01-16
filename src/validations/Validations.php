<?php

namespace src\validations;

use DateTime;
use src\responses\Responses;

/**
 * Class Validations
 * 
 * A collection of static methods for validating date, time, and field inputs.
 */
class Validations
{
        /**
     * Validate the format date
     *
     * @param string $date 
     * @return boolean
     */
    public static function validateDate(string $date): bool
    {
        $result = DateTime::createFromFormat('Y-m-d', $date);

        if ($result === false) {
            Responses::respondDateFormatIsNotCorrect();
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
    public static function validateTime(string $time): bool
    {
        if (!preg_match("/^\d{2}:\d{2}$/", $time)) {
            Responses::respondTimeFormatIsNotCorrect();
            return false;
        }

        if ($time < $_ENV["START_TIME"] || $time > $_ENV["END_TIME"]) {
            Responses::respondTimeIsNotValid();
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
    public static function validateFields(array $data): bool
    {

        $dataErrors = [
            "dateErrors" => null,
            "timeErrors" => null,
            "emailErrors" => null
        ];

        if ( empty($data["date"]) ) {
            $dataErrors["dateErrors"] = "Date field is empty.";
        }  

        if ( empty($data["start_time"]) ) {
            $dataErrors["timeErrors"] = "Start_time field is empty.";
        }

        if ( empty($data["email"]) ) {
            $dataErrors["emailErrors"] = "email field is empty.";
        }

        if ( !isset($dataErrors["dateErrors"]) && !isset($dataErrors["timeErrors"]) && !isset($dataErrors["emailErrors"])) {
            return true;
        }

        Responses::respondEmptyFields($dataErrors);

        return false;
    }

    /**
     * Validate that the date and time have not expired.
     *
     * @param array $data
     * @return bool
     */
    public static function validateTimeAndDateAreNotInThePast(array $data): bool
    {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:m');

        if ($data["date"] < $currentDate) {
            Responses::respondExpiredDate();
            return false;
        }

        if ($data["date"] === $currentDate) {

            if ($data["start_time"] < $currentTime) {
                Responses::respondExpiredTime();
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that the date of the appointment is not saturday or sunday
     *
     * @return boolean
     */
    public static function validateIsNotWeekend(array $data): bool
    {
        // Get the name of the day of the week
        $dayOfWeek = DateTime::createFromFormat('Y-m-d', $data["date"])->format('l');

        if ($dayOfWeek === 'Saturday' || $dayOfWeek === 'Sunday') {
            Responses::respondDateIsWeekend();
            return false;
        }

        return true;
    }
}