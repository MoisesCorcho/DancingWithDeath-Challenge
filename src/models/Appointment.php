<?php

namespace src\models;

use src\libraries\Database;
use PDO;

class Appointment
{

    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAppointments() : array
    {
        $sql = "SELECT * FROM appointments ORDER BY date DESC";

        $this->db->query($sql);
        $this->db->execute();
        
        $appointments = $this->db->resultSet();

        return $appointments;
    }

    public function getAppointment($id): array | bool
    {
        $sql = "SELECT * FROM appointments WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(":id", $id);
        $this->db->execute();

        return $this->db->single() !== false? (array) $this->db->single(): false;
    }

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

    public function deleteAppointment(string $id): int
    {
        $sql = "DELETE FROM appointments WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(":id", $id);
        $this->db->execute();

        return $this->db->rowCount();
    }

    public function knowIfTimeIsValid($data): array
    {   
        $sql = "SELECT TIMEDIFF(:time, start_time) AS diferencia, start_time 
                FROM appointments 
                WHERE date = :date
                HAVING diferencia < '01:00:00' AND diferencia > '-01:00:00'
                ";

        $this->db->query($sql);
        $this->db->bind(":time", $data["start_time"]);
        $this->db->bind(":date", $data["date"]);
        $this->db->execute();

        return $this->db->resultSet();
    }

}