php
<?php

namespace App\Models;

use PDO;
use PDOException;

class DonationEvent
{
    private PDO $db;
    private string $id;
    private string $sourceOrganizationId;
    private ?string $donationType;
    private ?string $donorExternalId;
    private ?string $eventStartTimestamp;
    private ?string $eventEndTimestamp;
    private ?string $status;
    private ?string $causeOfDeath;
    private ?string $clinicalSummary;
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

    public function getSourceOrganizationId(): string
    {
        return $this->sourceOrganizationId;
    }

    public function setSourceOrganizationId(string $sourceOrganizationId): void
    {
        $this->sourceOrganizationId = $sourceOrganizationId;
    }

    public function getDonationType(): ?string
    {
        return $this->donationType;
    }

    public function setDonationType(?string $donationType): void
    {
        $this->donationType = $donationType;
    }

    public function getDonorExternalId(): ?string
    {
        return $this->donorExternalId;
    }

    public function setDonorExternalId(?string $donorExternalId): void
    {
        $this->donorExternalId = $donorExternalId;
    }

    public function getEventStartTimestamp(): ?string
    {
        return $this->eventStartTimestamp;
    }

    public function setEventStartTimestamp(?string $eventStartTimestamp): void
    {
        $this->eventStartTimestamp = $eventStartTimestamp;
    }

    public function getEventEndTimestamp(): ?string
    {
        return $this->eventEndTimestamp;
    }

    public function setEventEndTimestamp(?string $eventEndTimestamp): void
    {
        $this->eventEndTimestamp = $eventEndTimestamp;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getCauseOfDeath(): ?string
    {
        return $this->causeOfDeath;
    }

    public function setCauseOfDeath(?string $causeOfDeath): void
    {
        $this->causeOfDeath = $causeOfDeath;
    }

    public function getClinicalSummary(): ?string
    {
        return $this->clinicalSummary;
    }

    public function setClinicalSummary(?string $clinicalSummary): void
    {
        $this->clinicalSummary = $clinicalSummary;
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
            $stmt = $this->db->prepare("INSERT INTO donation_events (id, source_organization_id, donation_type, donor_external_id, event_start_timestamp, event_end_timestamp, status, cause_of_death, clinical_summary, notes, created_by_user_id) VALUES (:id, :source_organization_id, :donation_type, :donor_external_id, :event_start_timestamp, :event_end_timestamp, :status, :cause_of_death, :clinical_summary, :notes, :created_by_user_id)");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':source_organization_id', $this->sourceOrganizationId);
            $stmt->bindValue(':donation_type', $this->donationType);
            $stmt->bindValue(':donor_external_id', $this->donorExternalId);
            $stmt->bindValue(':event_start_timestamp', $this->eventStartTimestamp);
            $stmt->bindValue(':event_end_timestamp', $this->eventEndTimestamp);
            $stmt->bindValue(':status', $this->status);
            $stmt->bindValue(':cause_of_death', $this->causeOfDeath);
            $stmt->bindValue(':clinical_summary', $this->clinicalSummary);
            $stmt->bindValue(':notes', $this->notes);
            $stmt->bindValue(':created_by_user_id', $this->createdbyUserId);
            $stmt->execute();

            return $this->id;
        } catch (PDOException $e) {
            throw new PDOException("Error creating donation event: " . $e->getMessage());
        }
    }

    public function update(array $data): bool
    {
        try {
            $this->donationType = $data['donation_type'] ?? $this->donationType;
            $this->donorExternalId = $data['donor_external_id'] ?? $this->donorExternalId;
            $this->status = $data['status'] ?? $this->status;
            $this->causeOfDeath = $data['cause_of_death'] ?? $this->causeOfDeath;
            $this->clinicalSummary = $data['clinical_summary'] ?? $this->clinicalSummary;
            $this->notes = $data['notes'] ?? $this->notes;

            $stmt = $this->db->prepare("UPDATE donation_events SET donation_type = :donation_type, donor_external_id = :donor_external_id, status = :status, cause_of_death = :cause_of_death, clinical_summary = :clinical_summary, notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindValue(':donation_type', $this->donationType);
            $stmt->bindValue(':donor_external_id', $this->donorExternalId);
            $stmt->bindValue(':status', $this->status);
            $stmt->bindValue(':cause_of_death', $this->causeOfDeath);
            $stmt->bindValue(':clinical_summary', $this->clinicalSummary);
            $stmt->bindValue(':notes', $this->notes);
            $stmt->bindValue(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM donation_events WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = 10): array
    {
        try {
            $whereClause = [];
            $params = [];

            if ($search !== null) {
                $whereClause[] = "(id LIKE :search OR donation_type LIKE :search OR donor_external_id LIKE :search OR status LIKE :search OR cause_of_death LIKE :search OR clinical_summary LIKE :search OR notes LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if ($filter !== null) {
                foreach ($filter as $field => $value) {
                    if (in_array($field, ['id', 'donation_type', 'donor_external_id', 'status', 'cause_of_death', 'clinical_summary', 'notes'])) {
                        $whereClause[] = "$field = :$field";
                        $params[":$field"] = $value;
                    }
                }
            }

            $sql = "SELECT * FROM donation_events";
            if (!empty($whereClause)) {
                $sql .= " WHERE " . implode(" AND ", $whereClause);
            }

            if ($sort !== null) {
                $sortParts = explode('&', $sort);
                $field = $sortParts[0];
                $direction = strtoupper($sortParts[1]) === 'DESC' ? 'DESC' : 'ASC';
                if (in_array($field, ['id', 'donation_type', 'donor_external_id', 'status', 'cause_of_death'])) {
                    $sql .= " ORDER BY $field $direction";
                }
            }

            if ($page !== null && $per_page !== null) {
                $page = max(1, $page);
                $offset = ($page - 1) * $per_page;
                $sql .= " LIMIT :per_page OFFSET :offset";
                $params[':per_page'] = $per_page;
                $params[':offset'] = $offset;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);            
            $donation_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $countSql = "SELECT COUNT(*) FROM donation_events";
            if (!empty($whereClause)) {
                $countSql .= " WHERE " . implode(" AND ", $whereClause);
            }

            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $totalCount = $countStmt->fetchColumn();

            return ['donation_events' => $donation_events, 'total_count' => $totalCount];
        } catch (PDOException $e) {
            return ['donation_events' => [], 'total_count' => 0];
        }
    }
}