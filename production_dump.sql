-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 24, 2026 at 01:21 PM
-- Server version: 10.11.17-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ektamultp_easyshoping`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'e.g. product, order, user, category',
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `user_name`, `action`, `entity_type`, `entity_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 4, 'Devbarat Prasad Patel', 'user.login', 'user', 4, 'Devbarat Prasad Patel logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 17:25:37'),
(2, 4, 'Devbarat Prasad Patel', 'user.logout', 'user', 4, 'Devbarat Prasad Patel logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 07:11:45'),
(3, 12, 'Devbarat', 'user.login', 'user', 12, 'Devbarat logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 07:15:46'),
(4, 13, 'Nepal Cyber Firm', 'user.login', 'user', 13, 'Nepal Cyber Firm logged in', NULL, NULL, '103.28.86.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 01:10:03'),
(5, 13, 'Nepal Cyber Firm', 'order.create', 'order', 4, 'New order #4 placed (Total: 110)', NULL, NULL, '103.28.86.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 01:27:27'),
(6, 13, 'Nepal Cyber Firm', 'order.update_status', 'order', 4, 'Order #4 status changed: Pending → Cancelled', '{\"status\":\"Pending\"}', '{\"status\":\"Cancelled\"}', '103.28.86.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 01:33:49'),
(7, 13, 'Nepal Cyber Firm', 'order.create', 'order', 5, 'New order #5 placed (Total: 150)', NULL, NULL, '103.28.86.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 01:39:02'),
(8, 13, 'Nepal Cyber Firm', 'order.update_status', 'order', 5, 'Order #5 status changed: Pending → Cancelled', '{\"status\":\"Pending\"}', '{\"status\":\"Cancelled\"}', '103.28.86.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 01:39:19'),
(9, 1, 'Admin', 'user.login', 'user', 1, 'Admin logged in', NULL, NULL, '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:01:52'),
(10, 1, 'Admin', 'user.logout', 'user', 1, 'Admin logged out', NULL, NULL, '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:04:24'),
(11, 1, 'Admin', 'user.login', 'user', 1, 'Admin logged in', NULL, NULL, '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:07:45'),
(12, 1, 'Admin', 'user.login', 'user', 1, 'Admin logged in', NULL, NULL, '113.199.240.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:14:03'),
(13, 1, 'Admin', 'user.logout', 'user', 1, 'Admin logged out', NULL, NULL, '113.199.240.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:15:07'),
(14, 1, 'Admin', 'user.login', 'user', 1, 'Admin logged in', NULL, NULL, '113.199.240.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:15:41'),
(15, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '103.28.86.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:20:10'),
(16, 1, 'Admin', 'user.logout', 'user', 1, 'Admin logged out', NULL, NULL, '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:21:38'),
(17, 14, 'Aaditya Kumar kushwaha', 'user.login', 'user', 14, 'Aaditya Kumar kushwaha logged in', NULL, NULL, '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:22:26'),
(18, 14, 'Aaditya Kumar kushwaha', 'order.create', 'order', 6, 'New order #6 placed (Total: 150)', NULL, NULL, '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:22:37'),
(19, 14, 'Aaditya Kumar kushwaha', 'order.update_status', 'order', 6, 'Order #6 status changed: Pending → Cancelled', '{\"status\":\"Pending\"}', '{\"status\":\"Cancelled\"}', '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:22:54'),
(20, 14, 'Aaditya Kumar kushwaha', 'order.update_status', 'order', 6, 'Order #6 status changed: Pending → Cancelled', '{\"status\":\"Pending\"}', '{\"status\":\"Cancelled\"}', '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:24:03'),
(21, 14, 'Aaditya Kumar kushwaha', 'order.update_status', 'order', 6, 'Order #6 status changed: Delivered → Return Requested', '{\"status\":\"Delivered\"}', '{\"status\":\"Return Requested\"}', '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:25:13'),
(22, 14, 'Aaditya Kumar kushwaha', 'order.update_status', 'order', 6, 'Order #6 status changed: Delivered → Return Requested', '{\"status\":\"Delivered\"}', '{\"status\":\"Return Requested\"}', '49.126.35.151', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 10:27:08'),
(23, 15, 'Sushil', 'user.login', 'user', 15, 'Sushil logged in', NULL, NULL, '49.126.69.37', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-05-22 13:59:21'),
(24, 14, 'Aaditya Kumar kushwaha', 'user.login', 'user', 14, 'Aaditya Kumar kushwaha logged in', NULL, NULL, '113.199.240.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 14:27:33'),
(25, 14, 'Aaditya Kumar kushwaha', 'user.login', 'user', 14, 'Aaditya Kumar kushwaha logged in', NULL, NULL, '113.199.240.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 15:03:07'),
(26, 14, 'Aaditya Kumar kushwaha', 'user.login', 'user', 14, 'Aaditya Kumar kushwaha logged in', NULL, NULL, '103.166.101.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 16:35:47'),
(27, 14, 'Aaditya Kumar kushwaha', 'order.update_status', 'order', 6, 'Order #6 status changed: Delivered → Return Requested', '{\"status\":\"Delivered\"}', '{\"status\":\"Return Requested\"}', '103.166.101.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-22 16:36:06'),
(28, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '2407:54c0:1b26:4a09:5463:9722:e443:647b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 16:57:51'),
(29, 14, 'Aaditya Kumar kushwaha', 'user.login', 'user', 14, 'Aaditya Kumar kushwaha logged in', NULL, NULL, '103.166.101.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 01:38:27'),
(30, 14, 'Aaditya Kumar kushwaha', 'user.logout', 'user', 14, 'Aaditya Kumar kushwaha logged out', NULL, NULL, '103.166.101.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 01:39:05'),
(31, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '103.166.101.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 01:39:10'),
(32, 16, 'Vijay Sah', 'user.login', 'user', 16, 'Vijay Sah logged in', NULL, NULL, '2405:acc0:1100:ead5:5598:2088:8b5e:af17', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-23 02:25:14'),
(33, 1, 'A.R.S', 'user.logout', 'user', 1, 'A.R.S logged out', NULL, NULL, '113.199.245.60', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 02:26:24'),
(34, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '113.199.245.60', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 02:26:28'),
(35, 16, 'Vijay Sah', 'order.create', 'order', 7, 'New order #7 placed (Total: 165)', NULL, NULL, '2405:acc0:1100:ead5:5598:2088:8b5e:af17', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-23 02:29:20'),
(36, 1, 'A.R.S', 'user.logout', 'user', 1, 'A.R.S logged out', NULL, NULL, '113.199.245.60', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 02:29:41'),
(37, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '113.199.245.60', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 02:29:48'),
(38, 16, 'Vijay Sah', 'order.create', 'order', 8, 'New order #8 placed (Total: 265)', NULL, NULL, '2405:acc0:1100:ead5:5598:2088:8b5e:af17', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-23 02:31:07'),
(39, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '2407:54c0:1b26:4a09:70a4:7f51:ae63:d21a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 03:10:45'),
(40, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '2407:54c0:1b26:aae2:9eb:f384:c269:4ae9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 05:38:47'),
(41, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '103.190.41.191', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 08:03:47'),
(42, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '49.126.105.37', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 10:47:53'),
(43, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '113.199.247.113', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-23 13:44:13'),
(44, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '2407:54c0:1b26:5ff7:1dce:7cfd:2e81:5b30', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 16:44:56'),
(45, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '2407:54c0:1b26:5ff7:f5a0:ef10:f092:d13', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-24 00:20:08'),
(46, 14, 'Aaditya Kumar kushwaha', 'user.login', 'user', 14, 'Aaditya Kumar kushwaha logged in', NULL, NULL, '103.166.101.95', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-05-24 01:00:31'),
(47, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '2407:54c0:1b26:f07d:d57d:644f:61b8:7324', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 08:38:55'),
(48, 1, 'A.R.S', 'user.login', 'user', 1, 'A.R.S logged in', NULL, NULL, '103.167.233.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link_type` enum('product','category','url','none') DEFAULT 'none',
  `link_value` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `link_type`, `link_value`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Summer Sale — Up to 50% Off', 'Shop the season\'s hottest deals on electronics, fashion & more.', 'banner-1.png', 'none', NULL, 1, 1, '2026-05-17 07:17:46', '2026-05-17 07:17:46'),
(2, 'New Arrivals — Fresh Drops Weekly', 'Be the first to discover trending products curated just for you.', 'banner-2.png', 'none', NULL, 2, 1, '2026-05-17 07:17:46', '2026-05-17 07:17:46'),
(3, 'Free Shipping on Orders Over ₹499', 'Limited time offer. No coupon needed — applied automatically.', 'banner-3.png', 'none', NULL, 3, 1, '2026-05-17 07:17:46', '2026-05-17 07:17:46');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(4, 4, '0cf7185434342520a6513cec7787d23d', 1, 1, '2026-05-03 17:25:40', '2026-05-03 17:25:40'),
(5, 12, 'aae31d25824b7b102a01415b2b3df51f', 3, 1, '2026-05-04 07:26:21', '2026-05-04 07:26:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Electronice', 'electronice'),
(2, 'MEDICINE', 'medicine'),
(3, 'CLOTHES', 'clothes');

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('fixed','percentage') DEFAULT 'fixed',
  `value` decimal(10,2) NOT NULL,
  `min_cart_amount` decimal(10,2) DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) DEFAULT NULL,
  `recipient` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `status` enum('pending','sending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `error_message` text DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content_html` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `applied_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `filename`, `applied_at`) VALUES
(1, '001_create_wishlists.sql', '2026-05-17 06:33:54'),
(2, '002_create_carts.sql', '2026-05-17 06:33:54');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('COD','eSewa','BankQR') NOT NULL,
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `delivery_status` enum('Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled','Return Requested','Returned') DEFAULT 'Pending',
  `current_location` varchar(255) DEFAULT 'Preparing for shipment',
  `location_updated_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `total_amount`, `coupon_code`, `discount_amount`, `payment_method`, `payment_status`, `delivery_status`, `current_location`, `location_updated_at`, `transaction_id`, `payment_proof`, `address`, `notes`, `created_at`) VALUES
(1, 2, 'Devbarat Prasad Patel', 'mind59024@gmail.com', '9811144402', 'Bahudramai-07, Phulkaul, Parsa', 2500.00, NULL, 0.00, 'COD', 'Failed', 'Cancelled', 'Preparing for shipment', '2026-05-22 10:30:03', NULL, NULL, NULL, NULL, '2026-04-12 10:02:17'),
(2, 2, 'Devbarat Prasad Patel', 'mind59024@gmail.com', '9811144402', 'Bahudramai-07, Phulkaul, Parsa', 2500.00, NULL, 0.00, 'COD', 'Failed', 'Cancelled', 'Preparing for shipment', '2026-05-22 10:29:55', NULL, NULL, NULL, NULL, '2026-04-12 13:26:44'),
(3, 2, 'Devbarat Prasad Patel', 'mind59024@gmail.com', '9811144402', 'Bahudramai-07, Phulkaul, Parsa', 10.00, NULL, 0.00, 'eSewa', 'Failed', 'Cancelled', 'Preparing for shipment', '2026-05-22 10:24:55', NULL, 'uploads/payments/esewa_1775980091_69db4e3b74888.png', NULL, NULL, '2026-04-12 13:33:11'),
(4, 13, 'Nepal Cyber Firm', 'nepalcyberfirm@gmail.com', '9800000000', 'Bahudaramai-7, Parsa, Madhesh Pradesh', 110.00, NULL, 0.00, 'COD', 'Failed', 'Cancelled', 'Preparing for shipment', '2026-05-22 10:24:45', NULL, NULL, NULL, NULL, '2026-05-22 01:27:27'),
(5, 13, 'Nepal Cyber Firm', 'nepalcyberfirm@gmail.com', '9800000000', 'Bahudaramai-7, Parsa, Madhesh Pradesh', 150.00, NULL, 0.00, 'eSewa', 'Failed', 'Cancelled', 'Preparing for shipment', '2026-05-22 10:24:39', NULL, 'uploads/payments/esewa_1779413942_6a0fb3b6256c5.png', NULL, NULL, '2026-05-22 01:39:02'),
(6, 14, 'Aaditya Kumar kushwaha', 'aaditkushwaha291@gmail.com', '9820210361', 'aaditkushwaha291@gmail.com, Birgunj-13, Parsa, Madhesh Pradesh', 150.00, NULL, 0.00, 'COD', 'Failed', 'Delivered', 'Preparing for shipment', '2026-05-22 16:58:30', NULL, NULL, NULL, NULL, '2026-05-22 10:22:37'),
(7, 16, 'Vijay Sah', 'vijaysah54321@gmail.com', '9821823670', 'Kaliya, Jaleshwor-12, Mahottari, Madhesh Pradesh', 165.00, NULL, 0.00, 'COD', 'Failed', 'Cancelled', 'Preparing for shipment', '2026-05-23 02:34:23', NULL, NULL, NULL, NULL, '2026-05-23 02:29:20'),
(8, 16, 'Vijay Sah', 'vijaysah54321@gmail.com', '9821823670', 'Kaliya, Jaleshwor-12, Mahottari, Madhesh Pradesh', 265.00, NULL, 0.00, 'COD', 'Pending', 'Out for Delivery', 'Out from birgunj', '2026-05-24 00:22:07', NULL, NULL, NULL, NULL, '2026-05-23 02:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `discount_price`) VALUES
(1, 1, 1, 1, 2500.00, NULL),
(2, 2, 1, 1, 2500.00, NULL),
(3, 3, 2, 1, 1000.00, 10.00),
(4, 4, 2, 1, 1000.00, 10.00),
(5, 5, 3, 1, 150.00, 50.00),
(6, 6, 3, 1, 150.00, 50.00),
(7, 7, 3, 1, 65.00, NULL),
(8, 8, 2, 1, 100.00, NULL),
(9, 8, 3, 1, 65.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `note`, `created_at`) VALUES
(1, 6, 'Shipped', 'Status changed from Return Requested to Shipped by admin', '2026-05-22 16:58:14'),
(2, 6, 'Shipped', 'Status changed from Shipped to Shipped by admin', '2026-05-22 16:58:19'),
(3, 6, 'Payment: Pending', 'Payment status changed from Failed to Pending by admin', '2026-05-22 16:58:19'),
(4, 6, 'Delivered', 'Status changed from Shipped to Delivered by admin', '2026-05-22 16:58:30'),
(5, 6, 'Payment: Failed', 'Payment status changed from Pending to Failed by admin', '2026-05-22 16:58:30'),
(6, 8, 'Shipped', 'Status changed from Pending to Shipped by admin', '2026-05-23 02:34:10'),
(7, 8, 'Payment: Failed', 'Payment status changed from Pending to Failed by admin', '2026-05-23 02:34:10'),
(8, 7, 'Cancelled', 'Status changed from Pending to Cancelled by admin', '2026-05-23 02:34:23'),
(9, 7, 'Payment: Failed', 'Payment status changed from Pending to Failed by admin', '2026-05-23 02:34:23'),
(10, 8, 'Shipped', 'Status changed from Shipped to Shipped by admin', '2026-05-23 03:43:59'),
(11, 8, 'Payment: Pending', 'Payment status changed from Failed to Pending by admin', '2026-05-23 03:43:59'),
(12, 8, 'Delivered', 'Status changed from Shipped to Delivered by admin', '2026-05-23 08:04:04'),
(13, 8, 'Payment: Paid', 'Payment status changed from Pending to Paid by admin', '2026-05-23 08:04:04'),
(14, 8, 'Shipped', 'Status changed from Delivered to Shipped by admin', '2026-05-23 08:04:53'),
(15, 8, 'Payment: Pending', 'Payment status changed from Paid to Pending by admin', '2026-05-23 08:04:53'),
(16, 8, 'Delivered', 'Status changed from Shipped to Delivered by admin', '2026-05-23 16:45:25'),
(17, 8, 'Payment: Paid', 'Payment status changed from Pending to Paid by admin', '2026-05-23 16:45:25'),
(18, 8, 'Shipped', 'Status changed from Delivered to Shipped by admin', '2026-05-23 17:07:14'),
(19, 8, 'Payment: Pending', 'Payment status changed from Paid to Pending by admin', '2026-05-23 17:07:14'),
(20, 8, 'Out for Delivery', 'Status changed from Shipped to Out for Delivery by admin', '2026-05-24 00:21:50'),
(21, 8, 'Out for Delivery', 'Status changed from Out for Delivery to Out for Delivery by admin', '2026-05-24 00:21:57'),
(22, 8, 'Payment: Refunded', 'Payment status changed from Pending to Refunded by admin', '2026-05-24 00:21:57'),
(23, 8, 'Out for Delivery', 'Status changed from Out for Delivery to Out for Delivery by admin', '2026-05-24 00:22:07'),
(24, 8, 'Payment: Pending', 'Payment status changed from Refunded to Pending by admin', '2026-05-24 00:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `hashed_otp` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `phone`, `otp_code`, `hashed_otp`, `expires_at`, `used`, `created_at`) VALUES
(1, '9800000000', '571901', '$2y$10$ueQmj0zSwDbsiNR9i0Dq3O2FGxvMB674OaUueTsb34PJUtCy9Aa2y', '2026-05-17 08:19:34', 0, '2026-05-17 08:14:34'),
(2, '9811144405', '560548', '$2y$10$lfOnnbjELohmfnUhV8uWHeUn5P7NcaxLtswp4YT0P3TXlXZ7/FFyK', '2026-05-17 16:05:50', 0, '2026-05-17 16:00:50'),
(3, '9812345678', '901044', '$2y$10$Mp1sm.bvu9YFtLD2wqRZZ.gwGKfdI4XvvCuIQxmHQ3oVkS/Uc0sd.', '2026-05-17 16:27:21', 1, '2026-05-17 16:22:21'),
(4, '9812345678', '500648', '$2y$10$g/BPE4R0Ucq6BD6WJ32NV.Q2ayMD9rIT2GP5M6JRFzg5nHEMuUO.y', '2026-05-17 16:27:47', 1, '2026-05-17 16:22:47'),
(5, '9812345678', '660324', '$2y$10$F1tCvaF8jYSRyEmdeArjCeZeSFumxgNsACaAifZzsHm0X/FAph3r.', '2026-05-17 16:28:03', 1, '2026-05-17 16:23:03'),
(6, 'test@easyshoppi', '567356', '$2y$10$mbh9qkHtaxzee69pLa27V.BbXx1du71rXvvzGGd/NCuljEhclMz1K', '2026-05-17 16:28:03', 0, '2026-05-17 16:23:03'),
(7, '9812345678', '160901', '$2y$10$Fcxr/QucGaXvpjmyWAzCaetIKcetNrO3HRnS5nSvz5j9b3HKmU2oO', '2026-05-17 16:28:25', 0, '2026-05-17 16:23:25'),
(8, 'test@easyshoppi', '518508', '$2y$10$MPJ2zsWh6CpuwoeSSHStSuMIFyu6O2D54VqULws2ljRTqZIRKvyGa', '2026-05-17 16:28:26', 0, '2026-05-17 16:23:26');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `discount_price`, `category_id`, `stock`, `image`, `sku`, `is_featured`, `created_at`) VALUES
(1, 'LED  LIGHT BULB 12W', 'led-light-bulb-12w', NULL, 320.00, 265.97, 1, 50, 'prod_6a108d1831f25.jpeg', NULL, 1, '2026-04-12 09:55:10'),
(2, 'COMD', 'comd', 'this is  a  test product', 100.00, NULL, 2, 101, 'prod_69db4e1cc7223.png', NULL, 0, '2026-04-12 13:32:40'),
(3, 'Vitamin E capsules', 'vitamin-e-capsules', 'Vitamin E capsules provide essential antioxidant support. They treat nutrient deficiencies and protect cells. Common variants like Evion 400 mg contain stable tocopheryl acetate.Core BenefitsAntioxidant shield: Protects cells from daily damage.Immune boost: Strengthens defense against viruses.Skin repair: Reduces appearance of dark spots.Hair vitality: Supports blood circulation in scalp', 65.00, NULL, 2, 99, 'prod_6a10353cf035d.jpg', NULL, 1, '2026-05-04 07:19:10'),
(4, 'LED LIGHT BULB 3W', 'led-light-bulb-3w', NULL, 150.00, 130.00, 1, 100, 'prod_6a108d50ecd17.jpeg', NULL, 0, '2026-05-22 17:07:28'),
(5, 'Immersion Water Heater', 'immersion-water-heater', NULL, 525.00, 498.00, 1, 100, 'prod_6a108d9971a5a.jpg', NULL, 0, '2026-05-22 17:08:41'),
(6, '6+3 Watt Double Colour', '6-3-watt-double-colour', NULL, 825.00, 700.00, 1, 100, 'prod_6a108e5e1ecdc.jpeg', NULL, 1, '2026-05-22 17:11:58'),
(7, 'LED BULB 30W', 'led-bulb-30w', NULL, 800.00, 699.97, 1, 100, 'prod_6a108eac91f6a.jpg', NULL, 0, '2026-05-22 17:13:16'),
(8, 'ELECTRIC  kettle', 'electric-kettle', NULL, 1125.00, 1068.00, 1, 100, 'prod_6a108f1591f61.jpeg', NULL, 0, '2026-05-22 17:15:01'),
(9, 'ELECTRIC JUG', 'electric-jug', NULL, 1000.00, 800.00, 1, 100, 'prod_6a108f54b50e3.jpeg', NULL, 0, '2026-05-22 17:16:04'),
(10, 'Electricity Meter', 'electricity-meter', NULL, 800.00, 560.00, 1, 100, 'prod_6a108fb25b597.jpeg', NULL, 0, '2026-05-22 17:17:38'),
(11, 'MCB(32AM DP MCB)', 'mcb-32am-dp-mcb', NULL, 1050.00, 955.00, 1, 100, 'prod_6a1090230d266.jpg', NULL, 0, '2026-05-22 17:19:31'),
(12, 'BATTAN HOLDER', 'battan-holder', NULL, 160.00, 140.00, 1, 100, 'prod_6a10907fa67a8.jpeg', NULL, 0, '2026-05-22 17:21:03'),
(13, 'Hometek Halogen Heater', 'hometek-halogen-heater', NULL, 2850.00, 2700.00, 1, 96, 'prod_6a10911a79942.jpeg', NULL, 0, '2026-05-22 17:23:38'),
(14, 'Adult diaper ( Large)', 'adult-diaper-large', 'Up To 8-10 hrs (Leakage Protection),\r\nRapid Absorption (Super Absorbent care ),\r\nRe-Sealable Tapes (Customised Fit),\r\nEasy to wear ,\r\nEasy to dispose ,', 900.00, 700.00, 3, 100, 'prod_6a113f68e57d6.jpg', NULL, 0, '2026-05-23 05:47:20'),
(15, 'Adult Diaper  (MEDIUM )', 'adult-diaper-medium', 'Up To 8-10 hrs (Leakage Protection),\r\nRapid Absorption (Super Absorbent care ),\r\nRe-Sealable Tapes (Customised Fit),\r\nEasy to wear, \r\nEasy to dispose', 900.00, 695.00, 3, 100, 'prod_6a114094d7012.jpg', NULL, 0, '2026-05-23 05:52:20'),
(16, 'Baby Diaper Small (5PIC)', 'baby-diaper-small-5pic', 'Prevents leakage ,\r\nAll Night Dryness,\r\nRash Proof,\r\nSuper Absorbent ,\r\nGuaranteed softness ,\r\nBreathable Material ,\r\nElastic waist,\r\nNET CONTENT:6 UNITS', 100.00, 90.00, 3, 98, 'prod_6a1141cff159e.jpg', NULL, 0, '2026-05-23 05:57:35'),
(17, 'Baby Diaper Medium', 'baby-diaper-medium', 'prevents leakage, \r\nAll Night Dryness,\r\nRash Proof,\r\nSuper Absorbent, \r\nGuaranteed softness ,\r\nBreathable Material ,\r\nElastic waist,\r\nNET CONTENT:5 UNITS', 100.00, 95.00, 3, 998, 'prod_6a114289def29.jpg', NULL, 0, '2026-05-23 06:00:41'),
(18, 'Baby Diaper Large', 'baby-diaper-large', 'Prevents leakage ,\r\nAll Night Dryness,\r\nRash Proof,\r\nSuper Absorbent ,\r\nGuaranteed softness ,\r\nBreathable Material ,\r\nElastic waist,\r\nNET Content: 5 UNITS', 110.00, 100.00, 3, 96, 'prod_6a1142dfe5817.jpg', NULL, 0, '2026-05-23 06:02:07'),
(19, 'Baby Wipes', 'baby-wipes', 'Help restore skin&#039;s,\r\nMoisturizing with Aloe Vera &amp; Vitamin-E,\r\nBiosensitivs + Aloevera &amp; chamomile,', 150.00, 130.00, 2, 98, 'prod_6a1143366012e.jpg', NULL, 0, '2026-05-23 06:03:34'),
(20, 'Earbuds (Bluetooth Wireless Earbuds Playtime With Charging Case Built-in Touch Control LED Screen For Sport Workout Hiking)', 'earbuds-bluetooth-wireless-earbuds-playtime-with-charging-case-built-in-touch-control-led-screen-for-sport-workout-hiking', 'Model:A9Pro Wireless Earbuds Display Size:2&quot; Transmission rate:24MBPS Ultra long call time:4-5 hours. Listening to music time:4-5 hours. With Charging case:20+Hours Play Input voltage: DC5VIA (Max) Charging time:1.5 hours Standby time:150 hours Input voltage: DC5V. input 2.IA Package Included: Pair TWS Earbuds Charging cable\r\n\r\ncurrent:IA Output voltage:5V/160mA (Max) Output current:\r\n\r\nCharging box Carry Strap\r\n\r\nDescription\r\n\r\nModel:A9Pro Wireless Earbuds\r\n\r\nDisplay Size:2&quot;\r\n\r\nTransmission rate:24MBPS\r\n\r\nUltra long call time:4-5 hours\r\n\r\nListening to music time:4-5 hours\r\n\r\nWith Charging case:20+Hours Play\r\n\r\nInput voltage: DC5VIA (Max)\r\n\r\nCharging time:1.5 hours\r\n\r\nStandby time:150 hours\r\n\r\nInput voltage: DC5V\r\n\r\ninput current:IA\r\n\r\nOutput voltage: 5V/160mA (Max)\r\n\r\nOutput current: 2.1A\r\n\r\nPackage Included:\r\n\r\nPair TWS Earbuds\r\n\r\nCharging cable\r\n\r\nCharging box\r\n\r\nCarry Strap', 1500.00, 1350.00, 1, 100, 'prod_6a1144339a873.jpg', NULL, 1, '2026-05-23 06:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`) VALUES
(11, 3, 'prod_6a10353cf035d.jpg', 1),
(12, 2, 'prod_69db4e1cc7223.png', 1),
(13, 1, 'prod_6a108d1831f25.jpeg', 1),
(14, 4, 'prod_6a108d50ecd17.jpeg', 1),
(15, 5, 'prod_6a108d9971a5a.jpg', 1),
(16, 6, 'prod_6a108e5e1ecdc.jpeg', 1),
(17, 6, 'prod_6a108e5e1f353.jpg', 0),
(18, 6, 'prod_6a108e5e1f52c.jpg', 0),
(19, 7, 'prod_6a108eac91f6a.jpg', 1),
(20, 7, 'prod_6a108eac92313.jpeg', 0),
(21, 8, 'prod_6a108f1591f61.jpeg', 1),
(22, 8, 'prod_6a108f15925b3.jpeg', 0),
(24, 9, 'prod_6a108f54b50e3.jpeg', 1),
(25, 10, 'prod_6a108fb25b597.jpeg', 1),
(26, 10, 'prod_6a108fb25ca4e.jpeg', 0),
(27, 11, 'prod_6a1090230d266.jpg', 1),
(29, 12, 'prod_6a10907fa67a8.jpeg', 1),
(30, 13, 'prod_6a10911a79942.jpeg', 1),
(33, 14, 'prod_6a113f68e57d6.jpg', 1),
(34, 14, 'prod_6a113f68e5ba2.jpg', 0),
(35, 15, 'prod_6a114094d7012.jpg', 1),
(36, 15, 'prod_6a114094d7591.jpg', 0),
(37, 16, 'prod_6a1141cff159e.jpg', 1),
(38, 17, 'prod_6a114289def29.jpg', 1),
(39, 17, 'prod_6a114289df6e7.jpg', 0),
(40, 18, 'prod_6a1142dfe5817.jpg', 1),
(41, 18, 'prod_6a1142dfe5c7f.jpg', 0),
(42, 19, 'prod_6a1143366012e.jpg', 1),
(43, 20, 'prod_6a1144339a873.jpg', 1),
(44, 20, 'prod_6a1144339ac1c.jpg', 0),
(45, 20, 'prod_6a1144339ad53.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `action` varchar(50) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `window_start` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `action`, `attempts`, `window_start`, `created_at`) VALUES
(36, '9820210361', 'register', 1, '2026-05-21 10:22:25', '2026-05-21 10:22:25'),
(37, '9820210361', 'login', 1, '2026-05-21 10:22:40', '2026-05-21 10:22:40'),
(38, '', 'login', 1, '2026-05-21 10:23:01', '2026-05-21 10:23:01'),
(39, '', 'register', 1, '2026-05-21 10:23:01', '2026-05-21 10:23:01'),
(40, 'test', 'login', 1, '2026-05-21 10:23:51', '2026-05-21 10:23:51'),
(41, '9820210361', 'register', 1, '2026-05-21 10:23:52', '2026-05-21 10:23:52');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`key`, `value`) VALUES
('admin_email', 'easyshoppinga.r.s1@gmail.com'),
('auto_approve_reviews', '0'),
('bank_account_details', ''),
('bank_qr_enabled', '0'),
('cod_enabled', '0'),
('company_address', ''),
('company_email', ''),
('company_name', ''),
('company_phone', ''),
('currency_code', ''),
('currency_symbol', ''),
('esewa_enabled', '0'),
('esewa_merchant_id', ''),
('estimated_delivery_days', ''),
('facebook_pixel_id', ''),
('facebook_url', ''),
('featured_products_limit', ''),
('free_shipping_threshold', '5000'),
('google_analytics_id', ''),
('instagram_url', ''),
('linkedin_url', ''),
('low_stock_threshold', ''),
('maintenance_message', ''),
('maintenance_mode', '0'),
('meta_description', ''),
('meta_keywords', ''),
('meta_title', ''),
('order_prefix', ''),
('products_per_page', ''),
('qr_code_path', '/uploads/qr/seller_qr.jpg'),
('reviews_enabled', '0'),
('reviews_per_page', ''),
('shipping_cost', '100'),
('site_description', ''),
('site_name', 'Easy Shopping A.R.S'),
('site_url', ''),
('smtp_encryption', 'tls'),
('smtp_host', 'smtp.gmail.com'),
('smtp_password', 'vobx mgfp fstc zlhx'),
('smtp_port', '587'),
('smtp_username', 'easyshoppinga.r.s1@gmail.com'),
('support_email', 'easyshoppinga.r.s1@gmail.com'),
('timezone', ''),
('twitter_url', '');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `role` enum('admin','support','technical','manager') DEFAULT 'support',
  `position` varchar(255) DEFAULT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `fb_link` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `email`, `mobile`, `role`, `position`, `profile_image`, `fb_link`, `bio`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Aaditya Kumar Kushwaha (A.R.K)', 'aaditkushwaha291@gmail.com', '9820210361', 'admin', '     Founder & CEO ', '/public/assets/img/team/team_1_1778111704.jpg', 'https://www.facebook.com/share/1E6Gn8hf6Z/', 'Experienced entrepreneur leading Easy Shopping A.R.S with passion for quality products and customer satisfaction.\r\n', 1, 1, '2026-05-05 19:48:48', '2026-05-07 09:59:42'),
(3, 'Roshan Kushwaha', 'roshan@easyshoppingars.com', '9706800854', 'support', 'Dealer & Delivery officer', '/public/assets/img/team/team_3_1778066801.jpg', 'https://www.facebook.com/share/1PZkU2JsD5/', 'A responsible individual who assists with product handling, coordinates with dealers, and manages delivery operations efficiently.', 1, 4, '2026-05-05 19:48:48', '2026-05-12 10:28:14'),
(4, 'Sushil Shah', 'easyshoppinga.r.s1@gmail.com', '9746815326', 'support', 'Technical Support  & customer service  officer', '/public/assets/img/team/team_4_1778069142.jpg', 'https://www.facebook.com/share/1LPtsG1odR/', 'Expert in troubleshooting technical issues and providing IT support for our online platform.', 1, 4, '2026-05-05 19:48:48', '2026-05-12 10:28:47'),
(5, 'Mukesh  raut ahit (Yadav)', 'mukesh@easyshoppingars.com', '9800000000', 'support', 'Dealer & Delivery  Head  officer', '/public/assets/img/team/team_5_1778071388.jpg', 'https://www.facebook.com/share/18qtWAMbgf/', 'Committed to providing exceptional customer service and building long-lasting relationships with our valued customers.\r\nA responsible individual who assists with product handling, coordinates with dealers, and manages delivery operations efficiently.', 1, 2, '2026-05-05 19:48:48', '2026-05-12 10:27:30'),
(6, 'A.R.K', NULL, NULL, 'admin', 'Technical support & Customer service Head officer', '/public/assets/img/team/team_6_1778148070.png', 'https://www.facebook.com/share/1E6Gn8hf6Z/', 'Expert in troubleshooting \r\ntechnical issues and providing IT support for our online \r\nplatform.\r\nEnsuring customer satisfaction through quality support and service.', 1, 5, '2026-05-07 09:52:17', '2026-05-12 10:29:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `reset_token_used_at` datetime DEFAULT NULL,
  `otp_attempts` tinyint(4) NOT NULL DEFAULT 0,
  `otp_issued_at` datetime DEFAULT NULL,
  `otp_hash` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL COMMENT 'SHA-256 hash of the persistent remember-me cookie token',
  `oauth_provider` varchar(20) DEFAULT NULL,
  `oauth_provider_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `mobile`, `password`, `address`, `role`, `reset_token`, `reset_expires`, `reset_token_used_at`, `otp_attempts`, `otp_issued_at`, `otp_hash`, `email_verified_at`, `verification_token`, `remember_token`, `oauth_provider`, `oauth_provider_id`, `created_at`) VALUES
(1, 'A.R.S', 'easyshoppinga.r.s1@gmail.com', '9746815326', '$2y$10$8EAg4tFV3UZQAqkbZgmoMO3.c9Bv.Qx2SqKbK6GiolQfB9NfBuQ/.', 'Birgunj-13, Parsa, Madhesh Pradesh', 'admin', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 10:54:28'),
(2, 'Devbarat Prasad Patel', 'mind59024@gmail.com', '9811144402', '$2y$12$VPBoIR19Sf70449AXW8D/eO4BWAUX03RSjDF1nRMIz0c/JR0m4C.u', 'Bahudramai-07, Phulkaul, Parsa', 'customer', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 15:04:48'),
(4, 'Devbarat Prasad Patel', 'pdewbrath@gmail.com', '9811144403', '$2y$12$eiF2G8Bo6YWbEgSXxz3rBO.OcyKkLXyzpuY6ElQNdMhX96zpaU7A.', 'Birgunj-13,Radhemai', 'customer', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-03 17:25:24'),
(12, 'Devbarat', 'smind59024@gmail.com', '9811144404', '$2y$12$BxYrr20s8FK03xmga4ceXeV9QDG3X/hwq4P..AqAu0xVUzIVagesK', 'Burgunj', 'admin', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '7dec1ecab6b88db9deb5a296da7ad08141c26a25206013dde93239d9ea4aa5f0', NULL, NULL, '2026-05-04 07:15:19'),
(13, 'Nepal Cyber Firm', 'nepalcyberfirm@gmail.com', '9800000000', '$2y$10$xMMlftZMXhcBqguKIcHa8O5144PuceNEp6gao1jl3J9ZJWam5fiZ6', 'Bahudaramai-7, Parsa, Madhesh Pradesh', 'customer', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 01:09:57'),
(14, 'Aaditya Kumar kushwaha', 'aaditkushwaha291@gmail.com', '9820210361', '$2y$10$2NuBRK6k.LPNO6l0k1gpMOjiytkLdnAvysiYQXw8wqVf2FhKGI2oC', 'Birgunj-13, Parsa, Madhesh Pradesh', 'customer', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:22:18'),
(15, 'Sushil', 'sushilshah@gmail.com', '9806832544', '$2y$10$w41Nkh1djpJ/bpUImQ12D.u53Jl316H0P8oYjNNFjQXHD5r.QWxHS', 'BIRJUNJ, Birgunj-13, Parsa, Madhesh Pradesh', 'customer', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 13:59:13'),
(16, 'Vijay Sah', 'vijaysah54321@gmail.com', '9821823670', '$2y$10$UD2qjo1ecCVH3DVCFSRcEue482l9lF8WhF8on9f3lJdyEfGjnlkMO', 'Kaliya, Jaleshwor-12, Mahottari, Madhesh Pradesh', 'customer', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-23 02:24:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `province` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `ward` varchar(50) NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `tag` varchar(50) DEFAULT 'Home',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `full_name`, `phone`, `province`, `district`, `municipality`, `ward`, `street`, `tag`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 13, 'Nepal Cyber Firm', '9800000000', 'Madhesh Pradesh', 'Parsa', 'Bahudaramai', '7', '', 'Home', 1, '2026-05-22 01:13:09', '2026-05-22 01:13:22'),
(2, 1, 'A.R.S', '9746815326', 'Madhesh Pradesh', 'Parsa', 'Birgunj', '13', '', 'Home', 1, '2026-05-22 10:17:45', '2026-05-22 10:17:45'),
(3, 14, 'Aaditya Kumar kushwaha', '9820210361', 'Madhesh Pradesh', 'Parsa', 'Birgunj', '13', '', 'Home', 1, '2026-05-22 10:22:18', '2026-05-22 15:04:09'),
(4, 15, 'Sushil', '9806832544', 'Madhesh Pradesh', 'Parsa', 'Birgunj', '13', 'BIRJUNJ', 'Home', 1, '2026-05-22 13:59:13', '2026-05-22 13:59:13'),
(5, 16, 'Vijay Sah', '9821823670', 'Madhesh Pradesh', 'Mahottari', 'Jaleshwor', '12', 'Kaliya', 'Home', 1, '2026-05-23 02:24:45', '2026-05-23 02:24:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_banners_active` (`is_active`),
  ADD KEY `idx_banners_order` (`sort_order`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cart_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_cart_user` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD UNIQUE KEY `unique_session_product` (`session_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `scheduled_at` (`scheduled_at`),
  ADD KEY `idx_email_queue_status` (`status`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filename` (`filename`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_osh_order` (`order_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otps_phone` (`phone`),
  ADD KEY `idx_otps_expires` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_product_review` (`user_id`,`product_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rl_lookup` (`identifier`,`action`),
  ADD KEY `idx_rl_window` (`window_start`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_team_members_email` (`email`),
  ADD UNIQUE KEY `idx_team_members_mobile` (`mobile`),
  ADD KEY `idx_team_members_role` (`role`),
  ADD KEY `idx_team_members_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_reset_expires` (`reset_expires`),
  ADD KEY `idx_users_remember_token` (`remember_token`),
  ADD KEY `idx_oauth` (`oauth_provider`,`oauth_provider_id`),
  ADD KEY `idx_users_role_created` (`role`,`created_at`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_addresses_user` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_session` (`user_id`,`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_wishlist_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_wishlist_user` (`user_id`),
  ADD KEY `fk_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `fk_osh_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_user_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
