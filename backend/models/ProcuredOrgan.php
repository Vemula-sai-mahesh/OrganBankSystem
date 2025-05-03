php
<?php

namespace App\Models;

use PDO;
use PDOException;

class ProcuredOrgan
{
    private PDO $db;
    private string $id;
    private string $donationEventId;
    private string $organTypeId;
    private string $currentOrganizationId;
    private ?string $organExternalId;
    private ?string $procurementTimestamp;
    private ?string $preservationTimestamp;
    private ?int $estimatedWarmIschemiaTimeMinutes;
    private ?int $estimatedColdIschemiaTimeMinutes;
    private ?string $expiryTimestamp;
    private ?string $status;
    private ?string $description;
    private ?string $bloodType;
    private ?string $clinicalNotes;
    private ?string $packagingDetails;
    private string $createdByUserId;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Getters and Setters
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getDonationEventId(): string
    {
        return $this->donationEventId;
    }

    public function setDonationEventId(string $donationEventId): void
    {
        $this->donationEventId = $donationEventId;
    }

    public function getOrganTypeId(): string
    {
        return $this->organTypeId;
    }

    public function setOrganTypeId(string $organTypeId): void
    {
        $this->organTypeId = $organTypeId;
    }

    public function getCurrentOrganizationId(): string
    {
        return $this->currentOrganizationId;
    }

    public function setCurrentOrganizationId(string $currentOrganizationId): void
    {
        $this->currentOrganizationId = $currentOrganizationId;
    }

    public function getOrganExternalId(): ?string
    {
        return $this->organExternalId;
    }

    public function setOrganExternalId(?string $organExternalId): void
    {
        $this->organExternalId = $organExternalId;
    }

    public function getProcurementTimestamp(): ?string
    {
        return $this->procurementTimestamp;
    }

    public function setProcurementTimestamp(?string $procurementTimestamp): void
    {
        $this->procurementTimestamp = $procurementTimestamp;
    }

    public function getPreservationTimestamp(): ?string
    {
        return $this->preservationTimestamp;
    }

    public function setPreservationTimestamp(?string $preservationTimestamp): void
    {
        $this->preservationTimestamp = $preservationTimestamp;
    }

    public function getEstimatedWarmIschemiaTimeMinutes(): ?int
    {
        return $this->estimatedWarmIschemiaTimeMinutes;
    }

    public function setEstimatedWarmIschemiaTimeMinutes(?int $estimatedWarmIschemiaTimeMinutes): void
    {
        $this->estimatedWarmIschemiaTimeMinutes = $estimatedWarmIschemiaTimeMinutes;
    }

    public function getEstimatedColdIschemiaTimeMinutes(): ?int
    {
        return $this->estimatedColdIschemiaTimeMinutes;
    }

    public function setEstimatedColdIschemiaTimeMinutes(?int $estimatedColdIschemiaTimeMinutes): void
    {
        $this->estimatedColdIschemiaTimeMinutes = $estimatedColdIschemiaTimeMinutes;
    }

    public function getExpiryTimestamp(): ?string
    {
        return $this->expiryTimestamp;
    }

