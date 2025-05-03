<?php
ob_start(); // Start output buffering

// Set content type to JSON immediately
header('Content-Type: application/json');

// Define log file path relative to this script
$logFilePath = __DIR__ . '/php_error.log';

// Enable error reporting for development (remove in production)
ini_set('display_errors', 0); // Turn off displaying errors in output
ini_set('log_errors', 1);    // Enable logging errors
ini_set('error_log', $logFilePath); // Set the log file
error_reporting(E_ALL);

// Custom error handler to catch non-exception errors and log them
set_error_handler(function ($severity, $message, $file, $line) use ($logFilePath) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return false;
    }
    $errorType = [
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
    ];
    $logMessage = "[" . date('Y-m-d H:i:s') . "] " . ($errorType[$severity] ?? 'Unknown Error') . ": {$message} in {$file} on line {$line}\n";
    error_log($logMessage, 3, $logFilePath);
    // Don't execute PHP internal error handler if it's not a fatal error type
    // Return true to prevent PHP's standard error handler, unless it's fatal
    return in_array($severity, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
});

// --- Main application logic wrapped in try-catch ---
try {
    // Autoload (For the moment we don't have it, so we remove it)
    //require_once __DIR__ . '/../../vendor/autoload.php';

    // Load database configuration
    $dbConfig = require_once __DIR__ . '/../config/database.php';

    // Create a Database connection
    try {
        $db = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['database']}", $dbConfig['username'], $dbConfig['password']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Recommended for security
    } catch (PDOException $e) {
        // Log the DB connection error specifically
        error_log("[" . date('Y-m-d H:i:s') . "] Database Connection Error: " . $e->getMessage() . "\n", 3, $logFilePath);
        // Send generic error to client
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed.']);
        exit; // Exit early on DB connection failure
    }


    // Attempt to load controller and model classes (basic autoloading simulation)
    // Adjust paths as necessary based on your actual structure
    $controllerBasePath = __DIR__ . '/controllers/';
    $modelBasePath = __DIR__ . '/../models/';
    $middlewareBasePath = __DIR__ . '/middleware/';
    $utilsBasePath = __DIR__ . '/../utils/';

    spl_autoload_register(function ($class) use ($controllerBasePath, $modelBasePath, $middlewareBasePath, $utilsBasePath, $logFilePath) {
        $parts = explode('\\', $class);
        $className = end($parts);
        $file = null;
        if (strpos($class, 'App\\Controllers\\') === 0) {
            $file = $controllerBasePath . $className . '.php';
        } elseif (strpos($class, 'App\\Models\\') === 0) {
            $file = $modelBasePath . $className . '.php';
        } elseif (strpos($class, 'App\\Middleware\\') === 0) {
            $file = $middlewareBasePath . $className . '.php';
        } elseif (strpos($class, 'App\\Utils\\') === 0) {
            $file = $utilsBasePath . $className . '.php';
        }

        if ($file && file_exists($file)) {
            require_once $file;
        } else if ($file) {
            // Log if the expected file was not found
             error_log("[" . date('Y-m-d H:i:s') . "] Autoload Error: Class {$class} file not found at {$file}\n", 3, $logFilePath);
        }
    });

    // Basic routing
    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    // Remove the base path if present
    $basePath = '/OrganBankSystem/backend/api/';
    if (strpos($requestUri, $basePath) === 0) {
        $requestUri = ltrim(substr($requestUri, strlen($basePath)), '/'); // Trim leading slash too
    }

    // Middleware function needs access to log path potentially
    function is_admin_middleware($db, $id, $logFilePath){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if(!isset($_SESSION['user_id'])){
             http_response_code(401);
             echo json_encode(['error' => 'Unauthorized']);
             exit();
        }

        try {
             $middleware = new \App\Middleware\AdminMiddleware($db);
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

    // Routes
    $routes = [
        '' => ['GET' => function () {
            echo json_encode(['message' => 'OrganBank API v1']);
        }], // Changed '/' to '' to match trimmed requestUri
        'login' => ['POST' => 'App\Controllers\AuthController@login'],
        'logout' => ['POST' => 'App\Controllers\AuthController@logout'],
        'organizations' => ['GET' => 'App\Controllers\OrganizationController@index', 'POST' => 'App\Controllers\OrganizationController@store'],
        'users/{id}/admin' => [
                'PUT' => function ($db, $id) use ($logFilePath) { // Pass logFilePath
                    is_admin_middleware($db, $id, $logFilePath);
                    $controller = new App\Controllers\UserController($db);
                    $controller->setAdmin($id);
                }
            ],
        'users' => ['GET' => 'App\Controllers\UserController@index', 'POST' => 'App\Controllers\UserController@store'],
       // 'users/{id}/admin' => ['PUT' => 'App\Controllers\UserController@setAdmin'], // Duplicate? Removed lower one
        'donation-events' => ['GET' => 'App\Controllers\DonationEventController@index', 'POST' => 'App\Controllers\DonationEventController@store'],
        'procured-organs' => ['GET' => 'App\Controllers\ProcuredOrganController@index', 'POST' => 'App\Controllers\ProcuredOrganController@store'],
        'procured-organs/{id}' => ['PUT' => 'App\Controllers\ProcuredOrganController@update', 'DELETE' => 'App\Controllers\ProcuredOrganController@delete'],
        'medical-markers/{id}' => ['PUT' => 'App\Controllers\MedicalMarkerController@update', 'DELETE' => 'App\Controllers\MedicalMarkerController@delete'],
        'medical-markers' => ['GET' => 'App\Controllers\MedicalMarkerController@index', 'POST' => 'App\Controllers\MedicalMarkerController@store'],
        'organ-transplants' => ['GET' => 'App\Controllers\OrganTransplantController@index', 'POST' => 'App\Controllers\OrganTransplantController@store'],
        'organ-transplants/{id}' => ['PUT' => 'App\Controllers\OrganTransplantController@update', 'DELETE' => 'App\Controllers\OrganTransplantController@delete'],
        'organizations/{id}' => ['PUT' => 'App\Controllers\OrganizationController@update', 'DELETE' => 'App\Controllers\OrganizationController@delete'],
        'users/{id}' => [
            'PUT' => 'App\Controllers\UserController@update',
            'DELETE' => function($db, $id) use ($logFilePath) { // Pass logFilePath
                is_admin_middleware($db, $id, $logFilePath);
                $controller = new App\Controllers\UserController($db);
                $controller->delete($id);
            }
        ],
        'donation-events/{id}' => ['PUT' => 'App\Controllers\DonationEventController@update', 'DELETE' => 'App\Controllers\DonationEventController@delete'],
     ];

    $routeFound = false; // Initialize routeFound to false

    foreach ($routes as $route => $handlers) {
        // Convert route placeholders like {id} to regex named capture groups (?P<id>\w+)
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $route);
        $pattern = "#^" . $pattern . "$#"; // Add regex delimiters and anchors

        if (preg_match($pattern, $requestUri, $matches)) {
            $routeFound = true;
            if (isset($handlers[$method])) {
                $handler = $handlers[$method];
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Extract named parameters

                // Check if handler is a callable function (closure)
                if (is_callable($handler)) {
                    // Pass extracted parameters along with the database connection
                    call_user_func_array($handler, array_merge([$db], $params));
                } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                    // Handler is Controller@method string
                    list($controllerName, $methodName) = explode('@', $handler);

                    // Ensure the full namespace is used if not already included
                     // Check if the controllerName lacks any known App namespace prefix
                     if (strpos($controllerName, 'App\\') !== 0) {
                        // Assuming controllers are the default if no namespace given
                        $controllerClassName = 'App\\Controllers\\' . $controllerName;
                    } else {
                         $controllerClassName = $controllerName; // Already has namespace
                    }

                    if (class_exists($controllerClassName)) {
                        $controller = new $controllerClassName($db);
                        if (method_exists($controller, $methodName)) {
                            // Call the controller method with extracted parameters
                            call_user_func_array([$controller, $methodName], $params);
                        } else {
                            http_response_code(500);
                            echo json_encode(['error' => "Method {$methodName} not found in controller {$controllerClassName}"]);
                        }
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => "Controller class {$controllerClassName} not found. Check namespace and autoloader."]);
                    }
                } else {
                     http_response_code(500);
                     echo json_encode(['error' => "Invalid handler specified for route {$route}"]);
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => "Method {$method} not allowed for route {$route}"]);
            }
            break; // Stop searching once a route is matched
        }
    }

    // Final check if any route was matched
    if (!$routeFound) {
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

} catch (\Throwable $e) {
    // Log any exception/error caught from the main block
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Uncaught Error: " . get_class($e) . " - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    error_log($logMessage, 3, $logFilePath);

    // Send a generic error response to the client
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred.']);
    exit; // Ensure script termination after sending error
}