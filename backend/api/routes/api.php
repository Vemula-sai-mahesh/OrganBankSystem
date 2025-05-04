<?php
use OrganBank\Controllers\AuthController;
use OrganBank\Controllers\UserController;
use OrganBank\Controllers\OrganizationController;
use OrganBank\Controllers\DonationEventController;
use OrganBank\Controllers\ProcuredOrganController;
use OrganBank\Controllers\TransplantController;
use OrganBank\Controllers\AdminController;
use OrganBank\Controllers\UtilityController;
use OrganBank\Middleware\AuthMiddleware;
use OrganBank\Middleware\AdminMiddleware;
use OrganBank\Middleware\OPOMiddleware;
use OrganBank\Middleware\TransplantMiddleware;
use OrganBank\Controllers\MedicalMarkerTypeController;
use OrganBank\Controllers\OrganTypeController;

// Define API routes

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // Organ Types
    $r->addRoute('GET', '/organ-types', [OrganTypeController::class, 'index']);

     // Medical Marker Types
     $r->addRoute('GET', '/medical-marker-types', [MedicalMarkerTypeController::class, 'index']);

      // Auth Routes
      $r->addRoute('POST', '/login', [AuthController::class, 'login']); // Login
      $r->addRoute('POST', '/logout', [AuthController::class, 'logout']); // Logout
      $r->addRoute('POST', '/register', [AuthController::class, 'register']); // Register

     // Medical marker values are public
     $r->addRoute('GET', '/medical-marker-values', [MedicalMarkerTypeController::class, 'getMedicalMarkerValues']);

    // Protected routes
    $r->addGroup('', function (FastRoute\RouteCollector $r)  {

        $r->addRoute('GET', '/users', [UserController::class, 'index']); //list all users
        $r->addRoute('POST', '/users', [UserController::class, 'create']); // Create a new user
        $r->addRoute('GET', '/users/{id}', [UserController::class, 'show']); // Get user by id
        $r->addRoute('POST', '/users/{id}', [UserController::class, 'update']); //update user
        $r->addRoute('DELETE', '/users/{id}', [UserController::class, 'delete']); // Delete user
        $r->addRoute('GET', '/users/{id}/platform-intent', [UserController::class, 'showIntent']); // Get intent by id
        $r->addRoute('POST', '/users/{id}/platform-intent', [UserController::class, 'updateIntent']); //update intent
        // Api Keys
        $r->addRoute('GET', '/api-keys', [\OrganBank\Controllers\ApiKeyController::class, 'index']);
        $r->addRoute('POST', '/api-keys', [\OrganBank\Controllers\ApiKeyController::class, 'create']);
        $r->addRoute('GET', '/api-keys/{id}', [\OrganBank\Controllers\ApiKeyController::class, 'show']);
        $r->addRoute('POST', '/api-keys/{id}', [\OrganBank\Controllers\ApiKeyController::class, 'update']);
        $r->addRoute('DELETE', '/api-keys/{id}', [\OrganBank\Controllers\ApiKeyController::class, 'delete']);

    })->add(new AuthMiddleware());

      // Admin Group
      $r->addGroup('', function (FastRoute\RouteCollector $r) {
          // Admin Routes
          $r->addRoute('GET', '/admin/analytics', [AdminController::class, 'getAnalytics']); // Get system analytics
          $r->addRoute('GET', '/admin/audit-log', [AdminController::class, 'getAuditLogs']); // Get audit logs
           // Organization Routes
          $r->addRoute('POST', '/organizations', [OrganizationController::class, 'create']); // Create a new organization
          $r->addRoute('DELETE', '/organizations/{id}', [OrganizationController::class, 'delete']); // Delete an organization
      })->add(new AdminMiddleware())->add(new AuthMiddleware());


    // Protected routes OPO
    $r->addGroup('', function (FastRoute\RouteCollector $r) {

        // Organization Routes
        $r->addRoute('GET', '/organizations', [OrganizationController::class, 'index']); // List all organizations
        $r->addRoute('GET', '/organizations/{id}', [OrganizationController::class, 'show']); // Get organization by ID
        $r->addRoute('POST', '/organizations/{id}', [OrganizationController::class, 'update']); // Update an organization
        // Donation Event Routes
        $r->addRoute('GET', '/donation-events', [DonationEventController::class, 'index']); // List all donation events
        $r->addRoute('POST', '/donation-events', [DonationEventController::class, 'create']); // Create a new donation event
        $r->addRoute('GET', '/donation-events/{id}', [DonationEventController::class, 'show']); // Get donation event by ID
        $r->addRoute('POST', '/donation-events/{id}', [DonationEventController::class, 'update']); // Update a donation event
        $r->addRoute('DELETE', '/donation-events/{id}', [DonationEventController::class, 'delete']); // Delete a donation event
        // Procured Organ Routes
        $r->addRoute('GET', '/procured-organs', [ProcuredOrganController::class, 'index']); // List all procured organs
        $r->addRoute('POST', '/procured-organs', [ProcuredOrganController::class, 'create']); // Create a new procured organ
        $r->addRoute('GET', '/procured-organs/{id}', [ProcuredOrganController::class, 'show']); // Get procured organ by ID
        $r->addRoute('POST', '/procured-organs/{id}', [ProcuredOrganController::class, 'update']); // Update a procured organ
        $r->addRoute('DELETE', '/procured-organs/{id}', [ProcuredOrganController::class, 'delete']); // Delete a procured organ
        // organ status log
        $r->addRoute('POST', '/procured-organs/{id}/status-log', [ProcuredOrganController::class, 'updateStatus']); //update status


    })->add(new OPOMiddleware())->add(new AuthMiddleware());


    // Protected routes Transplant
    $r->addGroup('', function (FastRoute\RouteCollector $r) {
        // Transplant Routes
        $r->addRoute('GET', '/transplants', [TransplantController::class, 'index']); // List all transplants
        $r->addRoute('POST', '/transplants', [TransplantController::class, 'create']); // Create a new transplant
        $r->addRoute('GET', '/transplants/{id}', [TransplantController::class, 'show']); // Get transplant by ID
        $r->addRoute('POST', '/transplants/{id}', [TransplantController::class, 'update']); // Update a transplant
        $r->addRoute('DELETE', '/transplants/{id}', [TransplantController::class, 'delete']); // Delete a transplant
    })->add(new TransplantMiddleware())->add(new AuthMiddleware());


    // Utilities Routes
    $r->addRoute('GET', '/utilities/pdf/platform-intent/{id}', [UtilityController::class, 'generateIntentPdf']); // Generate pdf intent
    $r->addRoute('GET', '/utilities/pdf/procured-organ/{id}', [UtilityController::class, 'generateOrganPdf']); // Generate pdf organ


    
    
   
    
    
    
   
    // Other routes will go here...
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Dispatch the route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

return $routeInfo;

