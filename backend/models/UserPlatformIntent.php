php
<?php
require_once 'BaseModel.php';

/**
 * Represents a user's intent to donate organs on the platform.
 */
class UserPlatformIntent extends BaseModel {
    protected $tableName = 'user_platform_intents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'organ_type_id',
        'declared_at',
        'is_active',
        'notes',
        'created_at',
        'updated_at'
    ];

    /**
     * Get a user's platform intent by user ID.
     *
     * @param string $userId The user ID.
     * @return array|null The user's intent or null if not found.
     */
    public function findByUserId(string $userId): ?array
    {
        $query = "SELECT * FROM {$this->tableName} WHERE user_id = :user_id";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':user_id', $userId);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Find platform intent by ID.
     *
     * @param string $intentId
     * @return array|null
     */
    public function findById(string $intentId): ?array
    {
        return $this->find($intentId);
    }

}
?>