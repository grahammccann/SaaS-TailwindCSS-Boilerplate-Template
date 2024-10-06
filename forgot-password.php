<?php
// File: forgot-password.php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $db = DB::getInstance();
            $user = $db->selectOneByField('users', 'email', $email);

            if ($user) {
                // Generate a password reset token
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Insert or update the reset token in the database
                $existingReset = $db->selectOneByField('password_resets', 'user_id', $user['id']);

                if ($existingReset) {
                    // Update existing token
                    $db->update('password_resets', 'user_id', $user['id'], [
                        'token' => $reset_token,
                        'expires_at' => $expires_at
                    ]);
                } else {
                    // Insert new token
                    $db->insert('password_resets', [
                        'user_id' => $user['id'],
                        'token' => $reset_token,
                        'expires_at' => $expires_at
                    ]);
                }

                // Send password reset email
                sendPasswordResetEmail($email, $reset_token);

                $success = "A password reset link has been sent to your email.";
            } else {
                $error = "No account found with that email address.";
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
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Forgot Password</h1>

        <!-- Success and Error Messages Inside the Form Container -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email Address:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
                >
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                Send Reset Link
            </button>
        </form>

        <p class="mt-4 text-center text-gray-600">
            Remembered your password?
            <a href="<?= htmlspecialchars(fullUrl() . 'login.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-indigo-600 hover:text-indigo-800">Login here</a>.
        </p>
    </div>
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>