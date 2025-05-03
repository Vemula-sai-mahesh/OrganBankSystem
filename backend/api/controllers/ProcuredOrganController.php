php
<?php

namespace App\Controllers;

use App\Models\ProcuredOrgan;
use PDO;

class ProcuredOrganController
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

            if ($sort && !$direction){
                echo json_encode(['error' => 'Direction is required when sort is provided']);
                http_response_code(400);
                return;
            }

            $procuredOrgans = ProcuredOrgan::getAll($this->db, $search, $filter, $sort, $direction, $page, $per_page);
            echo json_encode($procuredOrgans);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    private function validateProcuredOrganData($data): ?array
    {
      
        
        // Validate data
        if (empty($data['donation_event_id']) || empty($data['organ_type_id']) || empty($data['current_organization_id']) || empty($data['created_by_user_id']))
        {
            echo json_encode(['error' => 'Missing required fields']);
            http_response_code(400);
            return;
        }

        // Check if donation_event_id exists
        $stmt = $this->db->prepare("SELECT id FROM donation_events WHERE id = ?");
        $stmt->execute([$data['donation_event_id']]);
        $donationEvent = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$donationEvent) {
            echo json_encode(['error' => 'Donation event not found']);
            http_response_code(404);
            return;
        }

        // Check if organ_type_id exists
        $stmt = $this->db->prepare("SELECT id FROM organ_types WHERE id = ?");
        $stmt->execute([$data['organ_type_id']]);
        $organType = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$organType) {
            echo json_encode(['error' => 'Organ type not found']);
            http_response_code(404);
            return;
        }

        // Check if current_organization_id exists
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE id = ?");
        $stmt->execute([$data['current_organization_id']]);
        $currentOrganization = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentOrganization) {
            echo json_encode(['error' => 'Current organization not found']);
            http_response_code(404);
            return;
        }

        // Check if created_by_user_id exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['created_by_user_id']]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['error' => 'Created by user not found']);
            http_response_code(404);
            return;
        }

        return null;
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $validationError = $this->validateProcuredOrganData($data);
        if($validationError) return;

        
        $procuredOrgan = new ProcuredOrgan($this->db);
        $procuredOrgan->setId(uniqid());
        $procuredOrgan->setDonationEventId($data['donation_event_id']);
        $procuredOrgan->setOrganTypeId($data['organ_type_id']);
        $procuredOrgan->setCurrentOrganizationId($data['current_organization_id']);
        $procuredOrgan->setOrganExternalId($data['organ_external_id'] ?? null);
        $procuredOrgan->setProcurementTimestamp($data['procurement_timestamp'] ?? null);
        $procuredOrgan->setPreservationTimestamp($data['preservation_timestamp'] ?? null);
        $procuredOrgan->setEstimatedWarmIschemiaTimeMinutes($data['estimated_warm_ischemia_time_minutes'] ?? null);
        $procuredOrgan->setEstimatedColdIschemiaTimeMinutes($data['estimated_cold_ischemia_time_minutes'] ?? null);
        $procuredOrgan->setExpiryTimestamp($data['expiry_timestamp'] ?? null);
        $procuredOrgan->setStatus($data['status'] ?? null);
        $procuredOrgan->setDescription($data['description'] ?? null);
        $procuredOrgan->setBloodType($data['blood_type'] ?? null);
        $procuredOrgan->setClinicalNotes($data['clinical_notes'] ?? null);
        $procuredOrgan->setPackagingDetails($data['packaging_details'] ?? null);
        $procuredOrgan->setCreatedByUserId($data['created_by_user_id']);

        try {
            $procuredOrganId = $procuredOrgan->create();
            echo json_encode(['message' => 'Procured Organ created', 'id' => $procuredOrganId]);
            http_response_code(201);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $validationError = $this->validateProcuredOrganData($data);
        if($validationError) return;

        // Check if exists
        $procuredOrgan = new ProcuredOrgan($this->db);
        $procuredOrgan->setId($id);

        if (!$procuredOrgan->exists()) {
          echo json_encode(['error' => 'Procured Organ not found']);
          http_response_code(404);
          return;
        }

        $procuredOrgan->setDonationEventId($data['donation_event_id']);
        $procuredOrgan->setOrganTypeId($data['organ_type_id']);
        $procuredOrgan->setCurrentOrganizationId($data['current_organization_id']);
        $procuredOrgan->setOrganExternalId($data['organ_external_id'] ?? null);
        $procuredOrgan->setStatus($data['status'] ?? null);
        $procuredOrgan->setDescription($data['description'] ?? null);
        $procuredOrgan->setBloodType($data['blood_type'] ?? null);
        $procuredOrgan->setClinicalNotes($data['clinical_notes'] ?? null);
        $procuredOrgan->setPackagingDetails($data['packaging_details'] ?? null);

        try {
            $procuredOrgan->update($data);
            echo json_encode(['message' => 'Procured Organ updated']);
            http_response_code(200);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function delete(string $id): void
    {
      
    }
}