<?php
namespace App\Controllers;

use App\Models\DonationEvent;
use App\Models\ProcuredOrgan;
use App\Models\UserRole;
use PDO;
use Exception;

class DonationEventController
    extends BaseController {
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index($userId, $orgId, $userRoles): void
    {
        try {
            $filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? null;
            $sort = $_GET['sort'] ?? 'event_start_timestamp';
            $direction = $_GET['direction'] ?? 'desc';
            $search = $_GET['search'] ?? '';
            $page = $_GET['page'] ?? 1;
            $per_page = $_GET['per_page'] ?? 10;
            $donationEvents = DonationEvent::getAll($this->db, $search, $filter, $sort, $direction, $page, $per_page);
            $this->sendResponse($donationEvents);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function show($userId, $orgId, $userRoles, $id): void
    {
        try {
            $donationEvent = DonationEvent::getById($this->db, $id);
            // Check if user has permission to access this event
            if (!in_array('system_administrator', $userRoles) && $donationEvent->getSourceOrganizationId() != $orgId ) {
                $this->sendError('You do not have permission to access this donation event', 403);
                return;
            }

            if (!$donationEvent) {
                $this->sendError('Donation Event not found', 404);
                return;
            }
            $this->sendResponse($donationEvent);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function store($userId, $orgId, $userRoles): void
    {
        if (!in_array('system_administrator', $userRoles) && !in_array('opo_coordinator', $userRoles)) {
            $this->sendError('You do not have permission to create a donation event', 403);
            return;
        }

        $data = $this->getRequestBody();

        $requiredFields = ['source_organization_id'];
        $missingFields = array_diff($requiredFields, array_keys($data));
        if (!empty($missingFields)) {
            $this->sendError('Missing required fields: ' . implode(', ', $missingFields), 400);
            return;
        }


        // Validate date formats
        if (isset($data['event_start_timestamp']) && !$this->isValidDateFormat($data['event_start_timestamp'])) {
            $this->sendError('Invalid event_start_timestamp format. Expected YYYY-MM-DD HH:MM:SS', 400);
            return;
        }
        if (isset($data['event_end_timestamp']) && !$this->isValidDateFormat($data['event_end_timestamp'])) {
            $this->sendError('Invalid event_end_timestamp format. Expected YYYY-MM-DD HH:MM:SS', 400);
            return;
        }

        // Check organization permissions
        if(!in_array('system_administrator', $userRoles) && $data['source_organization_id'] != $orgId){
            $this->sendError('You are not allowed to create an event for this organization.', 403);
            return;
        }
        
        $data['created_by_user_id'] = $userId;

        $donationEvent = new DonationEvent($this->db);
        $donationEvent->setId(uniqid());
        $donationEvent->setSourceOrganizationId($data['source_organization_id']);
        $donationEvent->setDonationType($data['donation_type'] ?? '');
        $donationEvent->setDonorExternalId($data['donor_external_id'] ?? '');
        $donationEvent->setEventStartTimestamp($data['event_start_timestamp'] ?? '');
        $donationEvent->setEventEndTimestamp($data['event_end_timestamp'] ?? '');
        $donationEvent->setStatus($data['status'] ?? '');
        $donationEvent->setCauseOfDeath($data['cause_of_death'] ?? '');
        $donationEvent->setClinicalSummary($data['clinical_summary'] ?? '');
        $donationEvent->setNotes($data['notes'] ?? '');
        $donationEvent->setCreatedByUserId($userId);
        try {
            $donationEventId = $donationEvent->create();
            $this->sendResponse(['message' => 'Donation Event created', 'id' => $donationEventId], 201);
        } catch(\Exception $e) {
            $this->sendError('Error creating donation event: ' . $e->getMessage(), 500);
        }
    }

    public function update($userId, $orgId, $userRoles, $id): void
    {
        try {
            if (!in_array('system_administrator', $userRoles) && !in_array('opo_coordinator', $userRoles)) {
                $this->sendError('You do not have permission to update this donation event', 403);
                return;
            }

            $donationEvent = DonationEvent::getById($this->db, $id);

            if (!$donationEvent) {
                $this->sendError('Donation Event not found', 404);
                return;
            }

            if (!in_array('system_administrator', $userRoles) && $donationEvent->getSourceOrganizationId() != $orgId) {
                $this->sendError('You do not have permission to update this donation event', 403);
                return;
            }

            $data = $this->getRequestBody();

            if (isset($data['source_organization_id'])) {
                // Check if the new source_organization_id is valid for this user
                if(!in_array('system_administrator', $userRoles) && $data['source_organization_id'] != $orgId){
                    $this->sendError('You are not allowed to move the event for this organization.', 403);
                    return;
                }
                $donationEvent->setSourceOrganizationId($data['source_organization_id']);
            }
            $donationEvent->setDonationType($data['donation_type'] ?? $donationEvent->getDonationType());
            $donationEvent->setDonorExternalId($data['donor_external_id'] ?? $donationEvent->getDonorExternalId());
            $donationEvent->setEventStartTimestamp($data['event_start_timestamp'] ?? $donationEvent->getEventStartTimestamp());
            $donationEvent->setEventEndTimestamp($data['event_end_timestamp'] ?? $donationEvent->getEventEndTimestamp());
            $donationEvent->setStatus($data['status'] ?? $donationEvent->getStatus());
            $donationEvent->setCauseOfDeath($data['cause_of_death'] ?? $donationEvent->getCauseOfDeath());
            $donationEvent->setClinicalSummary($data['clinical_summary'] ?? $donationEvent->getClinicalSummary());
            $donationEvent->setNotes($data['notes'] ?? $donationEvent->getNotes());
            $donationEvent->setCreatedByUserId($donationEvent->getCreatedByUserId());

            try {
                 $donationEvent->update();
            } catch (Exception $e){
                $this->sendError('Error updating the Donation Event', 500);
                return;
            }


            $donationEvent = DonationEvent::getById($this->db, $id);

            if (!$donationEvent) {
                $this->sendError('Donation Event not found', 404);
                return;
            }

            $this->sendResponse(['message' => 'Donation Event updated'], 200);
        } catch(\Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function delete($userId, $orgId, $userRoles, $id): void
    {
        if (!in_array('system_administrator', $userRoles) && !in_array('opo_coordinator', $userRoles)) {
            $this->sendError('You do not have permission to delete this donation event', 403);
            return;
        }
        try {
            $donationEvent = DonationEvent::getById($this->db, $id);

            if (!$donationEvent) {
                $this->sendError('Donation Event not found', 404);
                return;
            }
            if (!in_array('system_administrator', $userRoles) && $donationEvent->getSourceOrganizationId() != $orgId) {
                $this->sendError('You do not have permission to delete this donation event', 403);
                return;
            }
            // Delete the organs for the event
            ProcuredOrgan::deleteByEvent($this->db, $id);
            $donationEvent->delete();
            $this->sendResponse(['message' => 'Donation Event deleted']);
        } catch (Exception $e) {
            $this->sendError('Error deleting the Donation Event', 500);
        }
    }
}