-- Clean SQL Starter for SaaS Template (No Users)
-- Generated: 12 July 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table: users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `verification_token` varchar(64) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: password_resets
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: settings
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL,
  `site_icon` varchar(255) DEFAULT 'fas fa-globe',
  `contact_email` varchar(255) NOT NULL,
  `recaptcha_site_key` varchar(255) NOT NULL,
  `recaptcha_secret_key` varchar(255) NOT NULL,
  `price_gbp` decimal(10,2) DEFAULT NULL,
  `stripe_mode` enum('live','test') DEFAULT 'test',
  `stripe_test_secret_key` varchar(255) DEFAULT NULL,
  `stripe_test_publishable_key` varchar(255) DEFAULT NULL,
  `stripe_live_secret_key` varchar(255) DEFAULT NULL,
  `stripe_live_publishable_key` varchar(255) DEFAULT NULL,
  `facebook_link` varchar(255) DEFAULT NULL,
  `x_link` varchar(255) DEFAULT NULL,
  `instagram_link` varchar(255) DEFAULT NULL,
  `home_meta_title` varchar(255) DEFAULT NULL,
  `home_meta_description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default settings row (generic)
INSERT INTO `settings` (
  `site_name`, `site_icon`, `contact_email`,
  `recaptcha_site_key`, `recaptcha_secret_key`,
  `price_gbp`, `stripe_mode`,
  `stripe_test_secret_key`, `stripe_test_publishable_key`,
  `stripe_live_secret_key`, `stripe_live_publishable_key`,
  `facebook_link`, `x_link`, `instagram_link`,
  `home_meta_title`, `home_meta_description`
) VALUES (
  'Your SaaS App',
  'fas fa-cogs',
  'support@yoursaasapp.com',
  'your-site-key',
  'your-secret-key',
  0.00,
  'test',
  'sk_test_XXXXXXXXXXXXXXXX',
  'pk_test_XXXXXXXXXXXXXXXX',
  'sk_live_XXXXXXXXXXXXXXXX',
  'pk_live_XXXXXXXXXXXXXXXX',
  'https://facebook.com/yourpage',
  'https://x.com/yourhandle',
  'https://instagram.com/yourprofile',
  'Welcome to Your SaaS App',
  'Launch, scale, and succeed with your all-in-one SaaS starter platform.'
);

COMMIT;