<?php
// File: reports.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect non-admin users
if (!isAdmin()) {
    header("Location: " . fullUrl() . "login/");
    exit();
}

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-screen py-12 px-4">
  <div class="max-w-5xl mx-auto">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-3xl font-semibold text-gray-800 flex items-center">
        <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
        Reports
      </h1>
    </div>
    <hr class="border-t-2 border-gray-200 mb-8">

    <!-- Summary Reports -->
    <section class="mb-8">
      <h2 class="text-2xl font-semibold text-gray-800 mb-2 flex items-center">
        <i class="fas fa-chart-pie text-gray-600 mr-2"></i>
        Summary Reports
      </h2>
      <hr class="border-t-2 border-gray-200 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- User Activity -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">User Activity</h3>
            <p class="text-gray-600 mb-4">Overview of recent user activity on the platform.</p>
            <button
              class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-4 py-2 rounded-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <i class="fas fa-eye mr-2"></i> View Report
            </button>
          </div>
        </div>

        <!-- Sales Report -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Sales Report</h3>
            <p class="text-gray-600 mb-4">Track the platform's sales and revenue over time.</p>
            <button
              class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-4 py-2 rounded-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <i class="fas fa-dollar-sign mr-2"></i> View Report
            </button>
          </div>
        </div>

        <!-- System Performance -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">System Performance</h3>
            <p class="text-gray-600 mb-4">Monitor the performance and uptime of your system.</p>
            <button
              class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-4 py-2 rounded-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <i class="fas fa-server mr-2"></i> View Report
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Detailed Reports -->
    <section class="mb-8">
      <h2 class="text-2xl font-semibold text-gray-800 mb-2 flex items-center">
        <i class="fas fa-list-alt text-gray-600 mr-2"></i>
        Detailed Reports
      </h2>
      <hr class="border-t-2 border-gray-200 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Subscription Growth -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Subscription Growth</h3>
            <p class="text-gray-600 mb-4">Track monthly subscription growth on the platform.</p>
            <button
              class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium text-sm px-4 py-2 rounded-md transition focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              <i class="fas fa-chart-line mr-2"></i> View Report
            </button>
          </div>
        </div>

        <!-- User Retention -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">User Retention</h3>
            <p class="text-gray-600 mb-4">Analyze the platform's user retention rates over time.</p>
            <button
              class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium text-sm px-4 py-2 rounded-md transition focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              <i class="fas fa-chart-bar mr-2"></i> View Report
            </button>
          </div>
        </div>
      </div>
    </section>

  </div>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>