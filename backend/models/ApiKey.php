php
<?php
namespace OrganBank\Models;

use PDO;
use OrganBank\Utils\Database;

class ApiKey extends BaseModel {
    protected $tableName = 'api_keys';

    public function __construct(Database $db) {
        parent::__construct($db);
        $this->tableName = 'api_keys';
    }

    public function findByKeyHash(string $keyHash): ?array
    {
        $query = "SELECT * FROM {$this->tableName} WHERE key_hash = :key_hash";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':key_hash', $keyHash);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    public function create(array $data): string
    {
        $query = "INSERT INTO {$this->tableName} (id, key_hash, organization_id, role_name, created_at, expires_at, last_used_at, is_active) VALUES (:id, :key_hash, :organization_id, :role_name, :created_at, :expires_at, :last_used_at, :is_active)";
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $data['id']);
        $stmt->bindValue(':key_hash', $data['key_hash']);
        $stmt->bindValue(':organization_id', $data['organization_id']);
        $stmt->bindValue(':role_name', $data['role_name']);
        $stmt->bindValue(':created_at', $data['created_at']);
        $stmt->bindValue(':expires_at', $data['expires_at']);
        $stmt->bindValue(':last_used_at', $data['last_used_at']);
        $stmt->bindValue(':is_active', $data['is_active']);

        $stmt->execute();

        return $data['id'];
    }

    public function updateLastUsed(string $id): void {
        $query = "UPDATE {$this->tableName} SET last_used_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
?>