<?php
// File: reset-password.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users to the dashboard
if (isLoggedIn()) {
    header("Location: " . fullUrl() . "dashboard.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        $token = $_POST['token'];
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($password) || strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $db = DB::getInstance();
            $resetEntry = $db->selectOne("SELECT * FROM password_resets WHERE token = ?", [$token]);

            if ($resetEntry && strtotime($resetEntry['expires_at']) > time()) {
                // Update user's password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $db->update('users', 'id', $resetEntry['user_id'], [
                    'password' => $hashedPassword,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Delete the password reset token
                $db->delete('password_resets', 'user_id', $resetEntry['user_id']);

                $success = "Your password has been reset successfully. You can now log in.";
            } else {
                $error = "Invalid or expired reset token.";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Include the header
include(__DIR__ . "/includes/inc-header.php");
?>

<!-- Main Content -->
<main class="container mx-auto my-12 px-4">
    <div class="max-w-md mx-auto bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Reset Password</h1>

        <!-- Success and Error Messages Inside the Form Container -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <i class="fas fa-check-circle mr-2 text-lg"></i>
                <span class="block sm:inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
            <form action="" method="POST">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-bold mb-2">New Password:</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                </div>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 font-bold mb-2">Confirm New Password:</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition focus:outline-none focus:ring-2 focus:ring-green-500">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>