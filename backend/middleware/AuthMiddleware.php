php
<?php

namespace OrganBankSystem\Backend\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use OrganBankSystem\Backend\Models\User;
use OrganBankSystem\Backend\Models\Organization;
use OrganBankSystem\Backend\Models\UserRole;

class AuthMiddleware
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'];
    }

    public function authenticate($headers, $requiredRoles = [])
    {
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized: Missing token"]);
            exit;
        }
    
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    
        if (!$token) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized: Missing token"]);
            exit;
        }

        
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            $userId = $decoded->user_id;

            $user = new User();
            $userData = $user->getById($userId);
            if (!$userData) {
                http_response_code(401);
                echo json_encode(["error" => "Unauthorized: User not found"]);
                exit;
            }

            $userRole = new UserRole();
            $rolesData = $userRole->getRolesByUserId($userId);

            if (!$rolesData || count($rolesData) == 0) {
                http_response_code(401);
                echo json_encode(["error" => "Unauthorized: Role not found"]);
                exit;
            }

            $userRoles = array_column($rolesData, 'role_name');

            if (!empty($requiredRoles) && !array_intersect($userRoles, $requiredRoles)) {
                http_response_code(403);
                echo json_encode(["error" => "Forbidden: Insufficient permissions"]);
                exit;
            }
    
            $decoded->roles = $userRoles;
            $decoded->user_data = $userData;
            $decoded->user_data['organization_id'] = $rolesData[0]['organization_id'];

            return $decoded;

        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized: Invalid token"]);
            exit;
        }
    }
}