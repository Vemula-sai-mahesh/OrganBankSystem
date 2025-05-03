php
<?php

// Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload (For the moment we don't have it, so we remove it)
//require_once __DIR__ . '/../../vendor/autoload.php';

// Load database configuration
$dbConfig = require_once __DIR__ . '/../config/database.php';

// Create a Database connection
try {
    $db = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['database']}", $dbConfig['username'], $dbConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    http_response_code(500);
    exit;
}

// Basic routing
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove the base path if present
$basePath = '/OrganBankSystem/backend/api/';

if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}


function is_admin_middleware($db, $id){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['user_id'])){
        echo json_encode(['error' => 'Unauthorized'], JSON_THROW_ON_ERROR);
        http_response_code(401);
        exit();
    }

    $middleware = new \App\Middleware\AdminMiddleware($db);
    if(!$middleware->isAdmin($_SESSION['user_id'])){
        echo json_encode(['error' => 'Forbidden: Admin privileges required'], JSON_THROW_ON_ERROR);
        http_response_code(403);
        exit();
    }

}

// Routes
$routes = [
    '/' => ['GET' => function () {
        echo json_encode(['message' => 'OrganBank API v1']);
    }],
    '/login' => ['POST' => '\App\Controllers\AuthController@login'],
    '/logout' => ['POST' => '\App\Controllers\AuthController@logout'],
        '/organizations' => ['GET' => '\App\Controllers\OrganizationController@index', 'POST' => '\App\Controllers\OrganizationController@store'],
        '/users/{id}/admin' => [
                'PUT' => function ($db, $id){
                    is_admin_middleware($db, $id);
                    $controller = new \App\Controllers\UserController($db);
                    $controller->setAdmin($id);
                }
            ],

    '/users' => ['GET' => '\App\Controllers\UserController@index', 'POST' => '\App\Controllers\UserController@store'],
    '/users/{id}/admin' => ['PUT' => '\App\Controllers\UserController@setAdmin'],
    '/donation-events' => ['GET' => '\App\Controllers\DonationEventController@index', 'POST' => '\App\Controllers\DonationEventController@store'],
    '/procured-organs' => ['GET' => '\App\Controllers\ProcuredOrganController@index', 'POST' => '\App\Controllers\ProcuredOrganController@store'],
    '/procured-organs/{id}' => ['PUT' => '\App\Controllers\ProcuredOrganController@update', 'DELETE' => '\App\Controllers\ProcuredOrganController@delete'],
    '/medical-markers/{id}' => ['PUT' => '\App\Controllers\MedicalMarkerController@update', 'DELETE' => '\App\Controllers\MedicalMarkerController@delete'],
    '/medical-markers' => ['GET' => '\App\Controllers\MedicalMarkerController@index', 'POST' => '\App\Controllers\MedicalMarkerController@store'],
    '/organ-transplants' => ['GET' => '\App\Controllers\OrganTransplantController@index', 'POST' => '\App\Controllers\OrganTransplantController@store'],
    '/organ-transplants/{id}' => ['PUT' => '\App\Controllers\OrganTransplantController@update', 'DELETE' => '\App\Controllers\OrganTransplantController@delete'],
        '/organizations/{id}' => ['PUT' => '\App\Controllers\OrganizationController@update', 'DELETE' => '\App\Controllers\OrganizationController@delete'],

        '/users/{id}' => [
            'PUT' => '\App\Controllers\UserController@update',
            'DELETE' => function($db, $id){
                is_admin_middleware($db, $id);
                $controller = new \App\Controllers\UserController($db);
                $controller->delete($id);
            }
        ],

    '/donation-events/{id}' => ['PUT' => '\App\Controllers\DonationEventController@update', 'DELETE' => '\App\Controllers\DonationEventController@delete'],
 ];
 









foreach ($routes as $route => $handlers) {
    if ($requestUri === $route) {
        $routeFound = true;
        if (isset($handlers[$method])) {
            $handler = $handlers[$method];
            if (is_callable($handler)) {
                $handler($db);
            } elseif (is_string($handler)) {
                list($controllerName, $methodName) = explode('@', $handler);
                $controllerClassName = $controllerName;
              if (class_exists($controllerClassName)) {
                    $controller = new $controllerClassName($db);
                    if (method_exists($controller, $methodName)) {
                        $controller->$methodName();
                    } else {
                        echo json_encode(['error' => 'Method not found'], JSON_THROW_ON_ERROR);
                        http_response_code(405); // Method Not Allowed
                    }
                } else {
                    echo json_encode(['error' => 'Controller not found'], JSON_THROW_ON_ERROR);
                    http_response_code(500); // Internal Server Error
                }
            }
        } else {
            echo json_encode(['error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
            http_response_code(405); // Method Not Allowed
        }
        break;
    }
}

if (!$routeFound) {
  foreach ($routes as $route => $handlers) {
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $route); // Convert route placeholders to regex groups
    $pattern = "#^$pattern$#";
    if (preg_match($pattern, $requestUri, $matches)) {
        $routeFound = true;
        if (isset($handlers[$method])) {
            $handler = $handlers[$method];
            if (is_callable($handler)) {
                $handler($db);
            } elseif (is_string($handler)) {
                list($controllerName, $methodName) = explode('@', $handler);
                $controllerClassName = $controllerName;
              if (class_exists($controllerClassName)) {
                    $controller = new $controllerClassName($db);
                    if (method_exists($controller, $methodName)) {
                        // Extract parameters from $matches and pass them to the controller method
                        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        call_user_func_array([$controller, $methodName], $params);
                    } else {
                        echo json_encode(['error' => 'Method not found'], JSON_THROW_ON_ERROR);
                        http_response_code(405); // Method Not Allowed
                    }
                }
            }
        } else {
            echo json_encode(['error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
            http_response_code(405); // Method Not Allowed
        }
        break;
    }
  }
}








if (!$routeFound) {
    echo json_encode(['error' => 'Route not found'], JSON_THROW_ON_ERROR);
    http_response_code(404);
}