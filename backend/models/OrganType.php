<?php

require_once 'BaseModel.php'; // Make sure this path is correct

class UserRole extends BaseModel
{
    protected $tableName = 'user_roles';
    protected $fillable = ['user_id', 'organization_id', 'role_name', 'assigned_at'];

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    public function findByUserAndOrganization($userId, $organizationId)
    {
        $query = "SELECT * FROM {$this->tableName} WHERE user_id = :user_id AND organization_id = :organization_id";
        $params = [':user_id' => $userId, ':organization_id' => $organizationId];

        return $this->fetch($query, $params);
    }

    public function findByUser($userId)
    {
        $query = "SELECT * FROM {$this->tableName} WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        return $this->fetchAll($query, $params);
    }

    public function findByOrganization($organizationId)
    {
        $query = "SELECT * FROM {$this->tableName} WHERE organization_id = :organization_id";
        $params = [':organization_id' => $organizationId];

        return $this->fetchAll($query, $params);
    }

    public function getRoleName()
    {
        return $this->role_name;
    }
}