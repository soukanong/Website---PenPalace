<?php
/**
 * Logout Handler
 * Destroys user session and redirects to the home page
 */

require_once __DIR__ . "/includes/auth.php";

$auth = Auth::getInstance();

// Check for CSRF token
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["csrf_token"])) {
    try {
        $auth->validateCsrfToken($_POST["csrf_token"]);
    } catch (Exception $e) {
        // Invalid CSRF token, log the attempt and redirect to home
        error_log("Invalid CSRF token: " . $e->getMessage());
        header("Location: index.php");
        exit();
    }
} else {
    // No CSRF token or invalid request method, redirect to home
    header("Location: index.php");
    exit();
}

// Logout the user
$auth->logout();

// Redirect to home page
header("Location: index.php");
exit();
