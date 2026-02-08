<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Abstract Base Controller
 * 
 * Provides common functionality for all controllers including
 * view rendering, JSON responses, redirects, and authentication helpers.
 */
abstract class Controller
{
    /**
     * Render a view with optional layout
     * 
     * @param string $view Dot-notation view path (e.g., 'complaints.index')
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout file to use (null for no layout)
     */
    protected function render(string $view, array $data = [], ?string $layout = 'app'): void
    {
        // Convert dot notation to file path
        $viewPath = str_replace('.', '/', $view);
        $viewFile = __DIR__ . "/../../views/{$viewPath}.php";

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View not found: {$view}");
        }

        // Extract data for use in view
        extract($data);

        // Start output buffering
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Wrap in layout if specified
        if ($layout !== null) {
            $layoutFile = __DIR__ . "/../../views/layouts/{$layout}.php";
            
            if (!file_exists($layoutFile)) {
                http_response_code(500);
                die("Layout not found: {$layout}");
            }
            
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Send a JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to a URI
     */
    protected function redirect(string $uri, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$uri}");
        exit;
    }

    /**
     * Require authentication, redirect to login if not authenticated
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
        }
    }

    /**
     * Get the current user's role
     */
    protected function currentRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Get the current user's ID
     */
    protected function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get the current user's name
     */
    protected function currentUserName(): ?string
    {
        return $_SESSION['name'] ?? null;
    }

    /**
     * Flash a message to the session
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get and clear flash message
     */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Store old input in session (for form validation)
     */
    protected function withOldInput(array $input): void
    {
        $_SESSION['old_input'] = $input;
    }

    /**
     * Get old input value
     */
    protected function old(string $key, string $default = ''): string
    {
        $value = $_SESSION['old_input'][$key] ?? $default;
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clear old input from session
     */
    protected function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }
}
