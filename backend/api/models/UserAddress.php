php
<?php

namespace App\Models;

use PDO;
use PDOException;

class UserAddress extends BaseModel
{
    protected ?int $id;
    protected ?int $user_id;
    protected ?string $street_address;
    protected ?string $city;
    protected ?string $state_province;
    protected ?string $postal_code;
    protected ?string $country;

    public function __construct(
        PDO $db,
        ?int $id = null,
        ?int $user_id = null,
        ?string $street_address = null,
        ?string $city = null,
        ?string $state_province = null,
        ?string $postal_code = null,
        ?string $country = null
    ) {
        parent::__construct($db);
        $this->id = $id;
        $this->user_id = $user_id;
        $this->street_address = $street_address;
        $this->city = $city;
        $this->state_province = $state_province;
        $this->postal_code = $postal_code;
        $this->country = $country;
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getStreetAddress(): ?string
    {
        return $this->street_address;
    }

    public function setStreetAddress(?string $street_address): void
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
        return $this->state_province;
    }

    public function setStateProvince(?string $state_province): void
    {
        $this->state_province = $state_province;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): void
    {
        $this->postal_code = $postal_code;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function save(): bool
    {
        try {
            if ($this->id) {
                $stmt = $this->db->prepare("UPDATE user_addresses SET user_id = ?, street_address = ?, city = ?, state_province = ?, postal_code = ?, country = ? WHERE id = ?");
                $stmt->execute([$this->user_id, $this->street_address, $this->city, $this->state_province, $this->postal_code, $this->country, $this->id]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO user_addresses (user_id, street_address, city, state_province, postal_code, country) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$this->user_id, $this->street_address, $this->city, $this->state_province, $this->postal_code, $this->country]);
                $this->id = $this->db->lastInsertId();
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error saving UserAddress: " . $e->getMessage());
            return false;
        }
    }

    public static function getByUserId(PDO $db, int $user_id): ?UserAddress
    {
        try {
            $stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return new UserAddress(
                    $db,
                    $result['id'],
                    $result['user_id'],
                    $result['street_address'],
                    $result['city'],
                    $result['state_province'],
                    $result['postal_code'],
                    $result['country']
                );
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error getting UserAddress by user ID: " . $e->getMessage());
            return null;
        }
    }
}