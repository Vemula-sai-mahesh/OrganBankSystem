<?php

namespace OrganBank\Models;

class BaseModel {
    protected $db;
    protected $tableName;

    public function __construct(Database $db) {
        $this->db = $db;
    }   
    
    public function findAll() {
        $sql = "SELECT * FROM {$this->tableName}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }       

    public function findById($id) {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        }

    public function create($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());   
            return false;
        }
    }

    public function update($id, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "UPDATE {$this->tableName} SET $columns WHERE id = :id"; 

        try {
            $stmt = $this->db->prepare($sql);
            $data['id'] = $id;
            $stmt->execute($data);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }


    public function delete($id) {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}       
