php
<?php

namespace App\Middleware;

use PDO;
use PDOException;

class AdminMiddleware
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function isAdmin(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT is_admin FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return (bool) $result['is_admin'];
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}