<?php
// File: reset-password.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header("Location: " . fullUrl() . "dashboard/");
    exit();
}

$error   = '';
$success = '';
$token   = trim($_GET['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        $token            = $_POST['token'] ?? '';
        $password         = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $db = DB::getInstance();
            $resetEntry = $db->selectOne(
                "SELECT * FROM password_resets WHERE token = :token",
                [':token' => $token]
            );

            if ($resetEntry && strtotime($resetEntry['expires_at']) > time()) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $db->update('users', 'id', $resetEntry['user_id'], [
                    'password'   => $hashedPassword,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $db->delete('password_resets', 'user_id', $resetEntry['user_id']);

                $success = "Your password has been reset successfully. You can now
                            <a href='" . fullUrl() . "login/' class='text-indigo-600 underline'>
                              log in
                            </a>.";
            } else {
                $error = "Invalid or expired reset token.";
            }
        }
    }
}

$csrf_token = generateCsrfToken();

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="container mx-auto my-12 px-4">
  <div class="max-w-md mx-auto bg-white p-8 shadow-lg rounded-lg">

    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">
      Reset Password
    </h1>

    <!-- Success / Error Alerts -->
    <?php if ($success): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2 text-green-500"></i>
        <span><?= $success ?></span>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
        <span><?= htmlspecialchars($error, ENT_QUOTES) ?></span>
      </div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
      <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            New Password
          </label>
          <input
            type="password"
            id="password"
            name="password"
            required
            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
        </div>

        <div>
          <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
            Confirm New Password
          </label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            required
            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
        </div>

        <button
          type="submit"
          class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-md shadow transition focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          <i class="fas fa-key"></i>
          Reset Password
        </button>
      </form>
    <?php endif; ?>

  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>