<?php
require_once 'BaseModel.php';

/**
 * Represents a medical marker type in the OrganBank system.
 *
 * This class handles interactions with the 'medical_marker_types' table in the database.
 */
class MedicalMarkerType extends BaseModel {
    /**
     * @var string The name of the database table associated with this model.
     */
    protected $tableName = 'medical_marker_types';

    /**
     * @var array The list of attributes for this model.
     */
    protected $attributes = [
        'id',
        'name',
        'data_type',
        'description',
    ];

    /**
     * Constructor for MedicalMarkerType.
     *
     * @param Database|null $db The database connection instance.
     */
    public function __construct($db = null) {
        parent::__construct($db);
    }

    /**
     * Finds a medical marker type by its ID.
     *
     * @param string $id The ID of the medical marker type.
     * @return array|null The medical marker type data if found, null otherwise.
     */
    public function findById($id) {
        return $this->find($id);
    }
}