php
<?php

namespace App\Controllers;

use App\Models\User;
use PDO;
use PDOException;

class UserController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        try {
            $search = $_GET['search'] ?? null;            
            $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? null;
            $sort = $_GET['sort'] ?? null;
            $page = $_GET['page'] ?? null;
            $per_page = $_GET['per_page'] ?? null;
            if ($sort) {
                $direction = $_GET['direction'] ?? null;
                if (!$direction) {
                    echo json_encode(['error' => 'Direction is required when sort is provided']);
                    http_response_code(400);
                    return;
                }
                $sort .= "&" . $direction;
            }
            $users = User::getAll($this->db, $search, $filter, $sort, $page, $per_page);
            $users = User::getAll($this->db, $search, $filter, $sort);
            echo json_encode($users);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            echo json_encode(['error' => 'Missing required fields']);
            http_response_code(400);
            return;
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format']);
            http_response_code(400);
            return;
        }

        // Validate password length
        if (strlen($data['password']) < 8) {
            echo json_encode(['error' => 'Password must be at least 8 characters long']);
            http_response_code(400);
            return;
        }

        if ($this->emailExists($data['email'])) {
            echo json_encode(['error' => 'Email already exists']);
            http_response_code(409);
            return;
        }

        $user = new User($this->db);
        $user->setId(uniqid());
        $user->setEmail($data['email']);
        $user->setPasswordHash(password_hash($data['password'], PASSWORD_DEFAULT));
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);

        try {
            $userId = $user->create();
            echo json_encode(['message' => 'User created', 'id' => $userId]);
            http_response_code(201);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate email format if provided
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format']);
            http_response_code(400);
            return;
        }

        // Validate password length if provided
        if (isset($data['password']) && strlen($data['password']) < 8) {
            echo json_encode(['error' => 'Password must be at least 8 characters long']);
            http_response_code(400);
            return;
        }

        $user = new User($this->db);
        $user->setId($id);

        try {
            if (!$user->update($data)) {
                echo json_encode(['error' => 'User not found']);
                http_response_code(404);
                return;
            }
            echo json_encode(['message' => 'User updated']);
            http_response_code(200);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function delete(string $id): void
    {
        $user = new User($this->db);
        $user->setId($id);
        try {
            if (!$user->delete()) {
                echo json_encode(['error' => 'User not found']);
                http_response_code(404);
                return;
            }
            echo json_encode(['message' => 'User deleted']);
            http_response_code(200);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function setAdmin(string $id): void
    {
        $user = new User($this->db);
        $user->setId($id);
        try {
            if (!$user->update(['is_admin' => 1])) {
                echo json_encode(['error' => 'User not found']);
                http_response_code(404);
                return;
            }
            echo json_encode(['message' => 'User updated as admin']);
            http_response_code(200);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    private function emailExists(string $email): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Error checking if email exists: " . $e->getMessage());
        }
    }
}