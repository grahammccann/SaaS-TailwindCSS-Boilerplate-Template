<?php
// File: dashboard.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header("Location: " . fullUrl() . "login/");
    exit();
}

$db           = DB::getInstance();
$currentUser  = getCurrentUser();
$siteSettings = getSiteSettings();

$error        = '';
$success      = '';
$showChangePw = isset($_POST['show_change_password']) || isset($_POST['change_password']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Trim inputs
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword     = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (!password_verify($currentPassword, $currentUser['password'])) {
        $error = "Your current password is incorrect.";
    } elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation do not match.";
    } else {
        // Update password
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->update('users', 'id', $currentUser['id'], [
            'password'   => $hashed,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $success      = "Password updated successfully.";
        $showChangePw = false;
        $currentUser  = getCurrentUser();
    }
}

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
  <div class="w-full max-w-3xl bg-white p-8 shadow-lg rounded-lg">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">
      Welcome to <?= htmlspecialchars($siteSettings['site_name'] ?? 'Your Dashboard', ENT_QUOTES) ?>!
    </h1>

    <!-- Success / Error Alerts -->
    <?php if ($success): ?>
      <div class="flex items-center p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg mb-6">
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <span><?= htmlspecialchars($success, ENT_QUOTES) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="flex items-center p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <span><?= htmlspecialchars($error, ENT_QUOTES) ?></span>
      </div>
    <?php endif; ?>

    <p class="mb-4 text-gray-700">
      Hello, <strong><?= htmlspecialchars($currentUser['username'] ?? 'Guest', ENT_QUOTES) ?></strong>!
    </p>
    <p class="mb-6 text-gray-600">
      This is your dashboard. From here, you can manage your account.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Change Password Card -->
      <form method="POST">
        <input type="hidden" name="show_change_password" value="1">
        <button type="submit"
                class="w-full text-left p-6 bg-indigo-50 border border-indigo-200 rounded-lg shadow hover:shadow-md hover:bg-indigo-100 transition">
          <div class="flex items-center space-x-4">
            <i class="fas fa-key text-indigo-600 text-2xl"></i>
            <div>
              <h3 class="text-lg font-semibold text-indigo-700">Change Password</h3>
              <p class="text-sm text-indigo-600">Update your account credentials</p>
            </div>
          </div>
        </button>
      </form>

      <!-- Logout Card -->
      <a href="<?= fullUrl() ?>logout/"
         class="block p-6 bg-red-50 border border-red-200 rounded-lg shadow hover:shadow-md hover:bg-red-100 transition">
        <div class="flex items-center space-x-4">
          <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
          <div>
            <h3 class="text-lg font-semibold text-red-700">Logout</h3>
            <p class="text-sm text-red-600">End your session securely</p>
          </div>
        </div>
      </a>
    </div>

    <?php if ($showChangePw): ?>
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-lock mr-2 text-indigo-600"></i> Change Your Password
        </h2>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="change_password" value="1">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
            <input
              type="password"
              name="current_password"
              required
              class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input
              type="password"
              name="new_password"
              required
              class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
            <input
              type="password"
              name="confirm_password"
              required
              class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
          </div>

          <div class="flex justify-end space-x-2 pt-2">
            <a href="<?= fullUrl() ?>dashboard/"
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
              Cancel
            </a>
            <button type="submit"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition flex items-center">
              <i class="fas fa-save mr-1"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>