<?php
// File: includes/inc-header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/inc-db-connection.php');
require_once(__DIR__ . '/inc-functions.php');

$current_page  = basename($_SERVER['PHP_SELF']);
$metadata      = getPageMetaData("/" . $current_page);
$siteSettings  = getSiteSettings();
$currentUser   = getCurrentUser();

$defaultTitle       = $siteSettings['site_name']        ?? 'My SaaS Application';
$defaultDescription = $siteSettings['site_description'] ?? 'Welcome to our SaaS application.';
?>
<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($metadata['title'] ?? $defaultTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($metadata['description'] ?? $defaultDescription, ENT_QUOTES, 'UTF-8') ?>">
  <?php if (!empty($metadata['keywords'])): ?>
    <meta name="keywords" content="<?= htmlspecialchars($metadata['keywords'], ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <link rel="icon" href="<?= htmlspecialchars(fullUrl() . 'favicon.png', ENT_QUOTES, 'UTF-8') ?>" sizes="16x16" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= htmlspecialchars(fullUrl() . 'css/style.css', ENT_QUOTES, 'UTF-8') ?>">
  <script>
    document.addEventListener('DOMContentLoaded', ()=> {
      const fa = document.createElement('link');
      fa.rel = 'stylesheet';
      fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css';
      document.head.appendChild(fa);
    });
  </script>
</head>
<body class="font-sans bg-gray-100 min-h-screen flex flex-col">

<header>
  <nav class="bg-white border-b border-gray-200 shadow sticky top-0 z-50">
    <div class="container mx-auto px-6 py-4 flex items-center justify-between">
      <!-- Logo -->
      <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8') ?>"
         class="flex items-center space-x-2 text-2xl font-bold text-indigo-600 hover:opacity-90 transition">
        <i class="<?= htmlspecialchars($siteSettings['site_icon'] ?? 'fas fa-globe', ENT_QUOTES) ?> fa-lg"></i>
        <span><?= htmlspecialchars($siteSettings['site_name'] ?? 'My SaaS Application', ENT_QUOTES) ?></span>
      </a>

      <!-- Mobile toggle -->
      <button id="mobile-menu-button" aria-label="Toggle menu" aria-expanded="false"
              class="md:hidden text-gray-600 hover:text-indigo-600 focus:outline-none">
        <i class="fas fa-bars fa-2x"></i>
      </button>

      <!-- Desktop navigation -->
      <div class="hidden md:flex items-center space-x-4">
        <a href="<?= fullUrl() ?>"
           class="<?= isActive('index.php') ?>">
          Home
        </a>
        <a href="<?= fullUrl() . 'about/' ?>"
           class="<?= isActive('about.php') ?>">
          About
        </a>
        <a href="<?= fullUrl() . 'features/' ?>"
           class="<?= isActive('features.php') ?>">
          Features
        </a>
        <a href="<?= fullUrl() . 'contact/' ?>"
           class="<?= isActive('contact.php') ?>">
          Contact
        </a>

        <?php if (!isLoggedIn()): ?>
          <a href="<?= fullUrl() . 'login/' ?>"
             class="uppercase tracking-wide inline-flex items-center bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:from-blue-600 hover:to-indigo-700 transition">
            <i class="fas fa-sign-in-alt mr-2"></i> LOGIN
          </a>
          <a href="<?= fullUrl() . 'signup/' ?>"
             class="uppercase tracking-wide inline-flex items-center bg-gradient-to-r from-green-400 to-green-600 text-white px-4 py-2 rounded-lg shadow hover:from-green-500 hover:to-green-700 transition">
            <i class="fas fa-user-plus mr-2"></i> SIGNUP
          </a>
        <?php else: ?>
          <a href="<?= fullUrl() . 'dashboard/' ?>"
             class="inline-flex items-center bg-purple-600 text-white px-4 py-2 rounded-lg shadow hover:bg-purple-700 transition <?= isActive('dashboard.php') ?>">
            <i class="fas fa-chart-pie mr-2"></i> Dashboard
          </a>

          <?php if (isAdmin()): ?>
            <div class="relative">
              <button
                id="admin-dropdown-toggle"
                class="flex items-center space-x-2 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <i class="fas fa-cog"></i>
                <span>Admin</span>
                <i id="admin-dropdown-icon" class="fas fa-chevron-down transition-transform duration-200"></i>
              </button>
              <div
                id="admin-dropdown"
                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 divide-y divide-gray-100"
                role="menu"
                aria-labelledby="admin-dropdown-toggle"
              >
                <a href="<?= fullUrl() . 'settings/' ?>"
                   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                   role="menuitem">
                  <i class="fas fa-cogs mr-2"></i> Settings
                </a>
                <a href="<?= fullUrl() . 'users/' ?>"
                   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                   role="menuitem">
                  <i class="fas fa-users mr-2"></i> Users
                </a>
                <a href="<?= fullUrl() . 'reports/' ?>"
                   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                   role="menuitem">
                  <i class="fas fa-chart-line mr-2"></i> Reports
                </a>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
      <ul class="px-4 py-4 space-y-2 text-sm font-medium uppercase">
        <li>
          <a href="<?= fullUrl() ?>"
             class="<?= isActive('index.php') ?> flex items-center px-3 py-2">
            <i class="fas fa-home mr-2"></i> Home
          </a>
        </li>
        <li>
          <a href="<?= fullUrl() . 'about/' ?>"
             class="<?= isActive('about.php') ?> flex items-center px-3 py-2">
            <i class="fas fa-info-circle mr-2"></i> About
          </a>
        </li>
        <li>
          <a href="<?= fullUrl() . 'features/' ?>"
             class="<?= isActive('features.php') ?> flex items-center px-3 py-2">
            <i class="fas fa-cogs mr-2"></i> Features
          </a>
        </li>
        <li>
          <a href="<?= fullUrl() . 'contact/' ?>"
             class="<?= isActive('contact.php') ?> flex items-center px-3 py-2">
            <i class="fas fa-envelope mr-2"></i> Contact
          </a>
        </li>

        <?php if (!isLoggedIn()): ?>
          <li>
            <a href="<?= fullUrl() . 'login/' ?>"
               class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex items-center justify-center rounded-lg shadow px-3 py-2 transition">
              <i class="fas fa-sign-in-alt mr-2"></i> LOGIN
            </a>
          </li>
          <li>
            <a href="<?= fullUrl() . 'signup/' ?>"
               class="bg-gradient-to-r from-green-400 to-green-600 text-white flex items-center justify-center rounded-lg shadow px-3 py-2 transition">
              <i class="fas fa-user-plus mr-2"></i> SIGNUP
            </a>
          </li>
        <?php else: ?>
          <li>
            <a href="<?= fullUrl() . 'dashboard/' ?>"
               class="bg-purple-600 text-white flex items-center justify-center rounded-lg shadow px-3 py-2 transition">
              <i class="fas fa-chart-pie mr-2"></i> Dashboard
            </a>
          </li>
          <?php if (isAdmin()): ?>
            <li>
              <span class="block w-full flex items-center justify-center space-x-2 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition uppercase tracking-wide">
                <i class="fas fa-cog"></i><span>Admin</span>
              </span>
            </li>
            <li>
              <a href="<?= fullUrl() . 'settings/' ?>"
                 class="flex items-center px-3 py-2">
                <i class="fas fa-cogs mr-2"></i> Settings
              </a>
            </li>
            <li>
              <a href="<?= fullUrl() . 'users/' ?>"
                 class="flex items-center px-3 py-2">
                <i class="fas fa-users mr-2"></i> Users
              </a>
            </li>
            <li>
              <a href="<?= fullUrl() . 'reports/' ?>"
                 class="flex items-center px-3 py-2">
                <i class="fas fa-chart-line mr-2"></i> Reports
              </a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
</header>

<script>
  // Mobile toggle
  const mobileBtn = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  mobileBtn?.addEventListener('click', () => {
    const exp = mobileBtn.getAttribute('aria-expanded') === 'true';
    mobileBtn.setAttribute('aria-expanded', (!exp).toString());
    mobileMenu.classList.toggle('hidden');
  });

  // Desktop admin dropdown
  const adminToggle = document.getElementById('admin-dropdown-toggle');
  const adminMenu   = document.getElementById('admin-dropdown');
  const adminIcon   = document.getElementById('admin-dropdown-icon');

  if (adminToggle) {
    adminToggle.addEventListener('click', e => {
      e.stopPropagation();
      adminMenu.classList.toggle('hidden');
      const expanded = !adminMenu.classList.contains('hidden');
      adminToggle.setAttribute('aria-expanded', expanded.toString());
      adminIcon.classList.toggle('rotate-180', expanded);
    });
    window.addEventListener('click', e => {
      if (!adminToggle.contains(e.target) && !adminMenu.contains(e.target)) {
        adminMenu.classList.add('hidden');
        adminIcon.classList.remove('rotate-180');
      }
    });
  }
</script>