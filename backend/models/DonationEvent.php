<?php

namespace App\Models;

use App\Utils\Database;
use PDO;
use PDOException;
use Ramsey\Uuid\Uuid;

class DonationEvent extends BaseModel
{
    protected string $tableName = 'donation_events';
    protected array $fields = [
        'id',
        'source_organization_id',
        'donation_type',
        'donor_external_id',
        'event_start_timestamp',
        'event_end_timestamp',
        'status',
        'cause_of_death',
        'clinical_summary',
        'notes',
        'created_by_user_id',
        'created_at',
        'updated_at'
    ];

    
    protected string $primaryKey = 'id';

    public function create(array $data): ?string
    {
        $db = Database::getInstance()->getConnection();
        try {
            $data['id'] = Uuid::uuid4()->toString();
            $fields = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $query = "INSERT INTO {$this->tableName} ($fields) VALUES ($placeholders)";
            $stmt = $db->prepare($query);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return $data['id'];
        } catch (PDOException $e) {
            error_log("Error creating donation event: " . $e->getMessage());
            return null;
        }
    }
    public function get(string $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $donationEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    
            return $donationEvent ?: null;
        } catch (PDOException $e) {
            error_log("Error getting donation event: " . $e->getMessage());
            return null;
        }
    }

    public function getAll(): ?array
    {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "SELECT * FROM {$this->tableName}";
            $stmt = $db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error getting all donation events: " . $e->getMessage());
            return null;
        }
    }
    public function update(string $id, array $data): bool
    {
        $db = Database::getInstance()->getConnection();
        try {
            $setClauses = [];
            foreach ($data as $key => $value) {
                $setClauses[] = "$key = :$key";
            }
            $setClauseString = implode(", ", $setClauses);

            $query = "UPDATE {$this->tableName} SET $setClauseString WHERE id = :id";
            $stmt = $db->prepare($query);

            $stmt->bindParam(':id', $id);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating donation event: " . $e->getMessage());
            return false;
        }
    }
    public function delete(string $id): bool
    {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "DELETE FROM {$this->tableName} WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting donation event: " . $e->getMessage());
            return false;
        }
    }

    public function getBySourceOrganization(string $sourceOrganizationId): ?array
    {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE source_organization_id = :source_organization_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':source_organization_id', $sourceOrganizationId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error getting donation events by source organization: " . $e->getMessage());
            return null;
        }
    }
    public function getByCreatedByUserId(string $userId): ?array
    {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE created_by_user_id = :created_by_user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':created_by_user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error getting donation events by user: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateStatus(string $id, string $newStatus): bool
    {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "UPDATE {$this->tableName} SET status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $newStatus);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating donation event status: " . $e->getMessage());
            return false;
        }
    }

    public function getActiveDonationEventsByOrganization(string $organizationId): ?array {
        $db = Database::getInstance()->getConnection();
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE source_organization_id = :source_organization_id AND status != 'Completed'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':source_organization_id', $organizationId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error getting active donation events by organization: " . $e->getMessage());
            return null;
        }
    }



}