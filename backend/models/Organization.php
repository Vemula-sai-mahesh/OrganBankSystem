php
<?php

namespace App\Models;

use PDO;
use PDOException;

class Organization
{
    private PDO $db;
    private string $id;
    private string $name;
    private ?string $type;
    private ?string $streetAddress;
    private ?string $city;
    private ?string $stateProvince;
    private ?string $country;
    private ?string $postalCode;
    private ?string $phoneNumber;
    private ?string $email;
    private ?string $websiteUrl;
    private bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(PDO $db)
    {
        $this->db = $db;
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

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(?string $streetAddress): void
    {
        $this->streetAddress = $streetAddress;
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getEmail(): ?string
    {
        return $this->email;
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

    public function create(): string
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO organizations (id, name, type, street_address, city, state_province, country, postal_code, phone_number, email, website_url, is_active) VALUES (:id, :name, :type, :street_address, :city, :state_province, :country, :postal_code, :phone_number, :email, :website_url, :is_active)");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':name', $this->name);
            $stmt->bindValue(':type', $this->type);
            $stmt->bindValue(':street_address', $this->streetAddress);
            $stmt->bindValue(':city', $this->city);
            $stmt->bindValue(':state_province', $this->stateProvince);
            $stmt->bindValue(':country', $this->country);
            $stmt->bindValue(':postal_code', $this->postalCode);
            $stmt->bindValue(':phone_number', $this->phoneNumber);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':website_url', $this->websiteUrl);
            $stmt->bindValue(':is_active', $this->isActive, PDO::PARAM_BOOL);
            $stmt->execute();

            return $this->id;
        } catch (PDOException $e) {
            throw new PDOException("Error creating organization: " . $e->getMessage());
        }
    }

    public function update(array $data): bool
    {
        try {
            foreach ($data as $key => $value) {
                $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
            $stmt = $this->db->prepare("UPDATE organizations SET name = :name, type = :type, email = :email, website_url = :website_url, phone_number = :phone_number WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':name', $this->name);
            $stmt->bindValue(':type', $this->type);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':website_url', $this->websiteUrl);
            $stmt->bindValue(':phone_number', $this->phoneNumber);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM organizations WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
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