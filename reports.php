<?php
// File: reports.php

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

// Include the header
include(__DIR__ . "/includes/inc-header.php");

?>

<main class="container mx-auto my-12 px-4">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Reports Dashboard</h1>

    <section class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Summary Reports</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: User Activity Report -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-800">User Activity</h3>
                <p class="text-gray-600 mb-4">Overview of recent user activity on the platform.</p>
                <button class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">
                    View Report
                </button>
            </div>

            <!-- Card 2: Sales Report -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-800">Sales Report</h3>
                <p class="text-gray-600 mb-4">Track the platform's sales and revenue over time.</p>
                <button class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">
                    View Report
                </button>
            </div>

            <!-- Card 3: System Performance -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-800">System Performance</h3>
                <p class="text-gray-600 mb-4">Monitor the performance and uptime of your system.</p>
                <button class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">
                    View Report
                </button>
            </div>
        </div>
    </section>

    <section class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Detailed Reports</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card: Monthly Subscription Growth -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-800">Subscription Growth</h3>
                <p class="text-gray-600 mb-4">Track monthly subscription growth on the platform.</p>
                <button class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition">
                    View Report
                </button>
            </div>

            <!-- Card: User Retention Rates -->
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-800">User Retention</h3>
                <p class="text-gray-600 mb-4">Analyze the platform's user retention rates over time.</p>
                <button class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition">
                    View Report
                </button>
            </div>
        </div>
    </section>

    <!-- More Reports or Charts Can Be Added Here -->
</main>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>