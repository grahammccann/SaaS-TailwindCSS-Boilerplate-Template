<?php
// File: verify-email.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (!empty($token)) {
    $db = DB::getInstance();
    $user = $db->selectOneByField('users', 'verification_token', $token);

    if ($user) {
        // Activate the user's account
        $db->update('users', 'id', $user['id'], [
            'is_active' => 1,
            'verification_token' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $success = "Your email has been verified successfully. You can now log in.";
    } else {
        $error = "Invalid or expired verification token.";
    }
} else {
    $error = "No verification token provided.";
}

// Include the header
include(__DIR__ . "/includes/inc-header.php");
?>

<!-- Main Content -->
<main class="container mx-auto my-12 px-4">
    <div class="max-w-md mx-auto bg-white p-8 shadow-lg rounded-lg text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Email Verification</h1>

        <!-- Success and Error Messages -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 inline-block" role="alert">
                <i class="fas fa-check-circle mr-2 text-lg"></i>
                <span class="block sm:inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <p class="mt-4">
                <a href="<?= htmlspecialchars(fullUrl() . 'login.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-indigo-600 hover:text-indigo-800">Click here to log in.</a>
            </p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 inline-block" role="alert">
                <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <p class="mt-4">
                <a href="<?= htmlspecialchars(fullUrl() . 'signup.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-indigo-600 hover:text-indigo-800">Click here to sign up.</a>
            </p>
        <?php endif; ?>
    </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>