php
<?php

require_once __DIR__ . '/../../models/MedicalMarkerType.php';
require_once __DIR__ . '/BaseController.php';

class MedicalMarkerTypeController extends BaseController
{
    public function getAllMedicalMarkerTypes()
    {
        try {
            $medicalMarkerTypes = MedicalMarkerType::findAll();

            if ($medicalMarkerTypes === false) {
                $this->sendErrorResponse(500, "Error retrieving medical marker types from database.");
            }

            $this->sendJsonResponse($medicalMarkerTypes);
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error: " . $e->getMessage());
        }
    }
}