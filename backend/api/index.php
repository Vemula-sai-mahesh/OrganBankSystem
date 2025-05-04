<?php
// Start output buffering
ob_start(); 

// Set content type to JSON immediately
header('Content-Type: application/json');

// Define log file path relative to this script
$logFilePath = __DIR__ . '/php_error.log';

// Enable error reporting for development (set display_errors to 0 in production)
ini_set('display_errors', 0); // Turn off displaying errors in output
ini_set('log_errors', 1);      // Enable logging errors
ini_set('error_log', $logFilePath); // Set the log file
error_reporting(E_ALL);

// Custom error handler to catch non-exception errors and log them
set_error_handler(function ($severity, $message, $file, $line) use ($logFilePath) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return false;
    }
    $errorType = [
        E_ERROR              => 'E_ERROR',
        E_WARNING            => 'E_WARNING',
        E_PARSE              => 'E_PARSE',
        E_NOTICE             => 'E_NOTICE',
        E_CORE_ERROR         => 'E_CORE_ERROR',
        E_CORE_WARNING       => 'E_CORE_WARNING',
        E_COMPILE_ERROR      => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING    => 'E_COMPILE_WARNING',
        E_USER_ERROR         => 'E_USER_ERROR',
        E_USER_WARNING       => 'E_USER_WARNING',
        E_USER_NOTICE        => 'E_USER_NOTICE',
        E_STRICT             => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED         => 'E_DEPRECATED',
        E_USER_DEPRECATED    => 'E_USER_DEPRECATED',
    ];
    $logMessage = "[" . date('Y-m-d H:i:s') . "] " . ($errorType[$severity] ?? 'Unknown Error') . ": {$message} in {$file} on line {$line}\n";
    error_log($logMessage, 3, $logFilePath);
    // Don't execute PHP internal error handler if it's not a fatal error type
    // Return true to prevent PHP's standard error handler, unless it's fatal
    // Note: Returning true for warnings/notices might hide output issues during dev.
    // Consider returning false during development to let PHP handle non-fatal errors too.
    // return in_array($severity, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
    return false; // Let PHP handle non-fatal errors as well (useful for debugging)
});

