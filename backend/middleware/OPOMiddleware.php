php
<?php

namespace OrganBankSystem\Backend\Middleware;

use OrganBankSystem\Backend\Models\User;
use OrganBankSystem\Backend\Models\UserRole;

use OrganBankSystem\Backend\Utils\Database;
use PDO;

class OPOMiddleware
{
    public static function handle()
    {
        // Check if the user is logged in and has the OPO Coordinator role
        $token = self::extractTokenFromHeader();

        if (!$token) {
            http_response_code(401); // Unauthorized
            echo json_encode(['message' => 'Unauthorized: No token provided']);
            exit;
        }

        $payload = self::validateToken($token);

        if (!$payload) {
            http_response_code(401); // Unauthorized
            echo json_encode(['message' => 'Unauthorized: Invalid token']);
            exit;
        }

        if (!self::isOPO($payload['user_id'])) {
            http_response_code(403); // Forbidden
            echo json_encode(['message' => 'Forbidden: User