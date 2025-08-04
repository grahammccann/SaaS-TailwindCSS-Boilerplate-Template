<?php
// File: settings.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isAdmin()) {
    header("Location: " . fullUrl() . "login.php");
    exit();
}

$siteSettings = getSiteSettings();
$error        = '';
$success      = '';
$csrf_token   = generateCsrfToken();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        if (isset($_POST['generate_sitemap'])) {
            try {
                generateSitemapAndRobots(fullUrl(), $_SERVER['DOCUMENT_ROOT']);
                $success = "Sitemap and robots.txt generated successfully.";
            } catch (Exception $e) {
                $error = "Failed to generate sitemap: " . $e->getMessage();
            }
        } elseif (isset($_POST['save_settings'])) {
            $data = [
                'site_name'                   => $_POST['site_name'] ?? '',
                'site_icon'                   => $_POST['site_icon'] ?? '',
                'contact_email'               => $_POST['contact_email'] ?? '',
                'price_gbp'                   => $_POST['price_gbp'] ?? 0,
                'recaptcha_site_key'          => $_POST['recaptcha_site_key'] ?? '',
                'recaptcha_secret_key'        => $_POST['recaptcha_secret_key'] ?? '',
                'stripe_mode'                 => $_POST['stripe_mode'] ?? 'test',
                'stripe_test_secret_key'      => $_POST['stripe_test_secret_key'] ?? '',
                'stripe_test_publishable_key' => $_POST['stripe_test_publishable_key'] ?? '',
                'stripe_live_secret_key'      => $_POST['stripe_live_secret_key'] ?? '',
                'stripe_live_publishable_key' => $_POST['stripe_live_publishable_key'] ?? '',
                'facebook_link'               => $_POST['facebook_link'] ?? '',
                'x_link'                      => $_POST['x_link'] ?? '',
                'instagram_link'              => $_POST['instagram_link'] ?? '',
                'home_meta_title'             => $_POST['home_meta_title'] ?? '',
                'home_meta_description'       => $_POST['home_meta_description'] ?? ''
            ];

            $db      = DB::getInstance();
            $updated = $db->update('settings', 'id', 1, $data);

            if ($updated) {
                $success      = "Settings updated successfully.";
                $siteSettings = getSiteSettings();
            } else {
                $error = "No changes made or update failed.";
            }
        }
    }
}

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-screen py-12 px-4">
  <div class="max-w-5xl mx-auto">

    <!-- Page Header + Sitemap Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
      <h1 class="text-3xl font-semibold text-gray-800 flex items-center">
        <i class="fas fa-cogs text-indigo-600 mr-2"></i>
        Settings
      </h1>
      <form method="post" class="w-full sm:w-auto">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <button
          type="submit"
          name="generate_sitemap"
          class="inline-flex items-center bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition"
        >
          <i class="fas fa-sitemap mr-2"></i>
          Generate Sitemap & Robots
        </button>
      </form>
    </div>
    <hr class="border-t-2 border-gray-200 mb-8">

    <?php renderAlerts($success, $error); ?>

    <!-- Settings Form -->
    <form method="post" class="bg-white rounded-xl shadow-lg divide-y divide-gray-200 overflow-hidden">
      <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
      <input type="hidden" name="save_settings" value="1">

      <!-- General Settings -->
      <section class="p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
          <i class="fas fa-globe mr-2 text-indigo-500"></i>
          General Settings
        </h2>
        <hr class="border-t-2 border-gray-200 mb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-600">Site Name</label>
            <input type="text" name="site_name"
                   value="<?= e($siteSettings['site_name']) ?>"
                   required
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Site Icon</label>
            <input type="text" name="site_icon"
                   value="<?= e($siteSettings['site_icon']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Contact Email</label>
            <input type="email" name="contact_email"
                   value="<?= e($siteSettings['contact_email']) ?>"
                   required
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Claim Fee (£ GBP)</label>
            <input type="number" step="0.01" name="price_gbp"
                   value="<?= e($siteSettings['price_gbp']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
        </div>
      </section>

      <!-- reCAPTCHA -->
      <section class="p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
          <i class="fas fa-shield-alt mr-2 text-indigo-500"></i>
          reCAPTCHA
        </h2>
        <hr class="border-t-2 border-gray-200 mb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-600">Site Key</label>
            <input type="text" name="recaptcha_site_key"
                   value="<?= e($siteSettings['recaptcha_site_key']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Secret Key</label>
            <input type="text" name="recaptcha_secret_key"
                   value="<?= e($siteSettings['recaptcha_secret_key']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
        </div>
      </section>

      <!-- Stripe Configuration -->
      <section class="p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
          <i class="fab fa-stripe-s mr-2 text-indigo-500"></i>
          Stripe Configuration
        </h2>
        <hr class="border-t-2 border-gray-200 mb-6">
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-600">Stripe Mode</label>
          <select name="stripe_mode"
                  class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="test" <?= $siteSettings['stripe_mode'] === 'test' ? 'selected' : '' ?>>Test</option>
            <option value="live" <?= $siteSettings['stripe_mode'] === 'live' ? 'selected' : '' ?>>Live</option>
          </select>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-600">Test Secret Key</label>
            <input type="text" name="stripe_test_secret_key"
                   value="<?= e($siteSettings['stripe_test_secret_key']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Test Publishable Key</label>
            <input type="text" name="stripe_test_publishable_key"
                   value="<?= e($siteSettings['stripe_test_publishable_key']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Live Secret Key</label>
            <input type="text" name="stripe_live_secret_key"
                   value="<?= e($siteSettings['stripe_live_secret_key']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Live Publishable Key</label>
            <input type="text" name="stripe_live_publishable_key"
                   value="<?= e($siteSettings['stripe_live_publishable_key']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
        </div>
      </section>

      <!-- Social Media -->
      <section class="p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
          <i class="fas fa-share-alt mr-2 text-indigo-500"></i>
          Social Media
        </h2>
        <hr class="border-t-2 border-gray-200 mb-6">
        <div class="grid md:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-600">Facebook URL</label>
            <input type="url" name="facebook_link"
                   value="<?= e($siteSettings['facebook_link']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">X (Twitter) URL</label>
            <input type="url" name="x_link"
                   value="<?= e($siteSettings['x_link']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600">Instagram URL</label>
            <input type="url" name="instagram_link"
                   value="<?= e($siteSettings['instagram_link']) ?>"
                   class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
        </div>
      </section>

      <!-- SEO Meta -->
      <section class="p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
          <i class="fas fa-search mr-2 text-indigo-500"></i>
          SEO Meta Data
        </h2>
        <hr class="border-t-2 border-gray-200 mb-6">
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-600">Homepage Meta Title</label>
          <input type="text" name="home_meta_title"
                 value="<?= e($siteSettings['home_meta_title']) ?>"
                 class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus;border-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600">Homepage Meta Description</label>
          <textarea name="home_meta_description" rows="4"
                    class="mt-1 w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:ring-indigo-500 focus;border-indigo-500"><?= e($siteSettings['home_meta_description']) ?></textarea>
        </div>
      </section>

      <!-- Submit -->
      <div class="px-8 py-6 bg-gray-50 text-right">
        <button type="submit"
                class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-6 py-3 rounded-md shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <i class="fas fa-save mr-2"></i>
          Save Changes
        </button>
      </div>
    </form>

  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>