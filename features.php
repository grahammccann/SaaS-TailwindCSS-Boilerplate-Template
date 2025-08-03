<?php

// File: features.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core files
require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Fetch site settings
$siteSettings = getSiteSettings();
$siteName = $siteSettings['site_name'] ?? 'Our SaaS Platform';

// Include the shared header
include(__DIR__ . "/includes/inc-header.php");
?>

<main class="container mx-auto my-12 px-4">
    <section class="max-w-4xl mx-auto bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Features of <?= htmlspecialchars($siteName) ?></h1>

        <!-- Flash messages -->
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

        <!-- Feature List -->
        <div class="space-y-6">
            <div class="border-b pb-4">
                <h2 class="text-xl font-semibold text-indigo-700"><i class="fas fa-cogs mr-2"></i>Customisable Dashboard</h2>
                <p class="text-gray-600 mt-2">Tailor your workspace to fit your workflow and priorities, all from a clean, intuitive interface.</p>
            </div>

            <div class="border-b pb-4">
                <h2 class="text-xl font-semibold text-indigo-700"><i class="fas fa-shield-alt mr-2"></i>Secure Authentication</h2>
                <p class="text-gray-600 mt-2">Your data is protected with modern encryption and secure session handling across all devices.</p>
            </div>

            <div class="border-b pb-4">
                <h2 class="text-xl font-semibold text-indigo-700"><i class="fas fa-clock mr-2"></i>Real-Time Sync</h2>
                <p class="text-gray-600 mt-2">Experience instant updates across users and devices, no refresh required.</p>
            </div>

            <div class="border-b pb-4">
                <h2 class="text-xl font-semibold text-indigo-700"><i class="fas fa-users mr-2"></i>Multi-User Collaboration</h2>
                <p class="text-gray-600 mt-2">Invite your team and collaborate in real time with roles and permissions to match your structure.</p>
            </div>

            <div class="border-b pb-4">
                <h2 class="text-xl font-semibold text-indigo-700"><i class="fas fa-chart-line mr-2"></i>Insightful Analytics</h2>
                <p class="text-gray-600 mt-2">Track engagement, usage trends, and key performance metrics with our built-in analytics tools.</p>
            </div>
        </div>

        <!-- Optional CTA -->
        <div class="mt-10 text-center">
            <a href="<?= fullUrl() . 'signup/'; ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded shadow transition duration-200">
                Get Started Now
            </a>
        </div>
    </section>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>
