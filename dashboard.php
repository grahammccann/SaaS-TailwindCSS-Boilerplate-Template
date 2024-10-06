<?php
// File: dashboard.php
?>

<?php
require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isLoggedIn()) {
    header("Location: " . fullUrl() . "login.php");
    exit();
}

// Fetch user data
$currentUser = getCurrentUser();
$siteSettings = getSiteSettings();

// Include the header
include(__DIR__ . "/includes/inc-header.php");

// Initialize variables
$error = '';
$success = '';

// Handle any dashboard-specific actions here
// For example, updating user profile, changing password, etc.
// Ensure to include CSRF protection and input validation as needed

?>
<main class="container mx-auto my-12 px-4 flex-grow">
    <div class="max-w-lg mx-auto bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Welcome to <?= htmlspecialchars($siteSettings['site_name'] ?? 'Your Dashboard', ENT_QUOTES, 'UTF-8'); ?>!</h1>

        <!-- Success Alert -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <p class="mb-4">
            Hello, 
            <?= isset($currentUser['username']) ? htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') : 'Guest'; ?>!
        </p>
        <p class="mb-4">This is your dashboard. Here you can manage your account and access exclusive features.</p>

        <!-- Add more dashboard features as needed -->
        <!-- For example, recent activity, account settings, etc. -->
    </div>
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>