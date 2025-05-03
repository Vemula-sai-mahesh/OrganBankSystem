<?php

namespace App\Controllers;

use App\Models\User;
use PDOException;
use App\Models\UserRole;

class UserController extends BaseController
{
    

    public function __construct()
    {
    }

    public function index()
    {
        try {
            $search = $_GET['search'] ?? null;
            $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? null;
            $sort = $_GET['sort'] ?? null;
            $page = $_GET['page'] ?? 1;
            $per_page = $_GET['per_page'] ?? null;

            if ($sort) {
                $direction = $_GET['direction'] ?? null;
                if (!$direction) {
                    $this->sendErrorResponse('Direction is required when sort is provided', 400);
                    return;
                }
                if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                    $this->sendErrorResponse("Invalid direction: $direction", 400);
                    return;
                }
                $sort .= "&" . $direction;
            }
            $users = User::getAll($search, $filter, $sort, $page, $per_page);
            echo json_encode($users);
        } catch (\Exception $e) {
            $this->sendErrorResponse('Database error during users get.');
        }
    }

    public function show(string $id)
    {
        try {
            $user = User::getById($id);
            if ($user) {
                $this->sendJsonResponse($user);
            } else {
                $this->sendErrorResponse('User not found', 404);
            }
        } catch (PDOException $e) {
            $this->sendErrorResponse('Database error during user get.');
        }
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            $this->sendErrorResponse('Missing required fields', 400);
            return;
        }

        }

        // Validate password length
        if (strlen($data['password']) < 8) {
            echo json_encode(['error' => 'Password must be at least 8 characters long']);
            http_response_code(400);
            return;
        }
        if (User::emailExists($data['email'])) {
            $this->sendErrorResponse('User with this email already exists', 409);
            return;
        }

        $user = new User();
        $user->setTable('users');
        $user->setId($data['id'] ?? uniqid());
        $user->setEmail($data['email']);
        $user->setPassword($data['password']); 
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setPhoneNumber($data['phone_number'] ?? null);
        $user->setStreetAddress($data['street_address'] ?? null);
        $user->setCity($data['city'] ?? null);
        $user->setStateProvince($data['state_province'] ?? null);
        $user->setCountry($data['country'] ?? null);
        $user->setPostalCode($data['postal_code'] ?? null);

        try {
            $userRole = new UserRole();
            $userRole->setTable('user_roles');
            $userRole->setUserId($user->getId());
            $userRole->setOrganizationId($data['organization_id']);
            $userRole->setRoleName($data['role_name']);
            $userRole->create();

            $user->create();
            $this->sendJsonResponse(['message' => 'User created', 'id' => $user->getId()], 201);
        } catch (PDOException $e) {
            $this->sendErrorResponse('Database error during user creation.');
        }
    }

    public function update(string $id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = User::getById($id);
        if (!$user) {
            $this->sendErrorResponse('User not found', 404);
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->sendErrorResponse('Invalid email format', 400);
            return;
        }

        if (isset($data['password']) && strlen($data['password']) < 8) {
            $this->sendErrorResponse('Password must be at least 8 characters long', 400);
            return;
        }
        try {
            $user->update($id, $data);
            $this->sendJsonResponse(['message' => 'User updated']);
        } catch (PDOException $e) {
            $this->sendErrorResponse('Database error during user update.', 500);
        }
    }

       public function delete(string $id)
    {   

        $user = User::getById($id);

        if (!$user) {
            $this->sendErrorResponse('User not found', 404);
            return;
        }
        if (!$user->delete($id)) {
            return;
        }
        try {
            if (!$user->delete()) {
                $this->sendErrorResponse('User not found', 404);
                return;
            }
            $this->sendJsonResponse(['message' => 'User deleted']);
        } catch (PDOException $e) {
            $this->sendErrorResponse('Database error during user deletion.');
        }
    }
}