php
<?php

namespace App\Controllers;

use App\Models\MedicalMarker;
use PDO;

class MedicalMarkerController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        try {
            $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $sort = $_GET['sort'] ?? null;
            $direction = $_GET['direction'] ?? null;
            $page = $_GET['page'] ?? null;
            $per_page = $_GET['per_page'] ?? null;

            if ($sort && !$direction) {
                echo json_encode(['error' => 'direction is required']);
                http_response_code(400);
                return; 
            }

            $medicalMarkers = MedicalMarker::getAll($this->db, $filter, $sort . '&' . $direction, $page, $per_page);
            echo json_encode($medicalMarkers);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate data
        if (empty($data['procured_organ_id'])) {
            echo json_encode(['error' => 'procured_organ_id is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['marker_type'])) {
            echo json_encode(['error' => 'marker_type is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['marker_value'])) {
            echo json_encode(['error' => 'marker_value is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['created_by_user_id'])) {
            echo json_encode(['error' => 'created_by_user_id is required']);
            http_response_code(400);
            return;
        }

        //Check if the ids exists
        $stmt = $this->db->prepare("SELECT id FROM procured_organs WHERE id = ?");
        $stmt->execute([$data['procured_organ_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'procured_organ_id not found']);
            http_response_code(404);
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['created_by_user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'created_by_user_id not found']);
            http_response_code(404);
            return;
        }

        $medicalMarker = new MedicalMarker($this->db);
        $medicalMarker->setId(uniqid());
        $medicalMarker->setProcuredOrganId($data['procured_organ_id']);
        $medicalMarker->setMarkerType($data['marker_type']);
        $medicalMarker->setMarkerValue($data['marker_value']);
        $medicalMarker->setNotes($data['notes'] ?? null);
        $medicalMarker->setCreatedByUserId($data['created_by_user_id']);

        try {
            $medicalMarkerId = $medicalMarker->create();
            echo json_encode(['message' => 'Medical Marker created', 'id' => $medicalMarkerId]);
            http_response_code(201);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function update($id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate data
        if (empty($data['procured_organ_id'])) {
            echo json_encode(['error' => 'procured_organ_id is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['marker_type'])) {
            echo json_encode(['error' => 'marker_type is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['marker_value'])) {
            echo json_encode(['error' => 'marker_value is required']);
            http_response_code(400);
            return;
        }
        if (empty($data['created_by_user_id'])) {
            echo json_encode(['error' => 'created_by_user_id is required']);
            http_response_code(400);
            return;
        }

        //Check if the ids exists
        $stmt = $this->db->prepare("SELECT id FROM procured_organs WHERE id = ?");
        $stmt->execute([$data['procured_organ_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'procured_organ_id not found']);
            http_response_code(404);
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$data['created_by_user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'created_by_user_id not found']);
            http_response_code(404);
            return;
        }

        $medicalMarker = new MedicalMarker($this->db);

        try {
            $stmt = $this->db->prepare("SELECT id FROM medical_markers WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                echo json_encode(['error' => 'Medical Marker not found']);
                http_response_code(404);
                return;
            }
            $medicalMarker->setId($id);
            $medicalMarker->update($data);
            echo json_encode(['message' => 'Medical Marker updated']);
            http_response_code(200);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }

    public function delete($id): void
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM medical_markers WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                echo json_encode(['error' => 'Medical Marker not found']);
                http_response_code(404);
                return;
            }
            $medicalMarker = new MedicalMarker($this->db);
            $medicalMarker->setId($id);
            $medicalMarker->delete();
            echo json_encode(['message' => 'Medical Marker deleted']);
            http_response_code(200);
        } catch (\PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }
}