<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Organization.php';
require_once __DIR__ . '/../models/DonationEvent.php';
require_once __DIR__ . '/../models/ProcuredOrgan.php';
require_once __DIR__ . '/../models/Transplant.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/BaseController.php'; // Assuming BaseController contains sendJsonResponse and sendErrorResponse

class AdminController extends BaseController {

    public function __construct($db) {
        parent::__construct($db);
    }

    public function getAnalytics() {
        try {
            $userModel = new User($this->db);
            $organizationModel = new Organization($this->db);
            $donationEventModel = new DonationEvent($this->db);
            $procuredOrganModel = new ProcuredOrgan($this->db);
            $transplantModel = new Transplant($this->db);

            list($totalUsers, $totalOrganizations, $totalDonationEvents, $totalProcuredOrgans, $totalTransplants) = [$userModel->countAll(), $organizationModel->countAll(), $donationEventModel->countAll(), $procuredOrganModel->countAll(), $transplantModel->countAll()];

            $data = [
                'total_users' => $totalUsers,
                'total_organizations' => $totalOrganizations,
                'total_donation_events' => $totalDonationEvents,
                'total_procured_organs' => $totalProcuredOrgans,
                'total_transplants' => $totalTransplants,
            ];

            $this->sendJsonResponse($data);
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Error fetching analytics: " . $e->getMessage());
        }
    }

    public function getAuditLogs() {
        try {
            $auditLogModel = new AuditLog($this->db);
            $auditLogs = $auditLogModel->getAll();        
            
            if (empty($auditLogs)) {
                $this->sendJsonResponse([], 200, "No audit logs found");
                return;
            }

            $this->sendJsonResponse($auditLogs);
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Error fetching audit logs: " . $e->getMessage());
        }
    }
}