<?php
namespace App\Models;


use App\Models\OrganType;
use App\Utils\Database;
use PDO;
use PDOException;

class ProcuredOrgan extends BaseModel
{
    protected string $tableName = 'procured_organs';
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
    private string $description;
    private ?string $bloodType;
    private ?string $clinicalNotes;
    private string $createdByUserId;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }
    // Getters and Setters

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

    public function getPackagingDetails(): string
    {
        return $this->packagingDetails ?? "";
    }

    public function setPackagingDetails(string $packagingDetails): void
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


    public function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = null): array
    {
        try {
            $query = "SELECT po.*, ot.name as organ_name FROM procured_organs po LEFT JOIN organ_types ot ON po.organ_type_id = ot.id";
            $countQuery = "SELECT COUNT(*) FROM procured_organs po";


            $whereClauses = [];
            $params = [];

            // Search Logic
            if ($search !== null) {
                $whereClauses[] = "(po.id LIKE :search OR organ_external_id LIKE :search OR status LIKE :search OR description LIKE :search OR blood_type LIKE :search OR clinical_notes LIKE :search)";
                $params[':search'] = "%" . $search . "%";
            }

            if ($filter !== null) {
                foreach ($filter as $field => $value) {
                    if (in_array($field, ['id', 'organ_external_id', 'status', 'description', 'blood_type', 'clinical_notes', 'organ_type_id'])) {
                        $whereClauses[] = "po.$field LIKE :filter_$field";
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

                if (in_array($sortField, ['id', 'organ_external_id', 'status', 'blood_type','organ_type_id']) && in_array($sortDirection, ['ASC', 'DESC'])) {
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
            // Handle the exception or log the error
            error_log("PDO Exception in getAll: " . $e->getMessage());
            // Check if the error message indicates a problem with the database connection
            if (strpos($e->getMessage(), 'SQLSTATE[HY000]') !== false) {
                // Database connection error, try reconnecting
               (new Database())->reconnect();
            }
           return [];
        }
    }

    public function getById(string $id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM procured_organs WHERE id = :id");
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                $this->loadData($result);
                return $this;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function loadData(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->donationEventId = $data['donation_event_id'] ?? null;
        $this->organTypeId = $data['organ_type_id'] ?? null;
        $this->currentOrganizationId = $data['current_organization_id'] ?? null;
        $this->organExternalId = $data['organ_external_id'] ?? null;
        $this->procurementTimestamp = $data['procurement_timestamp'] ?? null;
        $this->preservationTimestamp = $data['preservation_timestamp'] ?? null;
        $this->estimatedWarmIschemiaTimeMinutes = $data['estimated_warm_ischemia_time_minutes'] ?? null;
        $this->estimatedColdIschemiaTimeMinutes = $data['estimated_cold_ischemia_time_minutes'] ?? null;
        $this->expiryTimestamp = $data['expiry_timestamp'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->bloodType = $data['blood_type'] ?? null;
        $this->clinicalNotes = $data['clinical_notes'] ?? null;
        $this->createdByUserId = $data['created_by_user_id'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    public function create(): bool
    {
        $data = $this->toArray();
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $query = "INSERT INTO procured_organs ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }

    public function update(): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE procured_organs SET 
                organ_external_id = :organ_external_id, 
                status = :status, 
                description = :description, 
                blood_type = :blood_type, 
                clinical_notes = :clinical_notes,
                procurement_timestamp = :procurement_timestamp,
                preservation_timestamp = :preservation_timestamp,
                estimated_warm_ischemia_time_minutes = :estimated_warm_ischemia_time_minutes,
                estimated_cold_ischemia_time_minutes = :estimated_cold_ischemia_time_minutes,
                expiry_timestamp = :expiry_timestamp,
                updated_at = :updated_at
                WHERE id = :id");
            $stmt->bindValue(':procurement_timestamp', $this->procurementTimestamp);
            $stmt->bindValue(':preservation_timestamp', $this->preservationTimestamp);
            $stmt->bindValue(':estimated_warm_ischemia_time_minutes', $this->estimatedWarmIschemiaTimeMinutes);
            $stmt->bindValue(':estimated_cold_ischemia_time_minutes', $this->estimatedColdIschemiaTimeMinutes);
            $stmt->bindValue(':expiry_timestamp', $this->expiryTimestamp);
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
            
            
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
        try {
            $stmt = $this->db->prepare("DELETE FROM procured_organs WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'donation_event_id' => $this->donationEventId,
            'organ_type_id' => $this->organTypeId,
            'current_organization_id' => $this->currentOrganizationId,
            'organ_external_id' => $this->organExternalId,
            'procurement_timestamp' => $this->procurementTimestamp,
            'preservation_timestamp' => $this->preservationTimestamp,
            'estimated_warm_ischemia_time_minutes' => $this->estimatedWarmIschemiaTimeMinutes,
            'estimated_cold_ischemia_time_minutes' => $this->estimatedColdIschemiaTimeMinutes,
            'expiry_timestamp' => $this->expiryTimestamp,
            'status' => $this->status,
            'description' => $this->description,
            'blood_type' => $this->bloodType,
            'clinical_notes' => $this->clinicalNotes,
            'created_by_user_id' => $this->createdByUserId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
    
    public function setDb(PDO $db)
    {
        $this->db = $db;
    }
    



}
