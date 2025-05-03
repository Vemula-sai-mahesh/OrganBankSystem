<?php

namespace OrganBank\Utils;

use OrganBank\Config\Config;
use PDO;
use PDOException;

class Database
{
    private $conn;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $this->conn = null;
        
        $config = Config::getConfig();

        if (!$config) {
            throw new \Exception("Database configuration not found.");
        }

        $host = $config['db']['host'];
        $dbName = $config['db']['name'];
        $username = $config['db']['user'];
        $password = $config['db']['password'];

        try {
            $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $dbName, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}