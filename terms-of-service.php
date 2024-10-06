<?php
// File: terms-of-service.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Fetch site settings
$siteSettings = getSiteSettings();

// Include the header
include(__DIR__ . "/includes/inc-header.php");
?>


<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>