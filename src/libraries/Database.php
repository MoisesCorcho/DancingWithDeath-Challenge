<?php 

namespace src\libraries;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class Database
 * 
 * A class to handle database connections and operations using PDO.
 */
class Database
{
    
    /** @var PDO|null $conn The PDO connection object. */
    public ?PDO $conn = null;

    /** @var PDOStatement $stmt The PDO statement object. */
    private PDOStatement $stmt;

    /** @var PDOException $error Holds the PDOException object for error handling. */
    private PDOException $error;

    /** @var string $host The database host. */
    private string $host;

    /** @var string $name The database name. */
    private string $name;

    /** @var string $user The database username. */
    private string $user;

    /** @var string $pass The database password. */
    private string $pass;

    /**
     * Constructor to establish the database connection.
     */
    public function __construct()
    {
        $this->host = $_ENV["DB_HOST"];
        $this->name = $_ENV["DB_NAME"];
        $this->user = $_ENV["DB_USER"];
        $this->pass = $_ENV["DB_PASS"];

        if ($this->conn === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
            $options = array(
                PDO::ATTR_PERSISTENT => True,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );

            try {
                $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                http_response_code(500);
                echo json_encode(["message" => $this->error]);
            }
        }
    }

    /**
     * Prepare a SQL query for execution.
     *
     * @param string $sql The SQL query.
     * @return void
     */
    public function query($sql): void
    {
        $this->stmt = $this->conn->prepare($sql);
    }

    /**
     * Bind values to a prepared statement.
     *
     * @param string $param The parameter name to bind.
     * @param mixed $value The value to bind.
     * @param int $type The data type to bind (optional).
     * @return void
     */
    public function bind($param, $value, $type = null): void
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement.
     *
     * @return bool True on success, False on failure.
     */
    public function execute(): int
    {
        return $this->stmt->execute();
    }

    /**
     * Get the result set as an array of objects.
     *
     * @return array An array of objects.
     */
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single record as an object.
     *
     * @return object An object representing a single record.
     */
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get the number of rows affected by the last statement.
     *
     * @return int The number of rows affected.
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Return the ID of the last inserted row.
     *
     * @return string The ID of the last inserted row.
     */
    public function returnLastIdInserted()
    {
        return $this->conn->lastInsertId();
    }

}