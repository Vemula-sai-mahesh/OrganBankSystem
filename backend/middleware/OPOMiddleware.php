<?php
namespace OrganBankSystem\Backend\Api\Middleware;

use OrganBankSystem\Backend\Models\UserRole;

class OPOMiddleware
{
    public function __construct()
    {
    }

    public function isOPOCoordinator($headers)
    {
        $authMiddleware = new AuthMiddleware();
        $decoded = $authMiddleware->authenticate($headers);

        if (!$decoded || !isset($decoded->user_id)) {
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "Unauthorized: Invalid or missing token"]);
            return false;
        }

        $userRole = new UserRole();
        if (!$userRole->hasRole($decoded->user_id, 'opo_coordinator')) {
            http_response_code(403); // Forbidden
            echo json_encode(["message" => "Forbidden: OPO Coordinator role required"]);
            return false;
        }

        return true;
    }
}
