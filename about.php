<?php

// File: about.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

$siteSettings = getSiteSettings();
$siteName = $siteSettings['site_name'] ?? 'Our SaaS Platform';

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="container mx-auto my-12 px-4">
    <section class="max-w-3xl mx-auto bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">About <?= htmlspecialchars($siteName) ?></h1>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <i class="fas fa-check-circle mr-2 text-lg"></i>
                <span class="block sm:inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <div class="text-gray-700 leading-relaxed space-y-4">
            <p><?= htmlspecialchars($siteName) ?> is built to simplify your workflow, enhance productivity, and support your business growth. Whether you're a startup or an established company, we provide the tools you need to thrive.</p>

            <p>Our mission is to offer a streamlined, user-friendly platform that adapts to your needs — no matter your industry or goals.</p>

            <p>We're constantly improving our services based on your feedback, and we’re committed to transparency, simplicity, and long-term support.</p>
        </div>

        <div class="mt-8 text-center">
            <a href="<?= fullUrl() ?>features/" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded shadow transition duration-200">
                Explore Our Features
            </a>
        </div>
    </section>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>