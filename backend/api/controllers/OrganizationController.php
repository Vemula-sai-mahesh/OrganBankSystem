<?php

namespace OrganBankSystem\backend\api\Controllers;

use OrganBankSystem\backend\Models\Organization;
use OrganBankSystem\backend\Models\UserRole;
use PDO;
use OrganBankSystem\backend\api\Controllers\BaseController;

class OrganizationController extends BaseController
{
    private $db;

    public function __construct(PDO $db)
    {
      $this->db = $db;
    }
    
    public function index(): void
    {
        try {
            $search = $_GET['search'] ?? null; 

            $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? null;
            $sort = $_GET['sort'] ?? null;
            $direction = $_GET['direction'] ?? null;
            $page = $_GET['page'] ?? null;
            $per_page = $_GET['per_page'] ?? null;

            if ($sort !== null && $direction === null) {
                echo json_encode(['error' => 'Direction is required when sort is provided']);
                http_response_code(400);
                return;
            }$organizations = Organization::getAll($this->db, $search, $filter, $sort, $direction, $page, $per_page);
            
            echo json_encode($organizations);
            http_response_code(200);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }
    
    public function show(string $id): void
    {
         try {
                $organization = new Organization($this->db);
                $orgData = $organization->getById($id);
    
                if (!$orgData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Organization not found']);
                    return;
                }

            http_response_code(200);
            echo json_encode($orgData);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public function store(): void
    { 
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name'])) {
            echo json_encode(['error' => 'Name is required']);
            http_response_code(400);
            return;
        }

        if (empty($data['type'])) { 
            echo json_encode(['error' => 'Type is required']);   
            http_response_code(400);
            return;
            http_response_code(400);
            return;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format']);
            http_response_code(400);
            return;
        }

        if (!empty($data['website_url']) && !filter_var($data['website_url'], FILTER_VALIDATE_URL)) {
            echo json_encode(['error' => 'Invalid website URL format']);
            http_response_code(400);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE name = :name");
        $stmt->execute(['name' => $data['name']]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Organization with the same name already exists']);
            http_response_code(409); 
            return;
        }
        

        $organization = new Organization($this->db);
        $organization->setStreetAddress($data['street_address'] ?? null);
        $organization->setCity($data['city'] ?? null);
        $organization->setStateProvince($data['state_province'] ?? null);
        $organization->setCountry($data['country'] ?? null);
        $organization->setPostalCode($data['postal_code'] ?? null);
        $organization->setPhoneNumber($data['phone_number'] ?? null);
        $organization->setEmail($data['email'] ?? null);
        $organization->setWebsiteUrl($data['website_url'] ?? null);
        $organization->setName($data['name']);
        $organization->setType($data['type']);
        try {
            $organizationId = $organization->create();
            echo json_encode(['message' => 'Organization created', 'id' => $organizationId ]);
            http_response_code(201);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }
    
    public function update(string $id): void
    {
        // Validate data
        if (empty($data['name'])) {
            echo json_encode(['error' => 'Name is required']);
            http_response_code(400);
            return;
        }

        if (empty($data['type'])) {
            echo json_encode(['error' => 'Type is required']);
            http_response_code(400);
            return;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format']);
            http_response_code(400);
            return;
        }

        if (!empty($data['website_url']) && !filter_var($data['website_url'], FILTER_VALIDATE_URL)) {
            echo json_encode(['error' => 'Invalid website URL format']);
            http_response_code(400);
            return;
        }
    
        $data = json_decode(file_get_contents('php://input'), true);
        $organization = new Organization($this->db);
    
        // Check if organization exists
        $organizationData = $organization->getById($id);
        if (!$organizationData) {
            echo json_encode(['error' => 'Organization not found']);
            http_response_code(404);
            return;
        }
    
        $organization->setId($id);
        $organization->setName($data['name'] ?? $organizationData['name']);
        $organization->setType($data['type'] ?? $organizationData['type']);
        $organization->setStreetAddress($data['street_address'] ?? $organizationData['street_address']);
        $organization->setCity($data['city'] ?? $organizationData['city']);
        $organization->setStateProvince($data['state_province'] ?? $organizationData['state_province']);
        $organization->setCountry($data['country'] ?? $organizationData['country']);
        $updated = $organization->update($data);
        
        if ($updated) {
            echo json_encode(['message' => 'Organization updated']);
            http_response_code(200);
        }
    }

    public function delete(string $id): void
    {
        $organization = new Organization($this->db);
        try {
            // Check if the organization exists
            $orgData = $organization->getById($id);
            if (!$orgData) {
                http_response_code(404);
                echo json_encode(['error' => 'Organization not found']);
                return;
            }
    
            $organization->delete();
            echo json_encode(['message' => 'Organization deleted', "id" => $id]);
            http_response_code(200);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }   
    }
}