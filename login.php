<?php
// File: login.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged‐in users
if (isLoggedIn()) {
    header("Location: " . fullUrl() . "dashboard/");
    exit();
}

$error      = '';
$success    = '';
$csrf_token = generateCsrfToken();

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);

        if (!$email || !$password) {
            $error = "Please enter both email and password.";
        } else {
            $db   = DB::getInstance();
            $user = $db->selectOneByField('users', 'email', $email);

            if ($user) {
                if (!$user['is_active']) {
                    $error = "Your account is not active. Please verify your email or contact support.";
                } elseif (password_verify($password, $user['password'])) {
                    if (!headers_sent()) session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email']   = $user['email'];
                    $_SESSION['role']    = $user['role'];
                    header("Location: " . fullUrl() . "dashboard/");
                    exit();
                } else {
                    $error = "Login failed. Please check your credentials.";
                }
            } else {
                $error = "Login failed. Please check your credentials.";
            }
        }
    }
}

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white p-10 shadow-2xl rounded-2xl">

    <!-- Header with accent underline -->
    <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-10 pb-3 border-b-4 border-indigo-500">
      <i class="fas fa-lock text-indigo-500 mr-2"></i>Login
    </h1>

    <!-- Success / Error Alerts -->
    <?php if ($success): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2 text-green-500"></i>
        <?= htmlspecialchars($success, ENT_QUOTES) ?>
      </div>
    <?php elseif ($error): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
        <?= htmlspecialchars($error, ENT_QUOTES) ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="" method="POST" class="space-y-6">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">

      <div class="relative">
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
          <i class="fas fa-envelope"></i>
        </span>
        <input
          type="email"
          name="email"
          placeholder="Email address"
          required
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
        />
      </div>

      <div class="relative">
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
          <i class="fas fa-key"></i>
        </span>
        <input
          type="password"
          name="password"
          placeholder="Password"
          required
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        />
      </div>

      <div class="flex items-center justify-between text-sm">
        <label class="inline-flex items-center text-gray-600">
          <input type="checkbox" name="remember_me" class="mr-2"> Remember Me
        </label>
        <a href="<?= fullUrl() . 'forgot-password/'; ?>" class="text-indigo-600 hover:text-indigo-800">
          Forgot Password?
        </a>
      </div>

      <button
        type="submit"
        class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center gap-2"
      >
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>

    <p class="mt-6 text-center text-gray-600">
      Don't have an account?
      <a href="<?= fullUrl() . 'signup/'; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
        Sign up here
      </a>.
    </p>
  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>