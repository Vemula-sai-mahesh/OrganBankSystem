<?php
require_once __DIR__ . '/../../models/MedicalMarkerValue.php';
require_once __DIR__ . '/BaseController.php';

class MedicalMarkerValueController extends BaseController
{
    public function getAllMedicalMarkerValues()
    {
        try {
            $medicalMarkerValueModel = new MedicalMarkerValue();
            $medicalMarkerValues = $medicalMarkerValueModel->getAll();

            if ($medicalMarkerValues) {
                $this->sendJsonResponse(200, $medicalMarkerValues);
            } else {
                $this->sendJsonResponse(404, ['message' => 'No medical marker values found']);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(500, ['message' => 'Error fetching medical marker values', 'error' => $e->getMessage()]);
        }
    }
    public function getMedicalMarkerValueById($id)
    {
        try {
            $medicalMarkerValueModel = new MedicalMarkerValue();
            $medicalMarkerValue = $medicalMarkerValueModel->findById($id);

            if ($medicalMarkerValue) {
                $this->sendJsonResponse(200, $medicalMarkerValue);
            } else {
                $this->sendJsonResponse(404, ['message' => 'No medical marker value found']);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(500, ['message' => 'Error fetching medical marker value', 'error' => $e->getMessage()]);
        }
    }
}
?>