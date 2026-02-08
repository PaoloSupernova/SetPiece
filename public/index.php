<?php

declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_ENV'] === 'development' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Custom error and exception handlers
set_error_handler(function ($severity, $message, $file, $line) {
    // Don't throw exception for suppressed errors (with @)
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($exception) {
    error_log($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    
    http_response_code(500);
    
    if ($_ENV['APP_ENV'] === 'development') {
        echo '<h1>500 Internal Server Error</h1>';
        echo '<pre>' . htmlspecialchars($exception->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>An unexpected error occurred. Please try again later.</p>';
    }
});

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
    'cookie_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
    'cookie_secure' => $_ENV['SESSION_SECURE'] === 'true',
]);

// Create router instance
$router = new App\Core\Router();

// Authentication routes
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// Dashboard route
$router->get('/', 'DashboardController', 'index');
$router->get('/dashboard', 'DashboardController', 'index');

// Complaint routes
$router->get('/complaints', 'ComplaintController', 'index');
$router->get('/complaints/create', 'ComplaintController', 'create');
$router->post('/complaints', 'ComplaintController', 'store');
$router->get('/complaints/{id}', 'ComplaintController', 'show');
$router->post('/complaints/{id}/escalate', 'ComplaintController', 'escalate');

// Audit route (admin/dso only)
$router->get('/audit', 'AuditController', 'index');

// Dispatch the request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);
