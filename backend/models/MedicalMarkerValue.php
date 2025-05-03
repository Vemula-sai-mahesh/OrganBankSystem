php
<?php
require_once 'BaseModel.php';

class MedicalMarkerValue extends BaseModel {

    protected $tableName = 'medical_marker_values';

    public function __construct($db) {
        parent::__construct($db);
    }
    public function create($data) {
        $data['id'] = $this->generateUuid();
        return parent::create($data);
    }
    public function update($id, $data) {
        return parent::update($id, $data);
    }

    public function delete($id) {
        return parent::delete($id);
    }

    public function findById($id) {
        return parent::findById($id);
    }

    public function findAll() {
        return parent::findAll();
    }
    public function findByMarkerTypeId($markerTypeId) {
        $query = "SELECT * FROM {$this->tableName} WHERE marker_type_id = :marker_type_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':marker_type_id', $markerTypeId);
        return $this->executeQuery($stmt);
    }
}
?>