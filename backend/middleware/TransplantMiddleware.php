<?php
namespace OrganBankSystem\backend\middleware;

use OrganBankSystem\backend\utils\Database;
use PDO;

class TransplantMiddleware
{
    public static function handle($request)
    {
        // Check if the user is authenticated
        if (!isset($request->user_id) || empty($request->user_id)) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized: User not authenticated"]);
            return false;
        }

        // Get the user's role
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT role_name FROM user_roles WHERE user_id = :user_id AND role_name = 'transplant_coordinator'");
        $stmt->execute(['user_id' => $request->user_id]);
        $userRole = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user has the Transplant Coordinator role
        if (!$userRole) {
            http_response_code(403);
            echo json_encode(["message" => "Forbidden: User is not a Transplant Coordinator"]);
            return false;
        }

        return true;
    }
}