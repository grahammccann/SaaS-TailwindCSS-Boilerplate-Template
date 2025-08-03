<?php
// File: forgot-password.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users
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
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $db   = DB::getInstance();
            $user = $db->selectOneByField('users', 'email', $email);

            if ($user) {
                $reset_token = bin2hex(random_bytes(32));
                $expires_at  = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $existing = $db->selectOneByField('password_resets', 'user_id', $user['id']);
                if ($existing) {
                    $db->update('password_resets', 'user_id', $user['id'], [
                        'token'      => $reset_token,
                        'expires_at' => $expires_at
                    ]);
                } else {
                    $db->insert('password_resets', [
                        'user_id'    => $user['id'],
                        'token'      => $reset_token,
                        'expires_at' => $expires_at
                    ]);
                }

                sendPasswordResetEmail($email, $reset_token);
                $success = "A password reset link has been sent to your email.";
            } else {
                $error = "No account found with that email address.";
            }
        }
    }
}

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white p-10 shadow-2xl rounded-2xl">
    <!-- Header with accent underline -->
    <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-10 pb-3 border-b-4 border-blue-500">
      <i class="fas fa-envelope text-blue-500 mr-2"></i>Forgot Password
    </h1>

    <!-- Alerts -->
    <?php if ($success): ?>
      <div class="flex items-center mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
        <i class="fas fa-check-circle mr-3"></i><span><?= htmlspecialchars($success) ?></span>
      </div>
    <?php elseif ($error): ?>
      <div class="flex items-center mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
        <i class="fas fa-exclamation-circle mr-3"></i><span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="" class="space-y-6">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div class="relative">
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
          <i class="fas fa-envelope-open-text"></i>
        </span>
        <input
          type="email"
          name="email"
          placeholder="Email address"
          required
          class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
        />
      </div>

      <button
        type="submit"
        class="w-full h-12 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600
               text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-700
               transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <i class="fas fa-paper-plane"></i> Send Reset Link
      </button>
    </form>

    <!-- Footer link -->
    <p class="mt-6 text-center text-gray-600">
      Remembered your password?
      <a href="<?= fullUrl() . 'login/'; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
        Login here
      </a>.
    </p>
  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>
