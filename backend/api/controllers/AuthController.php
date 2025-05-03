<?php
namespace App\Controllers;

use PDO;
use PDOException;
use App\Models\User;

class AuthController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate data (basic example)
        if (empty($data['email']) || empty($data['password'])) {
            echo json_encode(['error' => 'Missing required fields']);
            http_response_code(400);
            return;
        }

        try {
            $user = User::getByEmail($this->db, $data['email']);

            if ($user && password_verify($data['password'], $user->getPassword())) {
                session_start();
                $_SESSION['user_id'] = $user->getId();
                ob_end_clean();
                echo json_encode(['message' => 'Logged in successfully', 'user_id' => $user->getId()]);
                http_response_code(200);
            } else {
                ob_end_clean();
                echo json_encode(['error' => 'Invalid credentials']);
                http_response_code(401);
            }
        } catch (PDOException $e) {
            ob_end_clean();
            echo json_encode(['error' => 'Database error during login.']);
            http_response_code(500);
        } catch (\Exception $e) {
            ob_end_clean();
            echo json_encode(['error' => 'General error during login.']);
            http_response_code(500);
        }
    }

    public function logout(): void
    {
        try {
            session_start();
            session_destroy();
            ob_end_clean();
            echo json_encode(['message' => 'Logged out successfully']);
            http_response_code(200);
        } catch (\Exception $e) {
            ob_end_clean();
            echo json_encode(['error' => 'Error during logout.']);
            http_response_code(500);
        }
    }
}

