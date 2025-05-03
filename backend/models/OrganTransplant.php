<?php
namespace App\Models;

use PDO;
use PDOException;

class OrganTransplant
{
    private PDO $db;
    private string $id;
    private string $procuredOrganId;
    private string $recipientUserId;
    private string $transplantOrganizationId;
    private ?string $transplantTimestamp;
    private ?string $postTransplantStatus;
    private ?string $notes;
    private string $createdUserId;
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

    public function getRecipientUserId(): string
    {
        return $this->recipientUserId;
    }

    public function setRecipientUserId(string $recipientUserId): void
    {
        $this->recipientUserId = $recipientUserId;
    }

    public function getTransplantOrganizationId(): string
    {
        return $this->transplantOrganizationId;
    }

    public function setTransplantOrganizationId(string $transplantOrganizationId): void
    {
        $this->transplantOrganizationId = $transplantOrganizationId;
    }

    public function getTransplantTimestamp(): ?string
    {
        return $this->transplantTimestamp;
    }

    public function setTransplantTimestamp(?string $transplantTimestamp): void
    {
        $this->transplantTimestamp = $transplantTimestamp;
    }

    public function getPostTransplantStatus(): ?string
    {
        return $this->postTransplantStatus;
    }

    public function setPostTransplantStatus(?string $postTransplantStatus): void
    {
        $this->postTransplantStatus = $postTransplantStatus;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getCreatedUserId(): string
    {
        return $this->createdUserId;
    }

    public function setCreatedUserId(string $createdUserId): void
    {
        $this->createdUserId = $createdUserId;
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
            $stmt = $this->db->prepare("INSERT INTO organ_transplants (id, procured_organ_id, recipient_user_id, transplant_organization_id, transplant_timestamp, post_transplant_status, notes, created_by_user_id) VALUES (:id, :procured_organ_id, :recipient_user_id, :transplant_organization_id, :transplant_timestamp, :post_transplant_status, :notes, :created_by_user_id)");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':procured_organ_id', $this->procuredOrganId);
            $stmt->bindValue(':recipient_user_id', $this->recipientUserId);
            $stmt->bindValue(':transplant_organization_id', $this->transplantOrganizationId);
            $stmt->bindValue(':transplant_timestamp', $this->transplantTimestamp);
            $stmt->bindValue(':post_transplant_status', $this->postTransplantStatus);
            $stmt->bindValue(':notes', $this->notes);
            $stmt->bindValue(':created_by_user_id', $this->createdUserId);
            $stmt->execute();

            return $this->id;
        } catch (PDOException $e) {
            throw new PDOException("Error creating organ transplant: " . $e->getMessage());
        }
    }

    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = null): array
    {
        try {
            $query = "SELECT ot.* FROM organ_transplants ot WHERE 1=1";
            $countQuery = "SELECT COUNT(*) FROM organ_transplants WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $query .= " AND (ot.id LIKE :search OR ot.post_transplant_status LIKE :search OR ot.notes LIKE :search)";
                $countQuery .= " AND (id LIKE :search OR post_transplant_status LIKE :search OR notes LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($filter)) {
                foreach ($filter as $key => $value) {
                    if (in_array($key, ['id', 'transplant_timestamp', 'post_transplant_status', 'notes'])) {
                        $query .= " AND ot.$key = :$key";
                        $countQuery .= " AND $key = :$key";
                        $params[":$key"] = $value;
                    }
                }
            }

            if (!empty($sort)) {
                [$field, $direction] = explode('&', $sort);
                if (in_array($field, ['id', 'transplant_timestamp', 'post_transplant_status'])) {
                    $query .= " ORDER BY ot.$field";
                    if (in_array($direction, ['asc', 'desc'])) {
                        $query .= " $direction";
                    }
                }
            }

            $stmtCount = $db->prepare($countQuery);

            foreach ($params as $key => &$value) {
                $stmtCount->bindParam($key, $value);
            }
            $stmtCount->execute();
            $totalCount = $stmtCount->fetchColumn();

            if (!empty($page) && !empty($per_page)) {
                $per_page = $per_page ?? 10;
                $page = $page < 1 ? 1 : $page;
                $offset = ($page - 1) * $per_page;
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $per_page;
                $params[':offset'] = $offset;
            }

            $stmt = $db->prepare($query);
            foreach ($params as $key => &$value) {
                $stmt->bindParam($key, $value);
            }

            $stmt->execute();
            return [
                'organ_transplants' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total_count' => $totalCount,
            ];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function update(array $data): bool
    {
        try {
            $this->setTransplantTimestamp($data['transplant_timestamp'] ?? $this->getTransplantTimestamp());
            $this->setPostTransplantStatus($data['post_transplant_status'] ?? $this->getPostTransplantStatus());
            $this->setNotes($data['notes'] ?? $this->getNotes());
            
            $stmt = $this->db->prepare("UPDATE organ_transplants SET transplant_timestamp = :transplant_timestamp, post_transplant_status = :post_transplant_status, notes = :notes WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':transplant_timestamp', $this->transplantTimestamp);
            $stmt->bindValue(':post_transplant_status', $this->postTransplantStatus);
            $stmt->bindValue(':notes', $this->notes);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Error updating organ transplant: " . $e->getMessage());
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM organ_transplants WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Error deleting organ transplant: " . $e->getMessage());
        }
    }
}