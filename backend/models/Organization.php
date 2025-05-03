<?php

namespace App\Models;
use App\Models\BaseModel;
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
    private bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;
    private $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? $this->getDb();
        $this->isActive = true;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getStreet_address(): ?string
    {
        return $this->street_address;
    }

    public function setStreet_address(?string $street_address): void
    {
        $this->street_address = $street_address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getStateProvince(): ?string
    {
        return $this->stateProvince;
    }

    public function setStateProvince(?string $stateProvince): void
    {
        $this->stateProvince = $stateProvince;
    }

    public function getPostal_code(): ?string
    {
        return $this->postal_code;
    }

    public function setPostal_code(?string $postal_code): void
    {
        $this->postal_code = $postal_code;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
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

    public function create(): bool
    {
        try {
            $query = "INSERT INTO organizations (id, name, type, street_address, city, state_province, country, postal_code, phone_number, email, website_url, is_active) 
                      VALUES (:id, :name, :type, :street_address, :city, :state_province, :country, :postal_code, :phone_number, :email, :website_url, :is_active)";            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $this->id,
                ':name' => $this->name,
                ':type' => $this->type,
                ':street_address' => $this->streetAddress,
                ':city' => $this->city,
                ':state_province' => $this->state_province,
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

    public static function getById(PDO $db, string $id): ?Organization
    {
        try {
            $stmt = $db->prepare("SELECT * FROM organizations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $organizationData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($organizationData) {
                $organization = new Organization();
                foreach ($organizationData as $key => $value) {
                    $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
                    if (method_exists($organization, $setter)) {
                        $organization->$setter($value);
                    }
                }
                return $organization;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error getting organization by ID: " . $e->getMessage());
            return null;
        }
    }

    public function update(): bool
    {
        try {
            $query = "UPDATE organizations SET name = :name, type = :type, street_address = :street_address, city = :city, state_province = :state_province,  postal_code = :postal_code, phone_number = :phone_number, email = :email, website_url = :website_url, is_active = :is_active WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':name' => $this->name,
                ':type' => $this->type,
                ':street_address' => $this->streetAddress,
                ':city' => $this->city,
                ':state_province' => $this->state_province,
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
            $whereClause = '';
            $limitClause = '';
            $offset = null;
            $totalCount = 0;

            $orderByClause = '';
            $params = [];
    
            if ($search) {
                $whereClause .= " WHERE id LIKE :search OR name LIKE :search OR type LIKE :search OR email LIKE :search OR website_url LIKE :search OR phone_number LIKE :search";
                $params[':search'] = "%$search%";
            }
    
            if ($filter) {
                $filterConditions = [];
                foreach ($filter as $field => $value) {
                    if (in_array($field, ['id', 'name', 'type', 'email', 'website_url', 'phone'])) {
                        $filterConditions[] = "$field = :$field";
                        $params[":$field"] = $value;
                    }
                }
                if (!empty($filterConditions)) {
                    $whereClause .= empty($whereClause) ? " WHERE " : " AND ";
                    $whereClause .= implode(" AND ", $filterConditions);
                }
            }
            
            if ($sort) {
                list($field, $direction) = explode('&', $sort);
                if (in_array($field, ['id', 'name', 'type', 'email', 'website_url', 'phone']) && in_array($direction, ['asc', 'desc'])) {
                    $orderByClause = " ORDER BY $field $direction";
                }
            }

            if ($page && $per_page) {
                $per_page = $per_page ?? 10;
                $page = $page < 1 ? 1 : $page;
                $offset = ($page - 1) * $per_page;
                $limitClause = " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $per_page;
                $params[':offset'] = $offset;
            }

            $stmt = $db->prepare("SELECT * FROM organizations $whereClause $orderByClause $limitClause");

            $stmt->execute($params);
            $organizations = $stmt->fetchAll(PDO::FETCH_ASSOC);

             // Count total organizations matching the filter
             $countStmt = $db->prepare("SELECT COUNT(*) FROM organizations $whereClause");
             $countStmt->execute($params);
             $totalCount = $countStmt->fetchColumn();

             return [
                'organizations' => $organizations,
                'total_count' => $totalCount,
            ];
        } catch (PDOException $e) {
            error_log("Error getting all organizations: " . $e->getMessage());
            return [];
        }
    }
}