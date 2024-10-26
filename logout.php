<?php
// File: logout.php

require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) session_start(); // Start session if not already started

$_SESSION = []; // Clear all session data

// Destroy session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

session_destroy(); // Finally, destroy the session

header("Location: " . fullUrl() . "login.php"); // Redirect to login page
exit();
?>
