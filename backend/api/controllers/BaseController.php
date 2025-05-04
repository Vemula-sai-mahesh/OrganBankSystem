<?php
namespace App\Controllers;

use OrganBankSystem\Backend\Models\UserPlatformIntent;
use OrganBankSystem\Backend\Models\ProcuredOrgan;
use OrganBankSystem\Backend\Models\OrganMedicalMarker;
use OrganBankSystem\Backend\Models\MedicalMarkerValue;
use OrganBankSystem\Backend\Models\User; 
use OrganBankSystem\Backend\Models\OrganType;
use setasign\Fpdi\Fpdi;

require_once __DIR__ . '/../../../vendor/autoload.php';


class BaseController {
    public function generatePdfIntent($intentId)
    {        
        $intent = new UserPlatformIntent();
        $intentData = $intent->findById($intentId);
        if (!$intentData) {
            http_response_code(404);
            echo json_encode(['message' => 'Intent not found']);
            return;
        }

        $user = new User();
        $userData = $user->findById($intentData['user_id']);
        if (!$userData) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            return;
        }
        $organType = new OrganType();
        $organTypeData = $organType->findById($intentData['organ_type_id']);
        if (!$organTypeData) {
            http_response_code(404);
            echo json_encode(['message' => 'Organ Type not found']);
            return;
        }
        // Validate if data is empty
    
          // Load the template PDF
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile(__DIR__ . '/../../../templates/intent_template.pdf'); // Path to your template
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0);

        // Add text to the PDF
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(100, 100);
        $pdf->Write(0, 'Donation Intent');
        $pdf->SetFont('Arial', '', 12);

        $pdf->SetXY(20, 120);        
        $pdf->Write(0, 'User: ' . $userData['first_name'] .' '. $userData['last_name']);

        $pdf->SetXY(20, 130);        
        $pdf->Write(0, 'User Email: ' . $userData['email']);

        $pdf->SetXY(20, 140);        
        $pdf->Write(0, 'Organ Type: ' . $organTypeData['name']);

        $pdf->SetXY(20, 150);
        $pdf->Write(0, 'Declared at: ' . $intentData['declared_at']);

        $pdf->SetXY(20, 160);
        $pdf->Write(0, 'Is Active: ' . ($intentData['is_active'] ? 'Yes' : 'No'));
        
        $pdf->SetXY(20, 170);
        $pdf->Write(0, 'Notes: ' . $intentData['notes']);

        // Output the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="intent_' . $intentId . '.pdf"');
        $pdf->Output();
    }

    public function generatePdfOrgan($organId)
    {
        
        $organ = new ProcuredOrgan();
        $organData = $organ->findFullById($organId);

        if (!$organData) {
            http_response_code(404);
            echo json_encode(['message' => 'Organ not found']);
            return;
        }

        $organType = new OrganType();
        $organTypeData = $organType->findById($organData['organ_type_id']);      
        if (!$organTypeData) {
            http_response_code(404);
            echo json_encode(['message' => 'Organ type not found']);
            return;
        }
        $medicalMarkers = new OrganMedicalMarker();
        $medicalMarkersData = $medicalMarkers->findByOrganId($organId);

        $markersText = '';

        if (!empty($medicalMarkersData)) {
            foreach ($medicalMarkersData as $markerData) {
                $medicalMarkerValue = new MedicalMarkerValue();
                $medicalMarkerValueData = $medicalMarkerValue->findById($markerData['medical_marker_value_id']);
                $markersText .=  $medicalMarkerValueData['value']. ' '; 
            }
        }
    

        if (!$organData) {
            http_response_code(404);
            echo json_encode(['message' => 'Organ not found']);
            return;
        }

        // Load the template PDF
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile(__DIR__ . '/../../../templates/organ_template.pdf'); // Path to your template
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0);

        // Add text to the PDF
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(100, 100);
        $pdf->Write(0, 'Procured Organ');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(20, 120);
        $pdf->Write(0, 'Organ ID: ' . $organData['id']);
        $pdf->SetXY(20, 130);
        $pdf->Write(0, 'Donation Event ID: ' . $organData['donation_event_id']);        
        $pdf->SetXY(20, 140);
        $pdf->Write(0, 'Organ Type: ' . $organTypeData['name']);
        $pdf->SetXY(20, 150);
        $pdf->Write(0, 'Current Organization ID: ' . $organData['current_organization_id']);        
        $pdf->SetXY(20, 160);
        $pdf->Write(0, 'Procurement Timestamp: ' . $organData['procurement_timestamp']);
        $pdf->SetXY(20, 170);
        $pdf->Write(0, 'Status: ' . $organData['status']);
        $pdf->SetXY(20, 180);
        $pdf->Write(0, 'Blood Type: ' . $organData['blood_type']);
        $pdf->SetXY(20, 190);        
        $pdf->Write(0, 'Markers: ' . $markersText);
        $pdf->SetXY(20, 200);
        $pdf->MultiCell(0, 10, 'Clinical Notes: ' . $organData['clinical_notes']);

        

        // Output the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="organ_' . $organId . '.pdf"');
        $pdf->Output();
    }
}