<?php
// File: verify-email.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error   = '';
$success = '';
$token   = trim($_GET['token'] ?? '');

if ($token) {
    $db   = DB::getInstance();
    $user = $db->selectOneByField('users', 'verification_token', $token);

    if ($user) {
        $db->update('users', 'id', $user['id'], [
            'is_active'          => 1,
            'verification_token' => null,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
        $success = "Your email has been verified successfully. You can now log in.";
    } else {
        $error = "Invalid or expired verification token.";
    }
} else {
    $error = "No verification token provided.";
}

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white p-10 shadow-2xl rounded-2xl">
    <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-10 pb-3 border-b-4 border-indigo-500">
      <i class="fas fa-envelope-open-text text-indigo-500 mr-2"></i>Email Verification
    </h1>

    <?php if ($success): ?>
      <div class="flex items-center p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg mb-6">
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <span><?= htmlspecialchars($success, ENT_QUOTES) ?></span>
      </div>
      <p class="text-center">
        <a href="<?= fullUrl() ?>login/" class="text-indigo-600 hover:text-indigo-800 font-medium">
          Click here to log in
        </a>
      </p>
    <?php else: ?>
      <div class="flex items-center p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <span><?= htmlspecialchars($error, ENT_QUOTES) ?></span>
      </div>
      <p class="text-center">
        <a href="<?= fullUrl() ?>signup/" class="text-indigo-600 hover:text-indigo-800 font-medium">
          Click here to sign up
        </a>
      </p>
    <?php endif; ?>
  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>