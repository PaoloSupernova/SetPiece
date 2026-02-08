<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * Authentication Controller
 * 
 * Handles user login, logout, and session management.
 */
class AuthController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Show the login form
     */
    public function showLogin(): void
    {
        // Redirect to dashboard if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->render('auth.login', [], null);
    }

    /**
     * Process login attempt
     */
    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required');
            $this->redirect('/login');
        }

        // Find user by email
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->flash('error', 'Invalid credentials');
            $this->redirect('/login');
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect to intended page or dashboard
        $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);

        $this->flash('success', 'Welcome back, ' . $user['name']);
        $this->redirect($redirectTo);
    }

    /**
     * Logout and destroy session
     */
    public function logout(): void
    {
        // Destroy session
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();

        $this->redirect('/login');
    }
}
