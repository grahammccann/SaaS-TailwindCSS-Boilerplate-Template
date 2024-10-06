<?php
// File: settings.php
?>

<?php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is admin
if (!isAdmin()) {
    header("Location: " . fullUrl() . "login.php");
    exit();
}

$siteSettings = getSiteSettings();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Sanitize and validate input
        $site_name = trim($_POST['site_name']);
        $contact_email = filter_var(trim($_POST['contact_email']), FILTER_VALIDATE_EMAIL);
        $recaptcha_site_key = trim($_POST['recaptcha_site_key']);
        $recaptcha_secret_key = trim($_POST['recaptcha_secret_key']);
        $price_gbp = trim($_POST['price_gbp']);
        $stripe_mode = in_array($_POST['stripe_mode'], ['live', 'test']) ? $_POST['stripe_mode'] : 'test';
        $site_icon = trim($_POST['site_icon']);

        // New Stripe Keys
        $stripe_test_secret_key = trim($_POST['stripe_test_secret_key']);
        $stripe_test_publishable_key = trim($_POST['stripe_test_publishable_key']);
        $stripe_live_secret_key = trim($_POST['stripe_live_secret_key']);
        $stripe_live_publishable_key = trim($_POST['stripe_live_publishable_key']);

        // New social media links
        $facebook_link = trim($_POST['facebook_link']);
        $twitter_link = trim($_POST['twitter_link']);
        $instagram_link = trim($_POST['instagram_link']);

        // Validate required fields
        if (empty($site_name)) {
            $error = "Site name cannot be empty.";
        } elseif (!$contact_email) {
            $error = "Please enter a valid contact email.";
        } elseif (!is_numeric($price_gbp) || $price_gbp < 0) {
            $error = "Please enter a valid price in GBP.";
        }

        if (empty($error)) {
            $db = DB::getInstance();

            // Prepare data to update
            $updateData = [
                'site_name' => $site_name,
                'contact_email' => $contact_email,
                'recaptcha_site_key' => $recaptcha_site_key,
                'recaptcha_secret_key' => $recaptcha_secret_key,
                'price_gbp' => $price_gbp,
                'stripe_mode' => $stripe_mode,
                'site_icon' => $site_icon,
                'facebook_link' => $facebook_link,
                'twitter_link' => $twitter_link,
                'instagram_link' => $instagram_link,
            ];

            // Only update Stripe keys if new values are provided
            if (!empty($stripe_test_secret_key)) {
                $updateData['stripe_test_secret_key'] = $stripe_test_secret_key;
            }

            if (!empty($stripe_test_publishable_key)) {
                $updateData['stripe_test_publishable_key'] = $stripe_test_publishable_key;
            }

            if (!empty($stripe_live_secret_key)) {
                $updateData['stripe_live_secret_key'] = $stripe_live_secret_key;
            }

            if (!empty($stripe_live_publishable_key)) {
                $updateData['stripe_live_publishable_key'] = $stripe_live_publishable_key;
            }

            // Update settings in the database
            $updated = $db->update("settings", "id", 1, $updateData);

            if ($updated) {
                $success = "Settings updated successfully.";
                // Refresh site settings
                $siteSettings = getSiteSettings();
            } else {
                $error = "Failed to update settings.";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Include the header
include(__DIR__ . "/includes/inc-header.php");
?>

<main class="container mx-auto my-12 px-4">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Site Settings</h1>

    <form action="" method="POST" class="max-w-lg mx-auto bg-white p-8 shadow-lg rounded-lg">
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

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Site Name Field -->
        <div class="mb-4">
            <label for="site_name" class="block text-gray-700 font-bold mb-2">Site Name:</label>
            <input 
                type="text" 
                id="site_name" 
                name="site_name" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                required
                value="<?= htmlspecialchars($siteSettings['site_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <!-- Site Icon Field -->
        <div class="mb-4">
            <label for="site_icon" class="block text-gray-700 font-bold mb-2">Site Icon (FontAwesome Icon Class):</label>
            <input 
                type="text" 
                id="site_icon" 
                name="site_icon" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['site_icon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <!-- Social Media Links Fields -->
        <div class="mb-4">
            <label for="facebook_link" class="block text-gray-700 font-bold mb-2">Facebook URL:</label>
            <input 
                type="url" 
                id="facebook_link" 
                name="facebook_link" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['facebook_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="mb-4">
            <label for="twitter_link" class="block text-gray-700 font-bold mb-2">Twitter URL:</label>
            <input 
                type="url" 
                id="twitter_link" 
                name="twitter_link" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['twitter_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="mb-4">
            <label for="instagram_link" class="block text-gray-700 font-bold mb-2">Instagram URL:</label>
            <input 
                type="url" 
                id="instagram_link" 
                name="instagram_link" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['instagram_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <!-- Recaptcha and Price Fields -->
        <div class="mb-4">
            <label for="recaptcha_site_key" class="block text-gray-700 font-bold mb-2">reCAPTCHA Site Key:</label>
            <input 
                type="text" 
                id="recaptcha_site_key" 
                name="recaptcha_site_key" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['recaptcha_site_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="mb-4">
            <label for="recaptcha_secret_key" class="block text-gray-700 font-bold mb-2">reCAPTCHA Secret Key:</label>
            <input 
                type="text" 
                id="recaptcha_secret_key" 
                name="recaptcha_secret_key" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['recaptcha_secret_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="mb-4">
            <label for="price_gbp" class="block text-gray-700 font-bold mb-2">Price (GBP):</label>
            <input 
                type="number" 
                step="0.01"
                id="price_gbp" 
                name="price_gbp" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                required
                value="<?= htmlspecialchars($siteSettings['price_gbp'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <!-- Stripe Mode Field -->
        <div class="mb-6">
            <label for="stripe_mode" class="block text-gray-700 font-bold mb-2">Stripe Mode:</label>
            <select 
                id="stripe_mode" 
                name="stripe_mode" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                required
            >
                <option value="live" <?= (isset($siteSettings['stripe_mode']) && $siteSettings['stripe_mode'] === 'live') ? 'selected' : '' ?>>Live</option>
                <option value="test" <?= (isset($siteSettings['stripe_mode']) && $siteSettings['stripe_mode'] === 'test') ? 'selected' : '' ?>>Test</option>
            </select>
        </div>

        <!-- New Stripe Keys Fields -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">Stripe API Keys</h2>
        <div class="mb-4">
            <label for="stripe_test_secret_key" class="block text-gray-700 font-bold mb-2">Stripe Test Secret Key:</label>
            <input 
                type="password" 
                id="stripe_test_secret_key" 
                name="stripe_test_secret_key" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter Test Secret Key"
            >
        </div>
        <div class="mb-4">
            <label for="stripe_test_publishable_key" class="block text-gray-700 font-bold mb-2">Stripe Test Publishable Key:</label>
            <input 
                type="text" 
                id="stripe_test_publishable_key" 
                name="stripe_test_publishable_key" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['stripe_test_publishable_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="mb-4">
            <label for="stripe_live_secret_key" class="block text-gray-700 font-bold mb-2">Stripe Live Secret Key:</label>
            <input 
                type="password" 
                id="stripe_live_secret_key" 
                name="stripe_live_secret_key" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter Live Secret Key"
            >
        </div>
        <div class="mb-4">
            <label for="stripe_live_publishable_key" class="block text-gray-700 font-bold mb-2">Stripe Live Publishable Key:</label>
            <input 
                type="text" 
                id="stripe_live_publishable_key" 
                name="stripe_live_publishable_key" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                value="<?= htmlspecialchars($siteSettings['stripe_live_publishable_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500">
            Update Settings
        </button>
    </form>
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>