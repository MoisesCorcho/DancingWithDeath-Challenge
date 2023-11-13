<?php

namespace src\models;

use src\libraries\Database;

/**
 * Class Appointment
 * 
 * Represents a model for managing appointments in the database.
 */
class Appointment
{

    /**
     * @var Database $db Instance of the Database class for handling database operations.
     */
    private Database $db;

    /**
     * Constructor method to initialize the Database instance.
     */
    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Retrieves all appointments from the database.
     *
     * @return array An array of appointments sorted by date in descending order.
     */
    public function getAppointments() : array
    {
        $sql = "SELECT * FROM appointments ORDER BY date DESC";

        $this->db->query($sql);
        $this->db->execute();
        
        $appointments = $this->db->resultSet();

        return $appointments;
    }

    /**
     * Retrieves a specific appointment based on its ID.
     *
     * @param mixed $id The ID of the appointment to be retrieved.
     * @return array|bool Returns an array representing the appointment data if found, otherwise false.
     */
    public function getAppointment($id): array | bool
    {
        $sql = "SELECT * FROM appointments WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(":id", $id);
        $this->db->execute();

        return $this->db->single() !== false? (array) $this->db->single(): false;
    }

    /**
     * Creates a new appointment entry in the database.
     *
     * @param array $data An array containing appointment data (date, start time, email).
     * @return int The ID of the newly created appointment.
     */
    public function createAppointment(array $data): int
    {
        $sql = "INSERT INTO appointments (date, start_time, email) VALUES (:date, :start_time, :email)";

        $this->db->query($sql);
        $this->db->bind(":date", $data["date"]);
        $this->db->bind(":start_time", $data["start_time"]);
        $this->db->bind(":email", $data["email"]);
        $this->db->execute();

        return $this->db->returnLastIdInserted();
    }

    /**
     * Updates an existing appointment in the database.
     *
     * @param array $data An array containing appointment data to be updated.
     * @param string $id The ID of the appointment to be updated.
     * @return int The number of rows affected by the update operation.
     */
    public function updateAppointment(array $data, string $id): int
    {   
        $dataKeys = array_keys($data);

        $dataMap = array_map(function($key) {

            return "$key = :$key";
        } ,$dataKeys);
            
        $setStatement = implode(",", $dataMap);

        $sql = "UPDATE appointments 
                SET " . $setStatement ." 
                WHERE id = :id";

        $this->db->query($sql);

        foreach ($data as $key => $value) {
            $this->db->bind(":$key", $value);
        }
        $this->db->bind(":id", $id);
        $this->db->execute();

        return $this->db->rowCount();
    }

    /**
     * Deletes an appointment from the database.
     *
     * @param string $id The ID of the appointment to be deleted.
     * @return int The number of rows affected by the delete operation.
     */
    public function deleteAppointment(string $id): int
    {
        $sql = "DELETE FROM appointments WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(":id", $id);
        $this->db->execute();

        return $this->db->rowCount();
    }

    /**
     * Checks if a given time for an appointment is valid and available.
     *
     * @param mixed $data An array containing date and start time information.
     * @return array Returns an array of appointments that meet the time criteria.
     */
    public function knowIfTimeIsValid($data): array
    {   
        /**
         * SQL query to determine if a given time is available for an appointment
         * 
         * Uses the TIMEDIFF function to calculate the time difference between :time and the start_time in the database.
         * Filters appointments based on two conditions using HAVING:
         * diferencia < '01:00:00': Ensures the difference between :time and start_time is less than one hour.
         * diferencia > '-01:00:00': Ensures the difference is greater than negative one hour (within one hour in the past).
         * This SQL query aims to find appointments where the start time is within an hour (plus or minus)
         */
        $sql = "SELECT TIMEDIFF(:time, start_time) AS diferencia, start_time 
                FROM appointments 
                WHERE date = :date
                HAVING diferencia < '01:00:00' AND diferencia > '-01:00:00'
                ";

        // Set up the SQL query with provided data
        $this->db->query($sql);
        $this->db->bind(":time", $data["start_time"]);
        $this->db->bind(":date", $data["date"]);
        $this->db->execute();

        // Return the result set from the query, indicating available appointments within the time frame
        return $this->db->resultSet();
    }

}