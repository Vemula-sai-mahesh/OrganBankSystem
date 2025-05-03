php
<?php
require_once __DIR__ . '/../models/Transplant.php';
require_once __DIR__ . '/BaseController.php';

class TransplantController extends BaseController
{
    public function create($data)
    {
        try {
            // Basic validation
            if (!isset($data['procured_organ_id']) || empty($data['procured_organ_id'])) {
                return $this->respondWithError("Procured organ ID is required", 400);
            }
            if (!isset($data['transplant_center_id']) || empty($data['transplant_center_id'])) {
                return $this->respondWithError("Transplant center ID is required", 400);
            }
            if (!isset($data['recorded_by_user_id']) || empty($data['recorded_by_user_id'])) {
                return $this->respondWithError("Recorded by user ID is required", 400);
            }

            $transplant = new Transplant();
            $transplant->procured_organ_id = $data['procured_organ_id'];
            $transplant->transplant_center_id = $data['transplant_center_id'];
            $transplant->recipient_external_id = $data['recipient_external_id'] ?? null;
            $transplant->transplant_timestamp = $data['transplant_timestamp'] ?? null;
            $transplant->outcome = $data['outcome'] ?? null;
            $transplant->notes = $data['notes'] ?? null;
            $transplant->recorded_by_user_id = $data['recorded_by_user_id'];

            if ($transplant->save()) {
                return $this->respondWithSuccess($transplant, 201);
            } else {
                return $this->respondWithError("Failed to create transplant", 500);
            }
        } catch (Exception $e) {
            error_log("Transplant creation error: " . $e->getMessage());
            return $this->respondWithError("Failed to create transplant", 500);
        }
    }
    
    public function get($id)
    {
        $transplant = Transplant::find($id);
        if ($transplant) {
            return $this->respondWithSuccess($transplant);
        } else {
            return $this->respondWithError("Transplant not found", 404);
        }
    }

    public function getAll()
    {
        $transplants = Transplant::all();
        return $this->respondWithSuccess($transplants);
    }

    public function update($id, $data)
    {
        try {
            $transplant = Transplant::find($id);
            if (!$transplant) {
                return $this->respondWithError("Transplant not found", 404);
            }

             // Basic validation
            if (isset($data['procured_organ_id']) && empty($data['procured_organ_id'])) {
                return $this->respondWithError("Procured organ ID is required", 400);
            }
            if (isset($data['transplant_center_id']) && empty($data['transplant_center_id'])) {
                return $this->respondWithError("Transplant center ID is required", 400);
            }
            if (isset($data['recorded_by_user_id']) && empty($data['recorded_by_user_id'])) {
                return $this->respondWithError("Recorded by user ID is required", 400);
            }

            $transplant->procured_organ_id = $data['procured_organ_id'] ?? $transplant->procured_organ_id;
            $transplant->transplant_center_id = $data['transplant_center_id'] ?? $transplant->transplant_center_id;
            $transplant->recipient_external_id = $data['recipient_external_id'] ?? $transplant->recipient_external_id;
            $transplant->transplant_timestamp = $data['transplant_timestamp'] ?? $transplant->transplant_timestamp;
            $transplant->outcome = $data['outcome'] ?? $transplant->outcome;
            $transplant->notes = $data['notes'] ?? $transplant->notes;
            $transplant->recorded_by_user_id = $data['recorded_by_user_id'] ?? $transplant->recorded_by_user_id;

            if ($transplant->save()) {
                return $this->respondWithSuccess($transplant);
            } else {
                return $this->respondWithError("Failed to update transplant", 500);
            }
        } catch (Exception $e) {
             error_log("Transplant update error: " . $e->getMessage());
             return $this->respondWithError("Failed to update transplant", 500);
        }
    }

    public function delete($id)
    {
        $transplant = Transplant::find($id);
        if (!$transplant) {
            return $this->respondWithError("Transplant not found", 404);
        }

        if ($transplant->delete()) {
            return $this->respondWithSuccess(null, 204);
        }
        else {
            return $this->respondWithError("Failed to delete transplant", 500);
        }
    } catch (Exception $e) {
        error_log("Transplant delete error: " . $e->getMessage());
        return $this->respondWithError("Failed to delete transplant", 500);
    }
}