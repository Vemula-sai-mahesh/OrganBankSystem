<?php
namespace App\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserRole;
use PDO;
use PDOException;

class UserController extends BaseController
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index()
    {
        try {
             $db = $this->db;
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

            $users = User::getAll($db, $search, $filter, $sort, $page, $per_page);
            echo json_encode($users);
        } catch (\Exception $e) {
            $this->respondWithError(500, 'Database error during users get.');
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
        } catch (\Exception $e) {
            $this->respondWithError(500, 'Database error during registration.');
        }
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            !isset($data['email']) ||
            !isset($data['password']) ||
            !isset($data['first_name']) ||
            !isset($data['last_name'])
        ) {
            $this->respondWithError(400, 'Missing required fields');
            return;
        }

        if (strlen($data['password']) < 8) {
            $this->respondWithError(400, 'Password must be at least 8 characters long');
            return;
        }

        try {
            if (User::getByEmail($this->db, $data['email'])) {
                $this->respondWithError(400, 'Email already registered');
                return;
            }
            
            $organization_id = isset($data['organization_id']) ? $data['organization_id'] : null;
            $role_name = isset($data['role_name']) ? $data['role_name'] : null;

            $user = new User(
                $this->db,
                null,
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['first_name'],
                $data['last_name'],
                 $data['phone_number']?? null,
            );
            $user->save();

            $userAddress = new UserAddress(
                $this->db,
                null,
                $user->getId(),
                $data['street_address']?? null,
                $data['city']?? null,
                $data['state_province']?? null,
                $data['postal_code']?? null,
                $data['country']?? null,
            );

           $userAddress->save();

            // Check if this is the first user (System Administrator)
             $userCount = User::count($this->db);
             if($userCount==1){
                $systemAdminRole = UserRole::where($this->db,'name', 'System Administrator')->first();
                if (!$systemAdminRole) {
                    $systemAdminRole = new UserRole(
                        $this->db,
                        null,
                       null,
                        'System Administrator');
                     $systemAdminRole->save();
                }
                $userRole = UserRole::where($this->db,'user_id', $user->getId())->first();
                if($userRole){
                     $userRole->setRoleName("System Administrator");
                     $userRole->save();
                }else{
                     $userRole = new UserRole(
                        $this->db,
                        $user->getId(),
                       null,
                        'System Administrator');
                     $userRole->save();
                }
              
             }else if (isset($data['organization_id']) && $data['organization_id'] === "None") {
                 $userRole = new UserRole(
                    $this->db,
                    $user->getId(),
                   null,
                    'Donor');
                 $userRole->save();
            }else if (isset($data['organization_id']) && isset($data['role_name'])){
                $organization = Organization::findById($this->db, $data['organization_id']);
                if(!$organization){
                   $this->respondWithError(404,'Organization not found.');
                   return;
                }
                  $userRole = new UserRole(
                    $this->db,
                    $user->getId(),
                    $data['organization_id'],
                    $data['role_name']);
                $userRole->save();
            }
            

            $this->respondWithSuccess(['message' => 'User registered successfully']);

        } catch (PDOException $e) {
             $this->respondWithError(500, 'Database error during registration.');
        } catch (\Exception $e) {
             $this->respondWithError(500, 'General error during registration.');
        }
    }

    public function update(string $id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = User::getById($id);

        if (!$user) {
            $this->respondWithError(404,'User not found');
            return;
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
            $this->respondWithSuccess(['message' => 'User updated']);
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

        try {
            if (!$user->delete($id)) {
                $this->sendErrorResponse('User not found', 404);
                return;
            }

            $this->respondWithSuccess(['message' => 'User deleted']);
        } catch (PDOException $e) {
            $this->sendErrorResponse('Database error during user deletion.');
        }
    }
}
