-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 24, 2024 at 03:18 PM
-- Server version: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bigbrothertimetracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
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
  `twitter_link` varchar(255) DEFAULT NULL,
  `instagram_link` varchar(255) DEFAULT NULL,
  `home_meta_title` varchar(255) DEFAULT NULL,
  `home_meta_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_name`, `site_icon`, `contact_email`, `recaptcha_site_key`, `recaptcha_secret_key`, `price_gbp`, `stripe_mode`, `stripe_test_secret_key`, `stripe_test_publishable_key`, `stripe_live_secret_key`, `stripe_live_publishable_key`, `facebook_link`, `twitter_link`, `instagram_link`, `home_meta_title`, `home_meta_description`) VALUES 
(1, 'site.com', 'fas fa-clock', 'contact@site.com', 'your_recaptcha_site_key', 'your_recaptcha_secret_key', 9.99, 'live', 'your_stripe_test_secret_key', 'your_stripe_test_publishable_key', 'your_stripe_live_secret_key', 'your_stripe_live_publishable_key', 'https://www.facebook.com/', 'https://x.com/', 'https://www.instagram.com/', 'The Ultimate Time Management Tool', 'site.com helps you monitor and manage your time effortlessly. Track tasks, projects, and team performance with ease, and boost productivity with automated tracking solutions.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;