php
<?php

namespace App\Controllers;

use App\Models\DonationEvent;
use PDO;

class DonationEventController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        try {
            $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? null;
            $sort = $_GET['sort'] ?? null;
            $direction = $_GET['direction'] ?? null;
            $search = $_GET['search'] ?? null;
            $page = $_GET['page'] ?? null;
            $per_page = $_GET['per_page'] ?? null;
            
            if ($sort !== null && $direction === null){
                 echo json_encode(['error' => 'Direction is required when using sort parameter']);
                 http_response_code(400);
                 return; 
            }

            

            $donationEvents = DonationEvent::getAll($this->db, $search, $filter, $sort . "&" . $direction);
            echo json_encode($donationEvents);
            $donationEvents = DonationEvent::getAll($this->db, $search, $filter, $sort . "&" . $direction, $page, $per_page);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

         // Check for required fields
        if (empty($data['source_organization_id'])) {
            echo json_encode(['error' => 'source_organization_id is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['created_by_user_id'])) {
            echo json_encode(['error' => 'created_by_user_id is required']);
            http_response_code(400);
            return;
        }

        // Check if source organization exists
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE id = ?");
        $stmt->execute([$data['source_organization_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'source_organization_id not found']);
            http_response_code(404);
            return;
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['created_by_user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'created_by_user_id not found']);
            http_response_code(404);
            return;
        }

        $donationEvent = new DonationEvent($this->db);
        $donationEvent->setId(uniqid());
        $donationEvent->setSourceOrganizationId($data['source_organization_id']);
        $donationEvent->setDonationType($data['donation_type'] ?? null);
        $donationEvent->setDonorExternalId($data['donor_external_id'] ?? null);
        $donationEvent->setEventStartTimestamp($data['event_start_timestamp'] ?? null);
        $donationEvent->setEventEndTimestamp($data['event_end_timestamp'] ?? null);
        $donationEvent->setStatus($data['status'] ?? null);
        $donationEvent->setCauseOfDeath($data['cause_of_death'] ?? null);
        $donationEvent->setClinicalSummary($data['clinical_summary'] ?? null);
        $donationEvent->setNotes($data['notes'] ?? null);
        $donationEvent->setCreatedByUserId($data['created_by_user_id']);

        try {
            $donationEventId = $donationEvent->create();
            echo json_encode(['message' => 'Donation Event created', 'id' => $donationEventId]);
            http_response_code(201);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

        public function update($id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Check for required fields
        if (empty($data['source_organization_id'])) {
            echo json_encode(['error' => 'source_organization_id is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['created_by_user_id'])) {
            echo json_encode(['error' => 'created_by_user_id is required']);
            http_response_code(400);
            return;
        }

        // Check if source organization exists
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE id = ?");
        $stmt->execute([$data['source_organization_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'source_organization_id not found']);
            http_response_code(404);
            return;
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['created_by_user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'created_by_user_id not found']);
            http_response_code(404);
            return;
        }

        $donationEvent = new DonationEvent($this->db);
        $donationEvent->setId($id);

        // Check if donation event exists
        $existingEvent = DonationEvent::getAll($this->db, null);
        $existingEvent = array_filter($existingEvent, function($event) use ($id) {
            return $event['id'] == $id;
        });
        if (empty($existingEvent)){
            echo json_encode(['error' => 'Donation Event not found']);
            http_response_code(404);
            return;
        }

        try {
            $updated = $donationEvent->update($data);
            if ($updated) {
                echo json_encode(['message' => 'Donation Event updated']);
                http_response_code(200);
            }
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function delete($id): void {
        $donationEvent = new DonationEvent($this->db);
        $donationEvent->setId($id);
        $donationEvent->delete();
    }
}