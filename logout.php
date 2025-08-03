<?php
// File: logout.php

require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: store a flash message (if you use one)
$_SESSION['message'] = 'You have been logged out successfully.';

// Clear all session variables
$_SESSION = [];

// Destroy session cookie securely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

// Redirect to clean login URL
header("Location: " . fullUrl() . "login/");
exit();
