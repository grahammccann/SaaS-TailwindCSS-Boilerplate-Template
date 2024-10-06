<?php
// File: login.php

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

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Sanitize input
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);

        // Validate input
        if (!$email || !$password) {
            $error = "Please enter both email and password.";
        } else {
            $db = DB::getInstance();
            $user = $db->selectOneByField('users', 'email', $email);

            if ($user) {
                // Check if the account is active
                if (!$user['is_active']) {
                    $error = "Your account is not active. Please verify your email or contact support.";
                } elseif (password_verify($password, $user['password'])) {
                    // Password is correct, set session variables
                    // Regenerate session ID to prevent session fixation
                    if (!headers_sent()) {
                        session_regenerate_id(true);
                    } else {
                        error_log('Cannot regenerate session ID; headers already sent.');
                    }

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect to the dashboard
                    header("Location: " . fullUrl() . "dashboard.php");
                    exit();
                } else {
                    // Incorrect password
                    $error = "Login failed. Please check your credentials.";
                }
            } else {
                // User not found
                $error = "Login failed. Please check your credentials.";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Include the header after handling any redirects
include(__DIR__ . "/includes/inc-header.php");
?>

<!-- Main Content -->
<main class="container mx-auto my-12 px-4">
    <div class="max-w-md mx-auto bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Login</h1>

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
                <label for="email" class="block text-gray-700 font-bold mb-2">Email:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                    required
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
                >
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                    required
                >
            </div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <input type="checkbox" id="remember_me" name="remember_me" class="mr-2 leading-tight">
                    <label for="remember_me" class="text-sm text-gray-600">Remember Me</label>
                </div>
                <div>
                    <a href="<?= htmlspecialchars(fullUrl() . 'forgot-password.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-sm text-indigo-600 hover:text-indigo-800">Forgot Password?</a>
                </div>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-gray-600">
            Don't have an account? 
            <a href="<?= htmlspecialchars(fullUrl() . 'signup.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-indigo-600 hover:text-indigo-800">Sign up here</a>.
        </p>
    </div>
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>