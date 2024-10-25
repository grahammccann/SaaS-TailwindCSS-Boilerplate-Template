<?php
// File: signup.php

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

// Handle signup logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Sanitize and validate input
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($username) < 3) {
            $error = "Username must be at least 3 characters long.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        }

        if (empty($error)) {
            $db = DB::getInstance();

            // Check if email already exists
            $existingUser = $db->selectOneByField('users', 'email', $email);
            if ($existingUser) {
                $error = "An account with this email already exists.";
            } else {
                // Check if username already exists
                $existingUsername = $db->selectOneByField('users', 'username', $username);
                if ($existingUsername) {
                    $error = "Username is already taken.";
                } else {
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Determine user role: 'admin' if first user, else 'user'
                    $userCount = $db->count('users');
                    $role = ($userCount === 0) ? 'admin' : 'user';

                    // Generate a verification token for regular users
                    $verification_token = bin2hex(random_bytes(32));

                    // Set is_active: auto-activate admin, deactivate regular users
                    $is_active = ($role === 'admin') ? 1 : 0;

                    // Insert the new user into the database
                    $inserted = $db->insert('users', [
                        'email' => $email,
                        'username' => $username,
                        'password' => $hashedPassword,
                        'role' => $role,
                        'verification_token' => $verification_token,
                        'is_active' => $is_active,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if ($inserted) {
                        if ($role === 'admin') {
                            // Log the admin user in immediately
                            $_SESSION['user_id'] = $inserted;
                            $_SESSION['email'] = $email;
                            $_SESSION['role'] = $role;

                            // Regenerate session ID to prevent session fixation
                            if (!headers_sent()) {
                                session_regenerate_id(true);
                            } else {
                                error_log('Cannot regenerate session ID; headers already sent.');
                            }

                            // Redirect to the dashboard
                            header("Location: " . fullUrl() . "dashboard.php");
                            exit();
                        } else {
                            // Send verification email to regular user
                            sendVerificationEmail($email, $verification_token);

                            // Show success message
                            $success = "Registration successful! Please check your email to verify your account.";
                        }
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
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
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Signup</h1>

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
                <label for="username" class="block text-gray-700 font-bold mb-2">Username:</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                    required
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : '' ?>"
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
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 font-bold mb-2">Confirm Password:</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                    required
                >
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition focus:outline-none focus:ring-2 focus:ring-green-500">
                Signup
            </button>
        </form>

        <p class="mt-4 text-center text-gray-600">
            Already have an account?
            <a href="<?= htmlspecialchars(fullUrl() . 'login.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-indigo-600 hover:text-indigo-800">Login here</a>.
        </p>
    </div>
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>