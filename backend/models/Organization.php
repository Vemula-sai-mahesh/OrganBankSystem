<?php
namespace App\Models;

use App\Models\BaseModel;
use PDO;
use PDOException;

class Organization extends BaseModel
{
    protected string $table = 'organizations';
    private PDO $db;
    
    private string $id;
    private string $name;
    private ?string $type;
    private ?string $street_address;
    private ?string $city;
    private ?string $stateProvince;
    private ?string $postal_code;
    private ?string $phoneNumber;
    private ?string $email;
    private ?string $websiteUrl;
    private ?string $country;
    private bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? $this->getDb();
        $this->isActive = true;
    }

    // Getters and Setters...

    // Skipping for brevity (same as your version, just fix variable names accordingly)

    public function create(): bool
    {
        try {
            $query = "INSERT INTO organizations 
                (id, name, type, street_address, city, state_province, country, postal_code, phone_number, email, website_url, is_active) 
                VALUES (:id, :name, :type, :street_address, :city, :state_province, :country, :postal_code, :phone_number, :email, :website_url, :is_active)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $this->id,
                ':name' => $this->name,
                ':type' => $this->type,
                ':street_address' => $this->street_address,
                ':city' => $this->city,
                ':state_province' => $this->stateProvince,
                ':country' => $this->country,
                ':postal_code' => $this->postal_code,
                ':phone_number' => $this->phoneNumber,
                ':email' => $this->email,
                ':website_url' => $this->websiteUrl,
                ':is_active' => $this->isActive,
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error creating organization: " . $e->getMessage());
            return false;
        }
    }

    public function update(): bool
    {
        try {
            $query = "UPDATE organizations SET 
                name = :name, type = :type, street_address = :street_address, city = :city, 
                state_province = :state_province, postal_code = :postal_code, 
                phone_number = :phone_number, email = :email, 
                website_url = :website_url, is_active = :is_active 
                WHERE id = :id";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':name' => $this->name,
                ':type' => $this->type,
                ':street_address' => $this->street_address,
                ':city' => $this->city,
                ':state_province' => $this->stateProvince,
                ':postal_code' => $this->postal_code,
                ':phone_number' => $this->phoneNumber,
                ':email' => $this->email,
                ':website_url' => $this->websiteUrl,
                ':is_active' => $this->isActive,
                ':id' => $this->id,
            ]);
        } catch (PDOException $e) {
            error_log("Error updating organization: " . $e->getMessage());
            return false;
        }
    }

    public static function getById(PDO $db, string $id): ?Organization
    {
        try {
            $stmt = $db->prepare("SELECT * FROM organizations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $org = new Organization($db);
                foreach ($data as $key => $value) {
                    $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
                    if (method_exists($org, $setter)) {
                        $org->$setter($value);
                    }
                }
                return $org;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error getting organization by ID: " . $e->getMessage());
            return null;
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM organizations WHERE id = :id");
            return $stmt->execute([':id' => $this->id]);
        } catch (PDOException $e) {
            error_log("Error deleting organization: " . $e->getMessage());
            return false;
        }
    }

    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = null): array
    {
        try {
            $where = '';
            $params = [];

            if ($search) {
                $where .= "WHERE id LIKE :search OR name LIKE :search OR type LIKE :search OR email LIKE :search OR website_url LIKE :search OR phone_number LIKE :search";
                $params[':search'] = "%$search%";
            }

            if ($filter) {
                $filters = [];
                foreach ($filter as $field => $value) {
                    if (in_array($field, ['id', 'name', 'type', 'email', 'website_url', 'phone_number'])) {
                        $filters[] = "$field = :$field";
                        $params[":$field"] = $value;
                    }
                }
                if (!empty($filters)) {
                    $where .= ($where ? ' AND ' : 'WHERE ') . implode(' AND ', $filters);
                }
            }

            $orderBy = '';
            if ($sort) {
                list($field, $dir) = explode('&', $sort);
                if (in_array($field, ['id', 'name', 'type', 'email', 'website_url', 'phone_number']) &&
                    in_array(strtolower($dir), ['asc', 'desc'])) {
                    $orderBy = "ORDER BY $field $dir";
                }
            }

            $limit = '';
            if ($page !== null && $per_page !== null) {
                $offset = ($page - 1) * $per_page;
                $limit = "LIMIT $per_page OFFSET $offset";
            }

            $sql = "SELECT * FROM organizations $where $orderBy $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Count
            $countStmt = $db->prepare("SELECT COUNT(*) FROM organizations $where");
            $countStmt->execute($params);
            $count = $countStmt->fetchColumn();

            return [
                'organizations' => $results,
                'total_count' => $count,
            ];
        } catch (PDOException $e) {
            error_log("Error getting organizations: " . $e->getMessage());
            return [];
        }
    }
}
