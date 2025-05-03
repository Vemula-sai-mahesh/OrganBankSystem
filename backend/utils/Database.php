php
<?php
namespace App\Utils;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(array $config): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}