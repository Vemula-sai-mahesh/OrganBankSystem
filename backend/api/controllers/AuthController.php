<?php 
namespace App\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Organization;
use PDO;
use PDOException;

class AuthController extends BaseController
{
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->respondWithError(400, 'Missing email or password');
            return;
        }

       try {
            $user = User::getByEmail($this->db, $data['email']);
            if (!$user) {
                $this->respondWithError(401, 'Invalid credentials');
                return;
            }

            if (!password_verify($data['password'], $user->getPasswordHash())) {
                $this->respondWithError(401, 'Invalid credentials');
                return;
            }
            $userRoles = UserRole::getByUserId($this->db, $user->getId());
            $roles = [];
            foreach ($userRoles as $userRole){
                $organization = Organization::findById($this->db,$userRole->getOrganizationId());
                 $roles[] = ['role' => $userRole->getRoleName(), 'organization_name' => $organization->getName() ];
            }
            $token = $this->generateToken($user->getId(), $roles);
            $this->respondWithSuccess(['token' => $token, 'user_id' => $user->getId(), 'roles' => $roles]);
        } catch (PDOException $e) {
            $this->respondWithError(500, 'Database error during login');
        }
    }

    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (
            !isset($data['first_name']) ||
            !isset($data['last_name']) ||
            !isset($data['email']) ||
             !isset($data['organization_id']) ||
            !isset($data['password'])
        ) {
            $this->respondWithError(400, 'Missing required fields');
            return;
        }

        try {
            if (User::getByEmail($this->db, $data['email'])) {
                $this->respondWithError(400, 'Email already registered');
                return;
            }
             $organization = Organization::findById($this->db, $data['organization_id']);
             if(!$organization){
                $this->respondWithError(404,'Organization not found.');
                return;
            }
            $user = new User(
                $this->db,
                null,
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['first_name'],
                $data['last_name'],
            );

            $user->save();
             $userRole = new UserRole($this->db,
             $user->getId(),
             $data['organization_id'],
             "Registered user");
             $userRole->save();
            $this->respondWithSuccess(['message' => 'User registered successfully']);
        } catch (PDOException $e) {
            $this->respondWithError(500, 'Database error during registration');
        } catch (\Exception $e) {
            $this->respondWithError(500, 'General error during registration.');
        }
    }

    public function logout(): void
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            $this->respondWithError(401, 'Unauthorized');
            return;
        }
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $userId = $this->getUserIdFromToken($token);
        if($userId){
             $this->invalidateToken($token);
             $this->respondWithSuccess(['message' => 'Logged out successfully']);
             return;
            }
       $this->respondWithError(500,'Error during logout.');
       return;
        
    }

}


