<?php

// File: terms-of-service.php

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
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Terms of Service</h1>

        <p class="mb-4 text-gray-700">
            By using <?= htmlspecialchars($siteSettings['site_name'] ?? 'this website') ?>, you agree to the following terms and conditions. Please read them carefully before accessing or using our services.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">1. Use of the Site</h2>
        <p class="mb-4 text-gray-700">
            You agree to use this site for lawful purposes only. You must not use the site in any way that may cause damage, interfere with its availability, or restrict others from using it.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">2. Intellectual Property</h2>
        <p class="mb-4 text-gray-700">
            All content on this site, including text, logos, images, and graphics, is the property of <?= htmlspecialchars($siteSettings['site_name'] ?? 'our site') ?> unless otherwise stated. You may not copy or reuse any content without permission.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">3. User Submissions</h2>
        <p class="mb-4 text-gray-700">
            If you submit a review or suggestion, you grant us the right to use, edit, and display that content publicly. We reserve the right to remove submissions that violate our standards.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">4. External Links</h2>
        <p class="mb-4 text-gray-700">
            Our website may contain links to third-party sites. We are not responsible for their content, practices, or privacy policies.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">5. Limitation of Liability</h2>
        <p class="mb-4 text-gray-700">
            We do our best to provide accurate and up-to-date information. However, we make no guarantees and accept no liability for any errors, omissions, or loss arising from use of the site.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">6. Modifications</h2>
        <p class="mb-4 text-gray-700">
            We may update these terms at any time. Changes will take effect when posted on this page.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-6 mb-2">7. Contact</h2>
        <p class="mb-4 text-gray-700">
            If you have any questions about these terms, please <a href="<?= fullUrl() . 'contact/' ?>" class="text-indigo-600 underline">contact us</a>.
        </p>
    </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>