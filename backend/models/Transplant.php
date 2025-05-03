php
<?php

class Transplant extends BaseModel {
    protected $tableName = 'transplants';

    public function create($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->get($this->db->lastInsertId());
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->tableName}";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function get($id) {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $setClause = "";
        foreach($data as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ", ");
        $sql = "UPDATE {$this->tableName} SET $setClause WHERE id = :id";
        $data['id'] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
             return $this->get($id);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }



}