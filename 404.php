<?php
// File: 404.php

// Send 404 header
http_response_code(404);

// Include your header (nav, styles, scripts)
require_once(__DIR__ . '/includes/inc-header.php');
?>

<main class="bg-gray-100 min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white p-10 shadow-2xl rounded-2xl text-center">
    <h1 class="text-6xl font-extrabold text-indigo-600 mb-4">
      <i class="fas fa-exclamation-triangle"></i> 404
    </h1>
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
      Page Not Found
    </h2>
    <p class="text-gray-600 mb-6">
      Sorry, we couldn’t find the page you were looking for.
    </p>
    <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8') ?>"
       class="inline-flex items-center bg-indigo-600 text-white px-6 py-3 rounded-lg shadow hover:bg-indigo-700 transition">
      <i class="fas fa-home mr-2"></i> Go Back Home
    </a>
  </div>
</main>

<?php
// Include your footer (scripts, closing tags)
require_once(__DIR__ . '/includes/inc-footer.php');
?>