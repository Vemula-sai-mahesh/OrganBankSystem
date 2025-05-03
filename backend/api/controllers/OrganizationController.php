php
<?php

namespace App\Controllers;

use App\Models\Organization;
use PDO;

class OrganizationController
{
    private PDO $db;

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
            }
            $organizations = Organization::getAll($this->db, $search, $filter, $sort . "&" . $direction, $page, $per_page);
            echo json_encode($organizations);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

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
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE name = :name");
        $stmt->execute(['name' => $data['name']]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Organization with the same name already exists']);
            http_response_code(409);
        }

        $organization = new Organization($this->db);
        $organization->setId(uniqid());
        $organization->setName($data['name']);
        $organization->setType($data['type']);
        $organization->setStreetAddress($data['street_address'] ?? null);
        $organization->setCity($data['city'] ?? null);
        $organization->setStateProvince($data['state_province'] ?? null);
        $organization->setCountry($data['country'] ?? null);
        $organization->setPostalCode($data['postal_code'] ?? null);
        $organization->setPhoneNumber($data['phone_number'] ?? null);
        $organization->setEmail($data['email'] ?? null);
        $organization->setWebsiteUrl($data['website_url'] ?? null);

        try {
            $organizationId = $organization->create();
            echo json_encode(['message' => 'Organization created', 'id' => $organizationId]);
            http_response_code(201);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

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
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE name = :name AND id != :id");
        $stmt->execute(['name' => $data['name'], 'id' => $id]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Organization with the same name already exists']);
            http_response_code(409);
            return;
        }

        $organization = new Organization($this->db);
        $organization->setId($id);
        
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Organization not found']);
            http_response_code(404);
            return;
        }
        $updated = $organization->update($data);
        
        if ($updated) {
            echo json_encode(['message' => 'Organization updated']);
            http_response_code(200);
        }
    }
    
    public function delete(string $id): void{
        $organization = new Organization($this->db);
        if(!$organization->delete($id)){
            echo json_encode(['error' => 'Organization not found']);
            http_response_code(404);
            return;
        }
        echo json_encode(['message' => 'Organization deleted']);
        http_response_code(200);
    }
}