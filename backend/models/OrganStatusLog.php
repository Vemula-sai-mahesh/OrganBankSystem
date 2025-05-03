php
<?php

class OrganStatusLog extends BaseModel {
    protected $tableName = 'organ_status_log';    
    
    public function __construct($db) {
        parent::__construct($db);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->tableName . " 
                (id, procured_organ_id, old_status, new_status, status_notes, changed_by_user_id) 
                VALUES (:id, :procured_organ_id, :old_status, :new_status, :status_notes, :changed_by_user_id)";
        
        $stmt = $this->conn->prepare($query);

        $this->sanitize($data);

        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":procured_organ_id", $data['procured_organ_id']);
        $stmt->bindParam(":old_status", $data['old_status']);
        $stmt->bindParam(":new_status", $data['new_status']);
        $stmt->bindParam(":status_notes", $data['status_notes']);
        $stmt->bindParam(":changed_by_user_id", $data['changed_by_user_id']);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function getByOrganId($organId) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE procured_organ_id = :organId ORDER BY timestamp DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":organId", $organId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}