    public function setExpiryTimestamp(?string $expiryTimestamp): void
    {
        $this->expiryTimestamp = $expiryTimestamp;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    public function setBloodType(?string $bloodType): void
    {
        $this->bloodType = $bloodType;
    }

    public function getClinicalNotes(): ?string
    {
        return $this->clinicalNotes;
    }

    public function setClinicalNotes(?string $clinicalNotes): void
    {
        $this->clinicalNotes = $clinicalNotes;
    }

    public function getPackagingDetails(): ?string
    {
        return $this->packagingDetails;
    }

    public function setPackagingDetails(?string $packagingDetails): void
    {
        $this->packagingDetails = $packagingDetails;
    }

    public function getCreatedByUserId(): string
    {
        return $this->createdByUserId;
    }

    public function setCreatedByUserId(string $createdByUserId): void
    {
        $this->createdByUserId = $createdByUserId;
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
            $stmt = $this->db->prepare("INSERT INTO procured_organs (id, donation_event_id, organ_type_id, current_organization_id, organ_external_id, procurement_timestamp, preservation_timestamp, estimated_warm_ischemia_time_minutes, estimated_cold_ischemia_time_minutes, expiry_timestamp, status, description, blood_type, clinical_notes, packaging_details, created_by_user_id) VALUES (:id, :donation_event_id, :organ_type_id, :current_organization_id, :organ_external_id, :procurement_timestamp, :preservation_timestamp, :estimated_warm_ischemia_time_minutes, :estimated_cold_ischemia_time_minutes, :expiry_timestamp, :status, :description, :blood_type, :clinical_notes, :packaging_details, :created_by_user_id)");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':donation_event_id', $this->donationEventId);
            $stmt->bindValue(':organ_type_id', $this->organTypeId);
            $stmt->bindValue(':current_organization_id', $this->currentOrganizationId);
            $stmt->bindValue(':organ_external_id', $this->organExternalId);
            $stmt->bindValue(':procurement_timestamp', $this->procurementTimestamp);
            $stmt->bindValue(':preservation_timestamp', $this->preservationTimestamp);
            $stmt->bindValue(':estimated_warm_ischemia_time_minutes', $this->estimatedWarmIschemiaTimeMinutes);
            $stmt->bindValue(':estimated_cold_ischemia_time_minutes', $this->estimatedColdIschemiaTimeMinutes);
            $stmt->bindValue(':expiry_timestamp', $this->expiryTimestamp);
            $stmt->bindValue(':status', $this->status);
            $stmt->bindValue(':description', $this->description);
            $stmt->bindValue(':blood_type', $this->bloodType);
            $stmt->bindValue(':clinical_notes', $this->clinicalNotes);
            $stmt->bindValue(':packaging_details', $this->packagingDetails);
            $stmt->bindValue(':created_by_user_id', $this->createdByUserId);
            $stmt->execute();
            return $this->id;
        } catch (PDOException $e) {
            throw new PDOException("Error creating procured organ: " . $e->getMessage());
        }
    }

    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = null): array
    {
        try {
            $query = "SELECT po.* FROM procured_organs po";
            $countQuery = "SELECT COUNT(*) FROM procured_organs po";


            $whereClauses = [];
            $params = [];

            // Search Logic
            if ($search !== null) {
                $whereClauses[] = "(id LIKE :search OR organ_external_id LIKE :search OR status LIKE :search OR description LIKE :search OR blood_type LIKE :search OR clinical_notes LIKE :search OR packaging_details LIKE :search)";
                $params[':search'] = "%" . $search . "%";
            }

            if ($filter !== null) {
                foreach ($filter as $field => $value) {
                    if (in_array($field, ['id', 'organ_external_id', 'status', 'description', 'blood_type', 'clinical_notes', 'packaging_details'])) {
                        $whereClauses[] = "$field LIKE :filter_$field";
                        $params[":filter_$field"] = "%$value%";
                    }
                }
            }

            if (!empty($whereClauses)) {
                $whereClause = " WHERE " . implode(" AND ", $whereClauses);
                $query .= $whereClause;
                $countQuery .= $whereClause;
            }


            if ($sort !== null) {
                $sortParts = explode('&', $sort);
                $sortField = $sortParts[0] ?? null;
                $sortDirection = strtoupper($sortParts[1] ?? 'ASC');

                if (in_array($sortField, ['id', 'organ_external_id', 'status', 'blood_type']) && in_array($sortDirection, ['ASC', 'DESC'])) {
                    $query .= " ORDER BY $sortField $sortDirection";
                }
            }

            // Pagination logic
            if ($page !== null && $per_page !== null) {
                $per_page = $per_page ?? 10;
                $page = max(1, $page);
                $offset = ($page - 1) * $per_page;

                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $per_page;
                $params[':offset'] = $offset;
            }

            // Execute the main query
            $stmt = $db->prepare($query);            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Execute the count query
            $countStmt = $db->prepare($countQuery);
            $countStmt->execute($params);
            $totalCount = $countStmt->fetchColumn();
            return ['procured_organs' => $results, 'total_count' => $totalCount];
        } catch (PDOException $e) {
           return [];
        }
    }

    public function update(array $data): bool
    {
        try {
            // Update properties if they exist in $data
            if (isset($data['organ_external_id'])) {
                $this->organExternalId = $data['organ_external_id'];
            }
            if (isset($data['status'])) {
                $this->status = $data['status'];
            }
            if (isset($data['description'])) {
                $this->description = $data['description'];
            }
            if (isset($data['blood_type'])) {
                $this->bloodType = $data['blood_type'];
            }
            if (isset($data['clinical_notes'])) {
                $this->clinicalNotes = $data['clinical_notes'];
            }
            if (isset($data['packaging_details'])) {
                $this->packagingDetails = $data['packaging_details'];
            }

            $stmt = $this->db->prepare("UPDATE procured_organs SET organ_external_id = :organ_external_id, status = :status, description = :description, blood_type = :blood_type, clinical_notes = :clinical_notes, packaging_details = :packaging_details WHERE id = :id");
            $stmt->bindValue(':organ_external_id', $this->organExternalId);
            $stmt->bindValue(':status', $this->status);
            $stmt->bindValue(':description', $this->description);
            $stmt->bindValue(':blood_type', $this->bloodType);
            $stmt->bindValue(':clinical_notes', $this->clinicalNotes);
            $stmt->bindValue(':packaging_details', $this->packagingDetails);
            $stmt->bindValue(':id', $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(): bool
    {
        $stmt = $this->db->prepare("DELETE FROM procured_organs WHERE id = :id");
        $stmt->bindValue(':id', $this->id);
        return $stmt->execute();
    }
}