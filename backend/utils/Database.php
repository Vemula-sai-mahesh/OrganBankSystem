<?php

namespace OrganBank\Utils;

use PDO;
use PDOException;

class Database
{
    private $conn;

    // Hardcoded connection details
    private string $host = 'localhost';
    private string $dbName = 'organ_bank_system'; // Make sure this matches your actual DB name
    private string $username = 'root';
    private string $password = '';

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $this->conn = null;
        
        // Use hardcoded details directly
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbName, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $exception) {
            // It's better to throw the exception or log it rather than echoing
            // echo "Connection error: " . $exception->getMessage(); 
            error_log("Database Connection Error: " . $exception->getMessage()); // Log the error
            throw $exception; // Re-throw the exception so the calling code knows about the failure
        }
    }

    public function getConnection()
    {
        // Ensure connection was successful before returning
        if ($this->conn === null) {
             throw new \RuntimeException("Database connection was not established successfully.");
        }
        return $this->conn;
    }
}