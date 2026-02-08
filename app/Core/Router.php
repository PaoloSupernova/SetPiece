<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple Regex-Based Router
 * 
 * Matches URI patterns against registered routes and dispatches
 * to the appropriate controller action.
 */
class Router
{
    private array $routes = [];

    /**
     * Register a GET route
     */
    public function get(string $pattern, string $controller, string $action): void
    {
        $this->routes['GET'][$pattern] = ['controller' => $controller, 'action' => $action];
    }

    /**
     * Register a POST route
     */
    public function post(string $pattern, string $controller, string $action): void
    {
        $this->routes['POST'][$pattern] = ['controller' => $controller, 'action' => $action];
    }

    /**
     * Dispatch the request to the matching route
     */
    public function dispatch(string $uri, string $method): void
    {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Check if method exists
        if (!isset($this->routes[$method])) {
            $this->sendError(405, 'Method Not Allowed');
            return;
        }

        // Try to match routes
        foreach ($this->routes[$method] as $pattern => $route) {
            $regex = $this->convertPatternToRegex($pattern);
            
            if (preg_match($regex, $uri, $matches)) {
                // Remove the full match
                array_shift($matches);
                
                $controllerClass = "App\\Controllers\\{$route['controller']}";
                
                if (!class_exists($controllerClass)) {
                    $this->sendError(500, 'Controller not found');
                    return;
                }
                
                $controller = new $controllerClass();
                $action = $route['action'];
                
                if (!method_exists($controller, $action)) {
                    $this->sendError(500, 'Action not found');
                    return;
                }
                
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        // No route matched
        $this->sendError(404, 'Not Found');
    }

    /**
     * Convert route pattern to regex
     */
    private function convertPatternToRegex(string $pattern): string
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        // Replace {id} with capture group for digits
        $pattern = preg_replace('/\{id\}/', '(\d+)', $pattern);
        
        // Replace {slug} with capture group for alphanumeric + hyphens
        $pattern = preg_replace('/\{slug\}/', '([a-zA-Z0-9\-]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Send an error response
     */
    private function sendError(int $code, string $message): void
    {
        http_response_code($code);
        echo "<h1>{$code} {$message}</h1>";
    }
}
