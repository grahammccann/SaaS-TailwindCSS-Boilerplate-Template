<?php

// File: privacy-policy.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Fetch site settings
$siteSettings = getSiteSettings();

// Include the header
include(__DIR__ . "/includes/inc-header.php");
?>

<!-- Main Content -->
<main class="container mx-auto my-12 px-4">
    <div class="max-w-3xl mx-auto bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Privacy Policy</h1>

        <p class="mb-4 text-gray-700">
            Your privacy is important to us. This policy outlines how we collect, use, and protect your information when you visit <?= htmlspecialchars($siteSettings['site_name'] ?? 'our site') ?>.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">Information We Collect</h2>
        <p class="mb-4 text-gray-700">
            We may collect anonymous data such as browser type, device information, IP address, and general usage statistics. If you submit a review or contact us, we may collect personal information such as your name and email address.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">How We Use Your Information</h2>
        <p class="mb-4 text-gray-700">
            Your information helps us improve the user experience, respond to enquiries, and ensure our platform remains secure. We do not sell your data to third parties.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">Cookies</h2>
        <p class="mb-4 text-gray-700">
            This site may use cookies to enhance your experience. You can choose to disable cookies in your browser settings.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">Third-Party Links</h2>
        <p class="mb-4 text-gray-700">
            Occasionally, we may include links to third-party websites. We are not responsible for their privacy practices.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">Your Rights</h2>
        <p class="mb-4 text-gray-700">
            You can request access to, correction of, or deletion of your personal data by contacting us through the details on our 
            <a href="<?= fullUrl() . 'contact/'; ?>" class="text-indigo-600 underline">contact page</a>.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">Policy Updates</h2>
        <p class="mb-4 text-gray-700">
            We may update this privacy policy from time to time. The latest version will always be available on this page.
        </p>
    </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>