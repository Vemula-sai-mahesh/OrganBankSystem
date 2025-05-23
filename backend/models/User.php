<?php
namespace App\Models;

use App\Utils\Database;
use App\Models\BaseModel;
use PDO;
use PDOException;

class User extends BaseModel
{
    private string $id;
    private string $email;
    private string $passwordHash;
    private string $firstName;
    private string $lastName;
    private ?string $phoneNumber;
    private string $password;
    private ?string $streetAddress;
    private ?string $city;
    private ?string $stateProvince;
    private ?string $country;
    private ?string $postalCode;
    private ?string $dateOfBirth;
    private ?string $gender;
    private ?string $preferredLanguage;
    private bool $emailVerified;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $lastLoginAt;
    private ?string $profilePictureUrl;

    public function __construct()
    {
        parent::__construct('users');
        $this->emailVerified = false;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
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

    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?string $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getPreferredLanguage(): ?string
    {
        return $this->preferredLanguage;
    }

    public function setPreferredLanguage(?string $preferredLanguage): void
    {
        $this->preferredLanguage = $preferredLanguage;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): void
    {
        $this->emailVerified = $emailVerified;
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

    public function getLastLoginAt(): ?string
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?string $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    public function getProfilePictureUrl(): ?string
    {
        return $this->profilePictureUrl;
    }

    public function setProfilePictureUrl(?string $profilePictureUrl): void
    {
        $this->profilePictureUrl = $profilePictureUrl;
    }

    public function create(): string
    {
        try {
            $data = [
                'id' => $this->id,
                'email' => $this->email,
                'password_hash' => $this->hashPassword($this->password),
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'phone_number' => $this->phoneNumber,
                'street_address' => $this->streetAddress,
              'city' => $this->city, 
              'state_province' => $this->stateProvince, 
              'country' => $this->country,
              'postal_code' => $this->postalCode,
              'email_verified' => $this->emailVerified,
            ];
            $this->db->insert($this->tableName,$data);


            return $this->id;
        } catch (PDOException $e) {
            throw new PDOException("Error creating user: " . $e->getMessage());
        }
    }

    public function update(array $data): bool
    {
        try {
            $setClauses = [];
            $params = [];
            foreach ($data as $key => $value) {
                if ($key === 'password') {
                  $hashedPassword = password_hash($value, PASSWORD_DEFAULT);
                  $setClauses[] = "password_hash = :password_hash";
                  $params[':password_hash'] = $hashedPassword;
                } elseif (in_array($key, ['first_name', 'last_name', 'email'])) {
                    $setClauses[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }

            if (empty($setClauses)) {
              return true;
            }

            $params[':id'] = $this->id;
            $sql = "UPDATE {$this->tableName} SET " . implode(", ", $setClauses) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = 10): array
    {
        try {
            $sql = "SELECT u.* FROM users";
            $sqlCount = "SELECT COUNT(*) FROM users";

            $whereClauses = [];
            $params = [];

             // Search
            if ($search !== null) {
                $whereClauses[] = "(u.id LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
                $params[':search'] = "%" . $search . "%";
            }

           // Filter
           if ($filter !== null) {
              foreach ($filter as $field => $value) {
                  if (in_array($field, ['id', 'first_name', 'last_name', 'email'])) {
                      $whereClauses[] = "u.{$field} = :{$field}";
                      $params[":{$field}"] = $value;
                  }
                }
            }

            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(" AND ", $whereClauses) ;
                $sqlCount .= " WHERE " . implode(" AND ", $whereClauses);
                
            }

            // Sort
            if ($sort !== null && preg_match('/^(id|first_name|last_name|email)&(asc|desc)$/i', $sort, $matches)) {
                $sql .= " ORDER BY {$matches[1]} {$matches[2]}";
            }
            
            // Pagination
           if ($page !== null && $per_page !== null) {
                $page = max(1, $page);
                $offset = ($page - 1) * $per_page;
                $sql .= " LIMIT :per_page OFFSET :offset";
                $params[':per_page'] = $per_page;
                $params[':offset'] = $offset;
           }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $stmtCount = $db->prepare($sqlCount);
            $stmtCount->execute($params);
            $totalCount = $stmtCount->fetchColumn();

            return ['users' => $users, 'total_count' => $totalCount];
        } catch (PDOException $e) {
            return [];
        }
    }

    // Added static method to find user by email
    public static function getByEmail(Database $db, string $email): ?User
    {
        try {
            $stmt = $db->getConnection()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                // Create a new User instance and populate it
                $user = new self(); // Use self($db) to call constructor
                $user->id = $userData['id'];
                 $user->email = $userData['email'];
                $user->passwordHash = $userData['password_hash'];
                $user->firstName = $userData['first_name'];
                $user->lastName = $userData['last_name'];
                $user->phoneNumber = $userData['phone_number'];
                $user->streetAddress = $userData['street_address'];
                $user->city = $userData['city'];
                $user->stateProvince = $userData['state_province'];
                $user->country = $userData['country'];
                $user->postalCode = $userData['postal_code'];
                $user->dateOfBirth = $userData['date_of_birth'];
                $user->gender = $userData['gender'];
                $user->preferredLanguage = $userData['preferred_language'];
                $user->emailVerified = (bool)$userData['email_verified'];
                $user->createdAt = $userData['created_at'];
                $user->updatedAt = $userData['updated_at'];
                $user->lastLoginAt = $userData['last_login_at'];

                return $user;
            }

            return null; // Return null if user not found

        } catch (PDOException $e) {
            // Optionally log the error here before re-throwing or returning null
            // error_log("Error in getByEmail: " . $e->getMessage());
            return null; // Or rethrow: throw new PDOException("Error fetching user by email: " . $e->getMessage());
        }
    }

    // Added static method to find user by ID
    public static function getById(Database $db, string $id): ?User
    {
        try {
            $stmt = $db->getConnection()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($userData) {
                return self::createUserFromData($userData);
            }
    
            return null; 
    
        } catch (PDOException $e) {
            return null; 
        }
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPasswordHash(): string // Renamed from getPasswordHash for consistency with AuthController
    {
        return $this->passwordHash;
    }
}