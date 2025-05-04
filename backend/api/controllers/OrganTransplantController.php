<?php
namespace App\Controllers;

use App\Models\OrganTransplant;
use PDO;

class OrganTransplantController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function userIdExists(string $userId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() !== false;
    }

    public function index(): void
    {
        $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $sort = $_GET['sort'] ?? null;
        $direction = $_GET['direction'] ?? null;
        $page = $_GET['page'] ?? null;
        $per_page = $_GET['per_page'] ?? null;

        if ($sort !== null && $direction === null) {
            echo json_encode(['error' => 'Direction is required']);
            http_response_code(400);
            return;
        }

        try {
            $organTransplants = OrganTransplant::getAll($this->db, $filter, "$sort&$direction", $page, $per_page);
            echo json_encode($organTransplants);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['procured_organ_id']) || empty($data['recipient_user_id']) || empty($data['transplant_organization_id']) || empty($data['created_by_user_id'])) {
            echo json_encode(['error' => 'Missing required fields']);
            http_response_code(400);
            return;
        }

        // Check if procured_organ_id exists.
        $stmt = $this->db->prepare("SELECT id FROM procured_organs WHERE id = ?");
        $stmt->execute([$data['procured_organ_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Procured organ not found']);
            http_response_code(404);
            return;
        }

        // Check if recipient_user_id exists.
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['recipient_user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Recipient user not found']);
            http_response_code(404);
            return;
        }

        // Check if transplant_organization_id exists.
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE id = ?");
        $stmt->execute([$data['transplant_organization_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Transplant organization not found']);
            http_response_code(404);
            return;
        }
        // Check if created_by_user_id exists
        if (!$this->userIdExists($data['created_by_user_id'])) {
            echo json_encode(['error' => 'Created by user not found']);
            http_response_code(404);
            return;
        }

        $organTransplant = new OrganTransplant($this->db);
        $organTransplant->setId(uniqid());
        $organTransplant->setProcuredOrganId($data['procured_organ_id']);
        $organTransplant->setRecipientUserId($data['recipient_user_id']);
        $organTransplant->setTransplantOrganizationId($data['transplant_organization_id']);
        $organTransplant->setTransplantTimestamp($data['transplant_timestamp'] ?? null);
        $organTransplant->setPostTransplantStatus($data['post_transplant_status'] ?? null);
        $organTransplant->setNotes($data['notes'] ?? null);
        $organTransplant->setCreatedByUserId($data['created_by_user_id']);

        try {
            $organTransplantId = $organTransplant->create();
            echo json_encode(['message' => 'Organ Transplant created', 'id' => $organTransplantId]);
            http_response_code(201);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['procured_organ_id']) || empty($data['recipient_user_id']) || empty($data['transplant_organization_id']) || empty($data['created_by_user_id'])) {
            echo json_encode(['error' => 'Missing required fields']);
            http_response_code(400);
            return;
        }

        // Check if procured_organ_id exists.
        $stmt = $this->db->prepare("SELECT id FROM procured_organs WHERE id = ?");
        $stmt->execute([$data['procured_organ_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Procured organ not found']);
            http_response_code(404);
            return;
        }

        // Check if recipient_user_id exists.
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['recipient_user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Recipient user not found']);
            http_response_code(404);
            return;
        }

        // Check if transplant_organization_id exists.
        $stmt = $this->db->prepare("SELECT id FROM organizations WHERE id = ?");
        $stmt->execute([$data['transplant_organization_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Transplant organization not found']);
            http_response_code(404);
            return;
        }
        // Check if created_by_user_id exists
        if (!$this->userIdExists($data['created_by_user_id'])) {
            echo json_encode(['error' => 'Created by user not found']);
            http_response_code(404);
            return;
        }

        // Check if organ transplant exists
        $stmt = $this->db->prepare("SELECT id FROM organ_transplants WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Organ transplant not found']);
            http_response_code(404);
            return;
        }

        $organTransplant = new OrganTransplant($this->db);
        $organTransplant->setId($id);
        $organTransplant->setProcuredOrganId($data['procured_organ_id']);
        $organTransplant->setRecipientUserId($data['recipient_user_id']);
        $organTransplant->setTransplantOrganizationId($data['transplant_organization_id']);
        $organTransplant->setTransplantTimestamp($data['transplant_timestamp'] ?? null);
        $organTransplant->setPostTransplantStatus($data['post_transplant_status'] ?? null);
        $organTransplant->setNotes($data['notes'] ?? null);
        $organTransplant->setCreatedByUserId($data['created_by_user_id']);

        try {
            if ($organTransplant->update($data)) {
                echo json_encode(['message' => 'Organ transplant updated']);
                http_response_code(200);
            } else {
                echo json_encode(['error' => 'Organ transplant not updated']);
                http_response_code(500);
            }
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function delete(string $id): void
    {
        $organTransplant = new OrganTransplant($this->db);
        try {
            if (!$organTransplant->delete($id)) {
                echo json_encode(['error' => 'Organ transplant not found']);
                http_response_code(404);
            } else {
                echo json_encode(['message' => 'Organ transplant deleted']);
                http_response_code(200);
            }
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

}