// --- Main application logic wrapped in try-catch ---
try {
    // Define base paths for autoloading
    $controllerBasePath = __DIR__ . '/controllers/';
    $modelBasePath = __DIR__ . '/../models/';       // Relative path assumes index.php is in /api/
    $middlewareBasePath = __DIR__ . '/middleware/';
    $utilsBasePath = __DIR__ . '/../utils/';       // Relative path assumes index.php is in /api/

    // Simple Autoloader
    spl_autoload_register(function ($class) use ($controllerBasePath, $modelBasePath, $middlewareBasePath, $utilsBasePath, $logFilePath) {
        $parts = explode('\\', $class);
        $className = end($parts);
        $file = null;

        // Map namespaces to base paths
        if (strpos($class, 'App\\Controllers\\') === 0) {
            $file = $controllerBasePath . $className . '.php';
        } elseif (strpos($class, 'App\\Models\\') === 0) {
            $file = $modelBasePath . $className . '.php';
        } elseif (strpos($class, 'App\\Middleware\\') === 0) {
            $file = $middlewareBasePath . $className . '.php';
        // ***** ADDED: Mapping for OrganBank\Utils namespace *****
        } elseif (strpos($class, 'OrganBank\\Utils\\') === 0) {
             $file = $utilsBasePath . $className . '.php'; // Assumes Database.php is in backend/utils/
        } elseif (strpos($class, 'App\\Utils\\') === 0) { // Keep old App\Utils if still used by other parts
            $file = $utilsBasePath . $className . '.php';
        }

        if ($file && file_exists($file)) {
            require_once $file;
        } else if ($file) {
            // Log if the expected file was not found for a mapped namespace
            error_log("[" . date('Y-m-d H:i:s') . "] Autoload Error: Class {$class} file not found at expected path {$file}\n", 3, $logFilePath);
        }
        // Optional: Log if no mapping was found (can be noisy)
        // else {
        //    error_log("[" . date('Y-m-d H:i:s') . "] Autoload Warning: No path mapping found for class {$class}\n", 3, $logFilePath);
        // }
    });

    // ***** CHANGED: Database Connection using the Database Class *****
    try {
        // Instantiate the Database class (using its full namespace)
        $database = new \OrganBank\Utils\Database(); 
        // Get the PDO connection object
        $db = $database->getConnection(); // $db now holds the active PDO connection
        
    } catch (\PDOException $e) {
        // Catch specific PDO exceptions during connection attempt within Database class
        error_log("[" . date('Y-m-d H:i:s') . "] Database Connection Error (PDOException): " . $e->getMessage() . "\n", 3, $logFilePath);
        http_response_code(500);
        // Avoid echoing detailed errors in production
        echo json_encode(['error' => 'Database connection failed. Please check server logs.']);
        exit; // Exit early on DB connection failure
    } catch (\Throwable $e) { 
        // Catch any other error during Database class instantiation/connection 
        // (e.g., the RuntimeException from getConnection if connect() failed silently)
        error_log("[" . date('Y-m-d H:i:s') . "] Error Initializing Database Connection: " . get_class($e) . " - " . $e->getMessage() . "\n", 3, $logFilePath);
        http_response_code(500);
        echo json_encode(['error' => 'Error initializing database connection. Please check server logs.']);
        exit;
    }
    // ***** END OF DATABASE CONNECTION CHANGE *****


    // Basic routing setup
    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];





    // Remove the base path if present and handle query strings
    $basePath = '/OrganBankSystem/backend/api/';
    $requestPath = parse_url($requestUri, PHP_URL_PATH); // Get only the path part

    if (strpos($requestPath, $basePath) === 0) {
        $requestPath = substr($requestPath, strlen($basePath)); 
    } else {
        $requestPath = ltrim($requestPath, '/'); // Trim leading slash if base path isn't present
    }
    

    // Middleware function (ensure session is started appropriately if needed)
    // Consider moving session_start() to the top if used globally
    function is_admin_middleware($db, $id, $logFilePath){ // $db is the PDO object
        if (session_status() == PHP_SESSION_NONE) {
            // Be cautious starting session here if headers already sent
            // Best practice: Start session earlier, before any output
             session_start(); 
        }

        if(!isset($_SESSION['user_id'])){
             http_response_code(401);
             echo json_encode(['error' => 'Unauthorized: Login required']);
             exit();
        }

        try {
             // Ensure AdminMiddleware class is loaded via autoloader
             $middleware = new \App\Middleware\AdminMiddleware($db); // Pass PDO object
             if(!$middleware->isAdmin($_SESSION['user_id'])){
                 http_response_code(403);
                 echo json_encode(['error' => 'Forbidden: Admin privileges required']);
                 exit();
             }
        } catch (\Throwable $e) { // Catch potential errors loading/running middleware
             error_log("[" . date('Y-m-d H:i:s') . "] Middleware Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString() . "\n", 3, $logFilePath);
             http_response_code(500);
             echo json_encode(['error' => 'Error processing authorization.']);
             exit();
        }
    }

    // Define Routes (structure remains the same, relies on $db being PDO object)
    $routes = [
        // Root path
        '' => ['GET' => function () { echo json_encode(['message' => 'OrganBank API v1']); }],
        // Auth
        'login' => ['POST' => 'App\Controllers\AuthController@login'],
        'logout' => ['POST' => 'App\Controllers\AuthController@logout'],
        // Auth
        'register' => ['POST' => 'App\Controllers\AuthController@register'],   
        // Organizations
        'organizations' => ['GET' => 'App\Controllers\OrganizationController@index','POST' => 'App\Controllers\OrganizationController@store'],
        'organizations/{id}' => ['PUT' => 'App\Controllers\OrganizationController@update','DELETE' => 'App\Controllers\OrganizationController@delete'],
        // Users
        'users' => ['GET' => 'App\Controllers\UserController@index', 'POST' => 'App\Controllers\UserController@store'], // User registration/creation
        'users/{id}' => [
            'PUT' => 'App\Controllers\UserController@update', // Update user details
            'DELETE' => function($db, $id) use ($logFilePath) { // Delete user (Admin only)
                 is_admin_middleware($db, $id, $logFilePath);
                 $controller = new App\Controllers\UserController($db);
                 $controller->delete($id);
             }
         ],
        'users/{id}/admin' => [ // Set user as admin (Admin only)
             'PUT' => function ($db, $id) use ($logFilePath) { 
                 is_admin_middleware($db, $id, $logFilePath);
                 $controller = new App\Controllers\UserController($db);
                 $controller->setAdmin($id); // Assumes setAdmin method exists
             }
         ],
        // Donation Events
        'donation-events' => ['GET' => 'App\Controllers\DonationEventController@index', 'POST' => 'App\Controllers\DonationEventController@store'],
        'donation-events/{id}' => ['PUT' => 'App\Controllers\DonationEventController@update', 'DELETE' => 'App\Controllers\DonationEventController@delete'],
        // Procured Organs
        'procured-organs' => ['GET' => 'App\Controllers\ProcuredOrganController@index', 'POST' => 'App\Controllers\ProcuredOrganController@store'],
        'procured-organs/{id}' => ['PUT' => 'App\Controllers\ProcuredOrganController@update', 'DELETE' => 'App\Controllers\ProcuredOrganController@delete'],
        // Medical Markers
        'medical-markers' => ['GET' => 'App\Controllers\MedicalMarkerController@index', 'POST' => 'App\Controllers\MedicalMarkerController@store'],
        'medical-markers/{id}' => ['PUT' => 'App\Controllers\MedicalMarkerController@update', 'DELETE' => 'App\Controllers\MedicalMarkerController@delete'],
         // Organ Transplants
        'organ-transplants' => ['GET' => 'App\Controllers\OrganTransplantController@index', 'POST' => 'App\Controllers\OrganTransplantController@store'],
        'organ-transplants/{id}' => ['PUT' => 'App\Controllers\OrganTransplantController@update', 'DELETE' => 'App\Controllers\OrganTransplantController@delete'],
    ];

    // Route Matching Logic (remains largely the same)
    $routeFound = false; 
    foreach ($routes as $route => $handlers) {
        // Convert route placeholders like {id} to regex named capture groups (?P<id>\d+) - Use \d+ for numeric IDs
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[a-zA-Z0-9_.-]+)', $route); // Allow more characters in IDs if needed
        $pattern = "#^" . str_replace('/', '\/', $pattern) . "$#"; // Add regex delimiters, anchors, escape slashes

        if (preg_match($pattern, $requestPath, $matches)) {
            $routeFound = true;
            if (isset($handlers[$method])) {
                $handler = $handlers[$method];
                // Extract named parameters (like 'id') from $matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); 

                try { // Add try-catch around handler execution
                    // Check if handler is a callable function (closure)
                    if (is_callable($handler)) {
                        // Pass PDO connection ($db) first, then extracted parameters
                        call_user_func_array($handler, array_merge([$db], $params));
                    } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                        // Handler is "Controller@method" string
                        list($controllerName, $methodName) = explode('@', $handler);

                        // Check if the controller name already includes the App namespace
                        if (strpos($controllerName, 'App\\Controllers\\') !== 0) {
                             // Assume it's in App\Controllers if no full namespace given
                             $controllerClassName = 'App\\Controllers\\' . $controllerName;
                        } else {
                            $controllerClassName = $controllerName; // Already has namespace
                        }

                        if (class_exists($controllerClassName)) {
                            $controller = new $controllerClassName($db); // Pass PDO connection
                            if (method_exists($controller, $methodName)) {
                                // Call the controller method with extracted parameters
                                call_user_func_array([$controller, $methodName], $params);
                            } else {
                                http_response_code(500);
                                echo json_encode(['error' => "Method {$methodName} not found in controller {$controllerClassName}"]);
                                error_log("Routing Error: Method {$methodName} not found in {$controllerClassName}", 3, $logFilePath);
                            }
                        } else {
                            http_response_code(500);
                            echo json_encode(['error' => "Controller class {$controllerClassName} not found. Check namespace and autoloader."]);
                             error_log("Routing Error: Controller {$controllerClassName} not found. Autoloader check needed.", 3, $logFilePath);
                        }
                    } else {
                         http_response_code(500);
                         echo json_encode(['error' => "Invalid handler specified for route {$route} and method {$method}"]);
                         error_log("Routing Error: Invalid handler for route {$route} [{$method}]", 3, $logFilePath);
                    }

                } catch (\Throwable $e) { // Catch errors specifically from route handler/controller
                     error_log("[" . date('Y-m-d H:i:s') . "] Handler Error ({$route} [{$method}]): " . get_class($e) . " - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString() . "\n", 3, $logFilePath);
                     http_response_code(500);
                     echo json_encode(['error' => 'An error occurred while processing your request.']);
                }

            } else {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['error' => "Method {$method} not allowed for this route ({$requestPath})"]);
            }
            break; // Stop searching once a route is matched
        }
    }

    // Final check if any route was matched
    if (!$routeFound) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Route not found', 'requested_path' => $requestPath]);
    }

} catch (\Throwable $e) { // Catch any uncaught error from the main try block
    // Log the exception/error
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Uncaught Global Error: " . get_class($e) . " - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    error_log($logMessage, 3, $logFilePath);

    // Send a generic error response IF headers haven't been sent already
    if (!headers_sent()) {
         http_response_code(500); // Internal Server Error
         echo json_encode(['error' => 'An internal server error occurred. Please check server logs.']);
    }
    // Optionally exit here, though script should end anyway
    // exit; 
} finally {
    // Clean up output buffering
    // Use ob_end_clean() if you want to discard buffer content on error
    // Use ob_end_flush() to send buffer content (might send partial JSON on error)
     if (ob_get_level() > 0) {
        ob_end_flush(); 
     }
}

?>