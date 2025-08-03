<?php
// File: signup.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header("Location: " . fullUrl() . "dashboard/");
    exit();
}

$error      = '';
$success    = '';
$csrf_token = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        $email            = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $username         = trim($_POST['username']);
        $password         = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

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
            $db           = DB::getInstance();
            $existingUser = $db->selectOneByField('users', 'email', $email);

            if ($existingUser) {
                $error = "An account with this email already exists.";
            } else {
                $existingUsername = $db->selectOneByField('users', 'username', $username);
                if ($existingUsername) {
                    $error = "Username is already taken.";
                } else {
                    $hashedPassword     = password_hash($password, PASSWORD_DEFAULT);
                    $userCount          = $db->count('users');
                    $role               = ($userCount === 0) ? 'admin' : 'user';
                    $verification_token = bin2hex(random_bytes(32));
                    $is_active          = ($role === 'admin') ? 1 : 0;

                    $inserted = $db->insert('users', [
                        'email'              => $email,
                        'username'           => $username,
                        'password'           => $hashedPassword,
                        'role'               => $role,
                        'verification_token' => $verification_token,
                        'is_active'          => $is_active,
                        'created_at'         => date('Y-m-d H:i:s'),
                        'updated_at'         => date('Y-m-d H:i:s'),
                    ]);

                    if ($inserted) {
                        if ($role === 'admin') {
                            $_SESSION['user_id'] = $inserted;
                            $_SESSION['email']   = $email;
                            $_SESSION['role']    = $role;
                            session_regenerate_id(true);
                            header("Location: " . fullUrl() . "dashboard/");
                            exit();
                        } else {
                            sendVerificationEmail($email, $verification_token);
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

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white p-10 shadow-2xl rounded-2xl">
    <!-- Header with accent underline -->
    <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-10 pb-3 border-b-4 border-green-500">
      <i class="fas fa-user-plus text-green-500 mr-2"></i>Signup
    </h1>

    <!-- Alerts -->
    <?php if ($success): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <span><?= htmlspecialchars($success, ENT_QUOTES) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <span><?= htmlspecialchars($error, ENT_QUOTES) ?></span>
      </div>
    <?php endif; ?>

    <!-- Signup Form -->
    <form method="POST" action="" class="space-y-6">
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
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
        />
      </div>

      <div class="relative">
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
          <i class="fas fa-user"></i>
        </span>
        <input
          type="text"
          name="username"
          placeholder="Username"
          required
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES) ?>"
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
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
        />
      </div>

      <div class="relative">
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
          <i class="fas fa-key"></i>
        </span>
        <input
          type="password"
          name="confirm_password"
          placeholder="Confirm Password"
          required
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
        />
      </div>

      <button
        type="submit"
        class="w-full h-12 flex items-center justify-center gap-2 bg-gradient-to-r from-green-500 to-green-600
               text-white font-semibold rounded-lg shadow-lg hover:from-green-600 hover:to-green-700
               transition-colors focus:outline-none focus:ring-2 focus:ring-green-500"
      >
        <i class="fas fa-user-plus"></i> Signup
      </button>
    </form>

    <p class="mt-6 text-center text-gray-600">
      Already have an account?
      <a href="<?= fullUrl() . 'login/'; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
        Login here
      </a>.
    </p>
  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>