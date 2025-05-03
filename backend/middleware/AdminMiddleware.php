php
<?php

namespace OrganBankSystem\Backend\Api\Middleware;

use OrganBankSystem\Backend\Models\UserRole;

class AdminMiddleware
{
    public function __construct()
    {
    }

    public function isAdmin($headers, $organizationId = null)
    {
        $authMiddleware = new AuthMiddleware();
        $decoded = $authMiddleware->authenticate($headers);
        
        if (!$decoded || !isset($decoded->user_id)) {
            return false;
        }

        $userRole = new UserRole();
        if(!$userRole->hasRole($decoded->user_id, 'system_administrator')){
            http_response_code(403);
            echo json_encode(["message" => "Forbidden: Admin role required"]);
            return false;
        }
        return true;
    }
}