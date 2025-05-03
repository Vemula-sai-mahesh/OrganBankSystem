php
<?php

namespace App\Models;

use PDO;
use PDOException;

class MedicalMarker
{
    private PDO $db;
    private string $id;
    private string $procuredOrganId;
    private string $markerType;
    private string $markerValue;
    private ?string $notes;
    private string $createdbyUserId;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getProcuredOrganId(): string
    {
        return $this->procuredOrganId;
    }

    public function setProcuredOrganId(string $procuredOrganId): void
    {
        $this->procuredOrganId = $procuredOrganId;
    }

    public function getMarkerType(): string
    {
        return $this->markerType;
    }

    public function setMarkerType(string $markerType): void
    {
        $this->markerType = $markerType;
    }

    public function getMarkerValue(): string
    {
        return $this->markerValue;
    }

    public function setMarkerValue(string $markerValue): void
    {
        $this->markerValue = $markerValue;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getCreatedByUserId(): string
    {
        return $this->createdbyUserId;
    }

    public function setCreatedByUserId(string $createdbyUserId): void
    {
        $this->createdbyUserId = $createdbyUserId;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function create(): string
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO medical_markers (id, procured_organ_id, marker_type, marker_value, notes, created_by_user_id) VALUES (:id, :procured_organ_id, :marker_type, :marker_value, :notes, :created_by_user_id)");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':procured_organ_id', $this->procuredOrganId);
            $stmt->bindValue(':marker_type', $this->markerType);
            $stmt->bindValue(':marker_value', $this->markerValue);
            $stmt->bindValue(':notes', $this->notes);
            $stmt->bindValue(':created_by_user_id', $this->createdbyUserId);
            $stmt->execute();

            return $this->id;
        } catch (PDOException $e) {
            throw new PDOException("Error creating medical marker: " . $e->getMessage());
        }
    }

    public function update(array $data): bool
    {
        try {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }

            $stmt = $this->db->prepare("UPDATE medical_markers SET marker_type = :marker_type, marker_value = :marker_value, notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':marker_type', $this->markerType);
            $stmt->bindValue(':marker_value', $this->markerValue);
            $stmt->bindValue(':notes', $this->notes);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM medical_markers WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }







    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = null): array
    {
        try {
            $query = "SELECT * FROM medical_markers WHERE 1=1";
            $countQuery = "SELECT COUNT(*) FROM medical_markers WHERE 1=1";
            $countParams = [];
            $params = [];

            if (!empty($search)) {
                $query .= " AND (id LIKE :search OR marker_type LIKE :search OR marker_value LIKE :search OR notes LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            if (!empty($filter)) {
                foreach ($filter as $field => $value) {
                    $countQuery .= " AND $field = :$field";
                    if (in_array($field, ['id', 'marker_type', 'marker_value', 'notes'])) {
                        $query .= " AND $field = :$field";
                        $params[":$field"] = $value;
                    }
                }
            }

            if (!empty($sort)) {
                $sortParts = explode('&', $sort);
                $field = $sortParts[0];
                $direction = $sortParts[1] ?? 'asc';
                if (in_array($field, ['id', 'marker_type', 'marker_value'])) {
                    $query .= " ORDER BY $field $direction";
                }
            }

            if ($page !== null && $per_page !== null) {
                $per_page = $per_page ?: 10;
                $page = max(1, $page); 
                $offset = ($page - 1) * $per_page;
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $per_page;
                $params[':offset'] = $offset;
            }

            
            foreach ($params as $key => $value) {
                $countParams[$key] = $value;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $medical_markers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $db->prepare($countQuery);
            $stmt->execute($countParams);
            $total_count = $stmt->fetchColumn();

            return [
                'medical_markers' => $medical_markers,
                'total_count' => $total_count
            ];
        } catch (PDOException $e) {
                return [];
        }
    }
}