<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/OrganType.php';

class OrganTypeController extends BaseController {

    public function getOrganTypes() {
        try {
            // Get all organ types
            $organTypeModel = new OrganType();
            $organTypes = $organTypeModel->all();

            // Check if organ types were found
            if (empty($organTypes)) {
                $this->sendJsonResponse([
                    "message" => "No organ types found."
                ], 404); // Not Found
                return;
            }

            // Return organ types as JSON response
            $this->sendJsonResponse($organTypes, 200); // OK

        } catch (PDOException $e) {
            // Handle database errors
            $this->sendJsonResponse([
                "message" => "Database error: " . $e->getMessage()
            ], 500); // Internal Server Error

        } catch (Exception $e) {
            // Handle other errors
            $this->sendJsonResponse([
                "message" => "An error occurred: " . $e->getMessage()
            ], 500); // Internal Server Error
        }
    }
    
}

?>