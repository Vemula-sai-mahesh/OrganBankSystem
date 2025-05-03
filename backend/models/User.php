<?php
namespace App\Models;

use PDO;
use PDOException;

class User
{
    private PDO $db;
    private string $id;
    private string $email;
    private string $passwordHash;
    private string $firstName;
    private string $lastName;
    private ?string $phoneNumber;
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

    public function __construct(PDO $db)
    {
        $this->db = $db;
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

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
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
            $stmt = $this->db->prepare("INSERT INTO users (id, email, password_hash, first_name, last_name, phone_number, street_address, city, state_province, country, postal_code, date_of_birth, gender, preferred_language, email_verified) VALUES (:id, :email, :password_hash, :first_name, :last_name, :phone_number, :street_address, :city, :state_province, :country, :postal_code, :date_of_birth, :gender, :preferred_language, :email_verified)");
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':email', $this->email);
            // Hash the password before saving
            $hashedPassword = password_hash($this->passwordHash, PASSWORD_DEFAULT);
            $stmt->bindValue(':password_hash', $hashedPassword);
            $stmt->bindValue(':first_name', $this->firstName);
            $stmt->bindValue(':last_name', $this->lastName);
            $stmt->bindValue(':phone_number', $this->phoneNumber);

            $stmt->bindValue(':street_address', $this->streetAddress);
            $stmt->bindValue(':city', $this->city);
            $stmt->bindValue(':state_province', $this->stateProvince);
            $stmt->bindValue(':country', $this->country);
            $stmt->bindValue(':postal_code', $this->postalCode);
            $stmt->bindValue(':date_of_birth', $this->dateOfBirth);
            $stmt->bindValue(':gender', $this->gender);
            $stmt->bindValue(':preferred_language', $this->preferredLanguage);
            $stmt->bindValue(':email_verified', $this->emailVerified, PDO::PARAM_BOOL);
            $stmt->execute();

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
            $sql = "UPDATE users SET " . implode(", ", $setClauses) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindValue(':id', $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getAll(PDO $db, ?string $search = null, ?array $filter = null, ?string $sort = null, ?int $page = null, ?int $per_page = 10): array
    {
        try {
            $sql = "SELECT u.* FROM users u";
            $sqlCount = "SELECT COUNT(*) FROM users u";

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

    public function setPassword(string $password): void
    {
        $this->passwordHash = $password;
    }

    // Added static method to find user by email
    public static function getByEmail(PDO $db, string $email): ?User
    {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindValue(':email', $email);
            $stmt->execute();

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                // Create a new User instance and populate it
                $user = new self($db); // Use self($db) to call constructor
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
                $user->profilePictureUrl = $userData['profile_picture_url'];
                // Add is_admin if it exists in your table
                // if (isset($userData['is_admin'])) {
                //     $user->isAdmin = (bool)$userData['is_admin'];
                // }

                return $user;
            }

            return null; // Return null if user not found

        } catch (PDOException $e) {
            // Optionally log the error here before re-throwing or returning null
            // error_log("Error in getByEmail: " . $e->getMessage());
            return null; // Or rethrow: throw new PDOException("Error fetching user by email: " . $e->getMessage());
        }
    }

    public function getPassword(): string // Renamed from getPasswordHash for consistency with AuthController
    {
        return $this->passwordHash;
    }
}