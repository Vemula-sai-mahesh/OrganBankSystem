php
<?php

require_once __DIR__ . '/../../models/ApiKey.php';
require_once __DIR__ . '/BaseController.php';

class ApiKeyController extends BaseController {
    private $apiKeyModel;

    public function __construct($db) {
        parent::__construct();
        $this->apiKeyModel = new ApiKey($db);
    }

    public function createApiKey($data) {
        if (!isset($data['organization_id']) || !isset($data['role_name'])) {
            $this->sendErrorResponse(400, 'Organization ID and role name are required.');
            return;
        }

        $organizationId = $data['organization_id'];
        $roleName = $data['role_name'];
        $expiresAt = $data['expires_at'] ?? null;

        $apiKeyData = [
            'organization_id' => $organizationId,
            'role_name' => $roleName,
            'expires_at' => $expiresAt
        ];

        try {
            $apiKey = $this->apiKeyModel->create($apiKeyData);
            $this->sendResponse(201, $apiKey);
        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
        }
    }

    public function getApiKey($id) {
        try {
            $apiKey = $this->apiKeyModel->findById($id);
            if ($apiKey) {
                $this->sendResponse(200, $apiKey);
            } else {
                $this->sendErrorResponse(404, 'API Key not found.');
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
        }
    }

    public function getAllApiKeys() {
        try {
            $apiKeys = $this->apiKeyModel->findAll();
            $this->sendResponse(200, $apiKeys);
        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
        }
    }

    public function updateApiKey($id, $data) {
        try {
             if (empty($data)) {
                $this->sendErrorResponse(400, 'No data provided for update.');
                return;
            }

             $existingApiKey = $this->apiKeyModel->findById($id);
            if (!$existingApiKey) {
                $this->sendErrorResponse(404, 'Api key not found.');
                return;
            }
           $this->apiKeyModel->update($id,$data);
            $this->sendResponse(200, 'API Key updated successfully.');
        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
        }
    }

    public function deleteApiKey($id) {
        try {
             $existingApiKey = $this->apiKeyModel->findById($id);
            if (!$existingApiKey) {
                $this->sendErrorResponse(404, 'Api key not found.');
                return;
            }
            $this->apiKeyModel->delete($id);
            $this->sendResponse(200, 'API Key deleted successfully.');
        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
        }
    }
}
?>