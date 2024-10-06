<?php
// File: inc-header.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/inc-db-connection.php');
require_once(__DIR__ . '/inc-functions.php');

$current_page = basename($_SERVER['PHP_SELF']);
$metadata = getPageMetaData("/" . $current_page);
$siteSettings = getSiteSettings();

$currentUser = getCurrentUser();

// Set default title and description if not set
$defaultTitle = $siteSettings['site_name'] ?? 'My SaaS Application';
$defaultDescription = $siteSettings['site_description'] ?? 'Welcome to our SaaS application.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Viewport Meta Tag for Mobile Responsiveness -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic Page Title and Description -->
    <title><?= htmlspecialchars($metadata['title'] ?? $defaultTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($metadata['description'] ?? $defaultDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <!-- SEO Meta Keywords (if any) -->
    <?php if (!empty($metadata['keywords'])): ?>
        <meta name="keywords" content="<?= htmlspecialchars($metadata['keywords'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars(fullUrl() . 'favicon.png', ENT_QUOTES, 'UTF-8'); ?>">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= htmlspecialchars(fullUrl() . 'css/style.css', ENT_QUOTES, 'UTF-8'); ?>">
    <script>
        tailwind.config = {
            theme: {
                extend: {}
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header using Semantic HTML -->
    <header>
        <!-- Navbar -->
        <nav class="bg-white shadow-lg">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <!-- Logo with Font Awesome Icon -->
                <div class="text-2xl font-bold text-indigo-600 flex items-center">
                    <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center">
                        <!-- Use the stored icon from settings -->
                        <i class="<?= htmlspecialchars($siteSettings['site_icon'] ?? 'fas fa-globe', ENT_QUOTES, 'UTF-8'); ?> mr-2"></i>
                        <?= htmlspecialchars($siteSettings['site_name'] ?? 'My SaaS Application', ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </div>
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" aria-label="Toggle navigation menu" class="block md:hidden text-gray-600 focus:outline-none">
                    <i class="fas fa-bars fa-2x"></i>
                </button>
                <!-- Desktop Navigation -->
                <div class="hidden md:flex space-x-4 items-center">
                    <?php if (!isLoggedIn()): ?>
                        <!-- Public Links (Visible Only to Guests) -->
                        <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8'); ?>" class="<?= htmlspecialchars(isActive('index.php'), ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-home mr-2"></i> Home
                        </a>
                        <a href="<?= htmlspecialchars(fullUrl() . 'about.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= htmlspecialchars(isActive('about.php'), ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-info-circle mr-2"></i> About
                        </a>
                        <a href="<?= htmlspecialchars(fullUrl() . 'features.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= htmlspecialchars(isActive('features.php'), ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-cogs mr-2"></i> Features
                        </a>
                        <a href="<?= htmlspecialchars(fullUrl() . 'contact.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= htmlspecialchars(isActive('contact.php'), ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-envelope mr-2"></i> Contact
                        </a>

                        <!-- Login & Signup Buttons -->
                        <button class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-2 px-4 rounded-md shadow-md hover:from-blue-600 hover:to-indigo-700 transition flex items-center"
                                onclick="window.location.href='<?= htmlspecialchars(fullUrl() . 'login.php', ENT_QUOTES, 'UTF-8'); ?>'">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                        <button class="bg-gradient-to-r from-green-400 to-green-600 text-white py-2 px-4 rounded-md shadow-md hover:from-green-500 hover:to-green-700 transition flex items-center"
                                onclick="window.location.href='<?= htmlspecialchars(fullUrl() . 'signup.php', ENT_QUOTES, 'UTF-8'); ?>'">
                            <i class="fas fa-user-plus mr-2"></i> Signup
                        </button>
                    <?php else: ?>
                        <!-- Logged-In User Links -->
                        <a href="<?= htmlspecialchars(fullUrl() . 'dashboard.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= htmlspecialchars(isActive('dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">
						    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
						</a>

                        <!-- Logout Button -->
                        <button class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md shadow-md transition flex items-center"
                                onclick="window.location.href='<?= htmlspecialchars(fullUrl() . 'logout.php', ENT_QUOTES, 'UTF-8'); ?>'">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>

                        <?php if (isAdmin()): ?>
                            <!-- Admin Dropdown -->
                            <div class="relative">
                                <button class="bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 transition flex items-center"
                                        id="admin-dropdown-toggle">
                                    <i class="fas fa-cog mr-2 text-xl"></i>
                                    <i class="fas fa-chevron-down ml-2 text-xl"></i>
                                </button>
                                <div id="admin-dropdown" class="absolute right-0 mt-2 w-48 bg-blue-600 rounded-md shadow-md z-20 hidden">
                                    <a href="<?= htmlspecialchars(fullUrl() . 'settings.php', ENT_QUOTES, 'UTF-8'); ?>" class="block px-4 py-2 text-white hover:bg-blue-700">
                                        <i class="fas fa-cogs mr-2"></i> Settings
                                    </a>
                                    <a href="<?= htmlspecialchars(fullUrl() . 'users.php', ENT_QUOTES, 'UTF-8'); ?>" class="block px-4 py-2 text-white hover:bg-blue-700">
                                        <i class="fas fa-users mr-2"></i> Users
                                    </a>
                                    <a href="<?= htmlspecialchars(fullUrl() . 'reports.php', ENT_QUOTES, 'UTF-8'); ?>" class="block px-4 py-2 text-white hover:bg-blue-700">
                                        <i class="fas fa-chart-line mr-2"></i> Reports
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden md:hidden">
                <div class="px-4 pt-2 pb-4 space-y-1">
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8'); ?>" class="block <?= htmlspecialchars(isActive('index.php'), ENT_QUOTES, 'UTF-8'); ?> text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-home mr-2"></i> Home
                        </a>
                        <a href="<?= htmlspecialchars(fullUrl() . 'about.php', ENT_QUOTES, 'UTF-8'); ?>" class="block <?= htmlspecialchars(isActive('about.php'), ENT_QUOTES, 'UTF-8'); ?> text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-info-circle mr-2"></i> About
                        </a>
                        <a href="<?= htmlspecialchars(fullUrl() . 'features.php', ENT_QUOTES, 'UTF-8'); ?>" class="block <?= htmlspecialchars(isActive('features.php'), ENT_QUOTES, 'UTF-8'); ?> text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-cogs mr-2"></i> Features
                        </a>
                        <a href="<?= htmlspecialchars(fullUrl() . 'contact.php', ENT_QUOTES, 'UTF-8'); ?>" class="block <?= htmlspecialchars(isActive('contact.php'), ENT_QUOTES, 'UTF-8'); ?> text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-envelope mr-2"></i> Contact
                        </a>

                        <button class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-2 px-4 rounded-md shadow-md hover:from-blue-600 hover:to-indigo-700 transition flex items-center justify-center mt-2"
                                onclick="window.location.href='<?= htmlspecialchars(fullUrl() . 'login.php', ENT_QUOTES, 'UTF-8'); ?>'">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                        <button class="w-full bg-gradient-to-r from-green-400 to-green-600 text-white py-2 px-4 rounded-md shadow-md hover:from-green-500 hover:to-green-700 transition flex items-center justify-center mt-2"
                                onclick="window.location.href='<?= htmlspecialchars(fullUrl() . 'signup.php', ENT_QUOTES, 'UTF-8'); ?>'">
                            <i class="fas fa-user-plus mr-2"></i> Signup
                        </button>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(fullUrl() . 'dashboard.php', ENT_QUOTES, 'UTF-8'); ?>" class="block <?= htmlspecialchars(isActive('dashboard.php'), ENT_QUOTES, 'UTF-8'); ?> text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>

                        <?php if (isAdmin()): ?>
                            <a href="<?= htmlspecialchars(fullUrl() . 'settings.php', ENT_QUOTES, 'UTF-8'); ?>" class="block text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md mt-2">
                                <i class="fas fa-cogs mr-2"></i> Settings
                            </a>
                            <a href="<?= htmlspecialchars(fullUrl() . 'users.php', ENT_QUOTES, 'UTF-8'); ?>" class="block text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md mt-2">
                                <i class="fas fa-users mr-2"></i> Users
                            </a>
                            <a href="<?= htmlspecialchars(fullUrl() . 'reports.php', ENT_QUOTES, 'UTF-8'); ?>" class="block text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md mt-2">
                                <i class="fas fa-chart-line mr-2"></i> Reports
                            </a>
                        <?php endif; ?>

                        <button class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md shadow-md transition flex items-center justify-center mt-2"
                                onclick="window.location.href='<?= htmlspecialchars(fullUrl() . 'logout.php', ENT_QUOTES, 'UTF-8'); ?>'">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- JavaScript for mobile and admin dropdown -->
    <script>
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Admin Dropdown Toggle
        const adminDropdownToggle = document.getElementById('admin-dropdown-toggle');
        const adminDropdown = document.getElementById('admin-dropdown');

        if (adminDropdownToggle) {
            adminDropdownToggle.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent the click from bubbling up
                adminDropdown.classList.toggle('hidden');
            });

            // Close admin dropdown when clicking outside
            window.addEventListener('click', (e) => {
                if (!adminDropdownToggle.contains(e.target) && !adminDropdown.contains(e.target)) {
                    adminDropdown.classList.add('hidden');
                }
            });
        }
    </script>