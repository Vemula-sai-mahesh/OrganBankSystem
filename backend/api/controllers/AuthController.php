<?php 
namespace App\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Organization;
use PDO;
use PDOException;

class AuthController extends BaseController
{
    private PDO $db;
    
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


