<?php
// File: includes/inc-constants.php

/**
 * Determine if the script is running on localhost or live environment,
 * and define database connection constants accordingly.
 */

if (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    // Localhost credentials
    define('DRIVER', 'mysql');
    define('HOST', 'localhost');
    define('USER', 'root'); // Default MySQL user for localhost
    define('PASS', '');     // Typically no password for local MySQL
    define('DATA', 'dev_saas'); // The database you're using locally
} else {
    // Production server credentials
    define('DRIVER', 'mysql');
    define('HOST', 'localhost');
    define('USER', 'bigbrothertimetracker'); // Production database user (replace 'prod_user' with actual production username)
    define('PASS', 'XvIhd4eHG3I55yO'); // Production password (replace 'prod_pass' with actual production password)
    define('DATA', 'bigbrothertimetracker'); // Production database name (replace 'prod_saas' with actual production database name)
}
?>