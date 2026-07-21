-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 21, 2026 at 11:37 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skena_coffe`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','owner','kasir') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `role`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin Skena', 'admin@skenacoffee.id', 'admin', '$2y$12$7JAWIeTbgnHdwJ/w44QHLuDvvzgfoRdRulFIULjYowwXVlezGH4xm', NULL, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(2, 'Owner Skena', 'owner@skenacoffee.id', 'owner', '$2y$12$4TxyEDWVmD6v9xx.qmcCkOPPIgOI8ipu8wBlmGllWqdeF.qZTTxLW', NULL, '2026-07-02 21:51:00', '2026-07-02 21:51:00'),
(3, 'Kasir Skena', 'kasir@skenacoffee.id', 'kasir', '$2y$12$5BLdfIDF4qF/tjg.K0lpF./TKPf2b7StWyU5NEI6NvQ6NUTo4CiUK', NULL, '2026-07-21 00:44:10', '2026-07-21 00:44:10');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Espresso Based', 0, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(2, 'Black Series', 1, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(3, 'Signature', 2, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(4, 'Blend Series', 3, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(5, 'Tea Series', 4, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(6, 'Matcha Series', 5, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(7, 'Appetizer', 6, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(8, 'Pasta', 7, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(9, 'Main Course', 8, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(10, 'Pizza', 9, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(11, 'Dessert', 10, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(12, 'Lainnya', 11, '2026-06-07 02:20:09', '2026-06-07 02:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category_id` bigint UNSIGNED NOT NULL,
  `price` bigint UNSIGNED NOT NULL,
  `has_hot` tinyint(1) NOT NULL DEFAULT '0',
  `price_hot` bigint UNSIGNED DEFAULT NULL,
  `desc_hot` text COLLATE utf8mb4_unicode_ci,
  `has_ice` tinyint(1) NOT NULL DEFAULT '0',
  `price_ice` bigint UNSIGNED DEFAULT NULL,
  `desc_ice` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` smallint UNSIGNED NOT NULL DEFAULT '0',
  `rating` decimal(3,1) NOT NULL DEFAULT '5.0',
  `reviews` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `description`, `category_id`, `price`, `has_hot`, `price_hot`, `desc_hot`, `has_ice`, `price_ice`, `desc_ice`, `image`, `tag`, `is_available`, `sort_order`, `rating`, `reviews`, `created_at`, `updated_at`) VALUES
(1, 'Espresso', 'Espresso', 1, 15000, 1, 15000, 'Espresso', 0, NULL, NULL, 'menus/c4bbQOUzJ3tOYKAjqJUfzOHOQoQyDh4T84xKtcyf.jpg', NULL, 1, 0, '5.0', 10, '2026-06-07 02:20:09', '2026-07-03 01:04:58'),
(2, 'Americano', 'Americano', 1, 25000, 1, 25000, 'Espresso with hot water', 1, 31000, 'Espresso, Water with ice', 'menus/HB7Y2TQ4ZlFkEXDN6yl5gvOkWt9qvmoNKcpaCZ7E.jpg', NULL, 1, 1, '4.8', 15, '2026-06-07 02:20:09', '2026-07-03 01:05:09'),
(3, 'Caffe Latte', 'Caffe Latte', 1, 31000, 1, 31000, 'Espresso, Fresh Milk Hot', 1, 32000, 'Espresso, Fresh Milk with Ice', 'menus/0iuHrjhir4bDtYQiY5OZ78iOfw8UKXpKcDJ1KzjJ.jpg', NULL, 1, 2, '4.9', 20, '2026-06-07 02:20:09', '2026-07-03 01:05:45'),
(4, 'Cappucino', 'Cappucino', 1, 30000, 1, 30000, 'Espresso, Fresh Milk Hot', 1, 32000, 'Espresso, Fresh Milk with Ice', 'menus/DuBU03Wn1ElT0O9WSOIQT6JnabYAmIbWn8UfOmwK.jpg', NULL, 1, 3, '4.7', 18, '2026-06-07 02:20:09', '2026-07-03 01:06:04'),
(5, 'Cream Vanilla', 'Cream Vanilla', 1, 35000, 1, 35000, 'Espresso, Vanilla Syrup, Creamer', 0, NULL, NULL, 'menus/zYpYNfDu69enDBl4HdwR3xVykIGQbG3YSk37V4bz.jpg', NULL, 1, 4, '4.6', 12, '2026-06-07 02:20:09', '2026-07-05 08:24:18'),
(6, 'Apple Black Sparkling', 'Apple Black Sparkling', 2, 32000, 0, NULL, NULL, 1, 32000, 'Espresso, Soda water, Juice Apple, Vanilla Syrup, with ice', 'menus/nuuNYtaWYadCjDmTbFxJ7XX0UIeU0RlbfOsxgC73.jpg', NULL, 1, 5, '4.8', 25, '2026-06-07 02:20:09', '2026-07-05 08:04:21'),
(7, 'Americano Strawberry', 'Americano Strawberry', 2, 33000, 0, NULL, NULL, 1, 33000, 'Espresso, Strawberry pure, Apple Juice with ice', 'menus/NBDUPlVUMoOUH4sZSnGJz7T5IGTDGIaEeU64NDZy.jpg', NULL, 1, 6, '4.7', 19, '2026-06-07 02:20:09', '2026-07-05 08:04:55'),
(8, 'Cranberry Black Sparkling', 'Cranberry Black Sparkling', 2, 32000, 0, NULL, NULL, 1, 32000, 'Espresso, Simple Syrup, Cranberry Juice, Soda Water with ice', 'menus/R84LndbUwwjjY4ztbpi6mHEK8f3YmEhXZN4g1wC5.jpg', NULL, 1, 7, '4.9', 22, '2026-06-07 02:20:09', '2026-07-05 08:09:18'),
(9, 'Guava Black Sparkling', 'Guava Black Sparkling', 2, 32000, 0, NULL, NULL, 1, 32000, 'Espresso, Soda Water, Juice Guava, Vanilla Syrup, with ice', NULL, NULL, 1, 8, '4.6', 15, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(10, 'Coffee LDR', 'Coffee LDR', 3, 30000, 1, 30000, 'Freshmilk Coconut, espresso, Brown Sugar', 1, 32000, 'Freshmilk Coconut, Espresso, creamer, Brown Sugar with ice', 'menus/AcuBs6DmpPxyYgjC16k5sGlTKRv4uFWnSBOYYclA.jpg', 'Terlaris', 1, 9, '5.0', 120, '2026-06-07 02:20:09', '2026-07-05 08:09:50'),
(11, 'Caramel Machiato Slay', 'Caramel Machiato Slay', 3, 33000, 0, NULL, NULL, 1, 33000, 'Freshmilk, Cimory, Syrup Butterscotch, Salted Caramel with caramel garnish and sauce', 'menus/miG4IAQcBC06YMez2LQ9GOv8JzxJ4WDKRlCgoaSx.jpg', NULL, 1, 10, '4.8', 85, '2026-06-07 02:20:09', '2026-07-05 08:10:04'),
(12, 'Mocha Deep Talk', 'Mocha Deep Talk', 3, 34000, 1, 34000, 'Espresso, Dark Choco, Creames and Freshmilk', 1, 41000, 'Espresso, Syrup Vanilla, Dark choco, Sea Cream Whip with ice', 'menus/kKIkA30UsYM3UjdLhaCbP7HCfqbYEVUcH5fkP7Ep.jpg', 'Premium', 1, 11, '4.9', 95, '2026-06-07 02:20:09', '2026-07-05 08:10:15'),
(13, 'Butterscotch Cegil', 'Butterscotch Cegil', 3, 36000, 0, NULL, NULL, 1, 36000, 'Freshmilk, Espresso, Syrup butterscotch, Sea Cream with ice', 'menus/WSzCGGLsRILUMk0KiySsVKsbo3ZqWMJEgaXjR2qk.jpg', NULL, 1, 12, '4.7', 70, '2026-06-07 02:20:09', '2026-07-05 08:10:24'),
(14, 'Ice Coffee Kenapa Makassar', 'Ice Coffee Kenapa Makassar', 3, 34000, 0, NULL, NULL, 1, 34000, 'Strawberry Milk, espresso, Syrup Vanilla, Butterscotch Powder, Creamer with ice', 'menus/kMTglsCqolLWM8wQm2bEbdvIEmDMSsfucIUjqdiU.jpg', NULL, 1, 13, '4.8', 65, '2026-06-07 02:20:09', '2026-07-05 08:10:37'),
(15, 'Ice Coffee Bjir', 'Ice Coffee Bjir', 3, 35000, 0, NULL, NULL, 1, 35000, 'Freshmilk, syrup Pandan, Creamer with ice', 'menus/1ZTFutvlONGbyCZBwImhKRnBdBn5DSNBaFJEP3V0.jpg', NULL, 1, 14, '4.6', 50, '2026-06-07 02:20:09', '2026-07-05 08:10:44'),
(16, 'Ice Coffee Baileys', 'Ice Coffee Baileys', 3, 39000, 0, NULL, NULL, 1, 39000, 'Freshmilk, Espresso, Baileys Powder, Syrup Vanilla, Syrup Rum, Creamer with ice', 'menus/3I7XwK2OXsWIMl0rbKn5SbLGX0G12ztfRjpGmVKp.jpg', 'Hits', 1, 15, '4.9', 110, '2026-06-07 02:20:09', '2026-07-05 08:10:55'),
(17, 'Ice Coffee Kalcer', 'Ice Coffee Kalcer', 3, 33000, 0, NULL, NULL, 1, 33000, 'Espresso, Creamer, Freshmilk, Brown Sugar, Cinnamon Powder with ice', 'menus/u9YQ1XbsJ13nUZMk7TCohyCe4k5jHcjI0GPrflaG.jpg', NULL, 1, 16, '4.7', 80, '2026-06-07 02:20:09', '2026-07-05 08:11:04'),
(18, 'Cream Bruelle Gwenchana', 'Cream Bruelle Gwenchana', 3, 35000, 1, 37000, 'Espresso, Creamer, Freshmilk, Brown Sugar, Cinnamon Powder with ice', 1, 35000, 'Espresso, Scrup Caramel, Freshmilk, Sea Cream Whip with ice', 'menus/jxyZgJLGYJRXnI4tVOqGcYKZGooA1GwY3H4z1DYk.jpg', NULL, 1, 17, '4.8', 90, '2026-06-07 02:20:09', '2026-07-05 08:11:34'),
(19, 'Cream Manggo Coco Citrus', 'Cream Manggo Coco Citrus', 3, 34000, 0, NULL, NULL, 1, 34000, 'Cream Manggo Coco Citrus', 'menus/2h7ZPxgXxAa1CE3kiEIo5SjJxM1Erskan1jQwTE6.jpg', NULL, 1, 18, '4.6', 45, '2026-06-07 02:20:09', '2026-07-05 08:11:56'),
(20, 'Strawberry Snow Cream', 'Strawberry Snow Cream', 3, 34000, 0, NULL, NULL, 1, 34000, 'Strawberry Snow Cream', 'menus/odVKKJXx0WYm4ASmqYA21nLxkEvnkEM1q0cbsJtB.jpg', NULL, 1, 19, '4.7', 55, '2026-06-07 02:20:09', '2026-07-05 08:12:12'),
(21, 'Minty Berry Bliss', 'Minty Berry Bliss', 3, 34000, 0, NULL, NULL, 1, 34000, 'Minty Berry Bliss', 'menus/TD0ltCr8Rt6zy2XeaK1st2bCDcttReY5veMGHXnd.jpg', NULL, 1, 20, '4.8', 60, '2026-06-07 02:20:09', '2026-07-05 08:12:22'),
(22, 'Choco Butter Yolo', 'Choco Butter Yolo', 4, 39000, 0, NULL, NULL, 1, 39000, 'Dark Choco, Butterscotch Syrup, Sea Cream Whip, Freshmilk with ice', NULL, 'Terlaris', 1, 21, '4.9', 105, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(23, 'Salt Choco Sans', 'Salt Choco Sans', 4, 36000, 0, NULL, NULL, 1, 36000, 'Dark choco, Syrup Caramel, Syrup Salted Caramel, Freshmilk, Sea Cream Whip with ice', NULL, NULL, 1, 22, '4.8', 85, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(24, 'Ice Chocolate Pw', 'Ice Chocolate Pw', 4, 30000, 0, NULL, NULL, 1, 30000, 'Dark Choco, Water, Simple Syrup, Creamer with ice', NULL, NULL, 1, 23, '4.7', 75, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(25, 'Cheese YGY', 'Cheese YGY', 4, 37000, 0, NULL, NULL, 1, 37000, 'Cheese Biscuit, Vanilla Syrup, Sea Cream Whip, Freshmilk, Topping Caramel with ice', 'menus/vJCEMKDADOSTckYVBEXJ6sa6lJDPhVHWwMWCQUuy.jpg', 'Hits', 1, 24, '4.8', 95, '2026-06-07 02:20:09', '2026-07-05 08:13:38'),
(26, 'Cookies & Cream', 'Cookies & Cream', 4, 33000, 0, NULL, NULL, 1, 33000, 'Oreo Biscuit, Syrup Vanilla, Sea Cream Whip, Fresh Milk with ice', 'menus/AWvimHIynHC70DI18zAYQxFWPUq4fPblGPGeX1zs.jpg', NULL, 1, 25, '4.9', 115, '2026-06-07 02:20:09', '2026-07-05 08:13:48'),
(27, 'Lotus Gamon', 'Lotus Gamon', 4, 38000, 0, NULL, NULL, 1, 38000, 'Lotus Biscuit, Syrup Butterscotch, sea Cream Whip, Freshmilk with ice', 'menus/QojCxfawNn0h5S8SK80hV6VoHWbNQ6QvpNpuaYCI.jpg', NULL, 1, 26, '4.8', 88, '2026-06-07 02:20:09', '2026-07-05 08:13:56'),
(28, 'Lychee Tea', 'Lychee Tea', 5, 29000, 0, NULL, NULL, 1, 29000, 'Black Tea, Lychee powder, Lychee fruit, Simple Syrup with ice', 'menus/5okZRzDwreDooZtF2xqqe6ozIAVPXzKPGGeXmVyj.jpg', NULL, 1, 27, '4.7', 65, '2026-06-07 02:20:09', '2026-07-05 08:14:04'),
(29, 'Lemon Tea', 'Lemon Tea', 5, 29000, 0, NULL, NULL, 1, 29000, 'Black Tea, Lemon Powder, Simple Syrup with ice', 'menus/3ufKFGfmt52rQHWLYkSiqfHjQQKffg6ajD5rI66K.jpg', NULL, 1, 28, '4.6', 50, '2026-06-07 02:20:09', '2026-07-05 08:14:14'),
(30, 'Manggo Tea', 'Manggo Tea', 5, 30000, 0, NULL, NULL, 1, 30000, 'Black tea, Manggo Syrup, Simple Syrup with ice', 'menus/bHbTS6IdpbximjMSZAIka8lEZlm6Ak0jnisTkMDF.jpg', NULL, 1, 29, '4.8', 70, '2026-06-07 02:20:09', '2026-07-05 08:14:32'),
(31, 'Lemonade Berry', 'Lemonade Berry', 5, 33000, 0, NULL, NULL, 1, 33000, 'Lemon Powder, water, Strawberry Jam. Simple Syrup, with ice', 'menus/hVXac2mbc8dCBtIWUyVutRYt98DzlL3Ic0Kqs2nN.jpg', NULL, 1, 30, '4.7', 55, '2026-06-07 02:20:09', '2026-07-05 08:14:43'),
(32, 'Passion Fruit Ucul', 'Passion Fruit Ucul', 5, 28000, 0, NULL, NULL, 1, 28000, 'Lemon Powder, Water, Markisa Syrup, Simple Syrup with ice', 'menus/ZJiUflrVNLdEV9d781oTnjRS1HYU5td7DAAOoJ2D.jpg', NULL, 1, 31, '4.6', 40, '2026-06-07 02:20:09', '2026-07-05 08:14:53'),
(33, 'Milk Tea', 'Milk Tea', 5, 29000, 1, 29000, 'Tea, Condense Milk', 0, NULL, NULL, NULL, NULL, 1, 32, '4.5', 35, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(34, 'Matcha Flanky', 'Matcha Flanky', 6, 38000, 0, NULL, NULL, 1, 38000, 'Sea Salt, Whip Cream, Freshmilk, Condense Milk, Matcha Powder with ice', 'menus/Nf6qDEoV4BnQMI1hz7WWvolHKLDsdaDdbqhFqneP.jpg', 'Terlaris', 1, 33, '4.9', 90, '2026-06-07 02:20:09', '2026-07-05 08:15:16'),
(35, 'Matcha Earl Grey', 'Matcha Earl Grey', 6, 38000, 0, NULL, NULL, 1, 38000, 'Matcha Powder, Freshmilk, Whip Cream, Condense Milk, earl Grey with ice', NULL, NULL, 1, 34, '4.8', 85, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(36, 'Strawberry Matcha', 'Strawberry Matcha', 6, 36000, 0, NULL, NULL, 1, 36000, 'Strawberry Pure, Strawberry Milk, Whip Cream, Freshmilk, Condense Milk, Matcha powder with ice', 'menus/OkLb3BB2oGGfA8G39MAO7VKWcCAKDHIHrxRiAmXI.jpg', 'Hits', 1, 35, '4.9', 110, '2026-06-07 02:20:09', '2026-07-05 08:15:36'),
(37, 'Matcha Latte', 'Matcha Latte', 6, 31000, 0, NULL, NULL, 1, 31000, 'Matcha Powder, Freshmilk, Condense Milk, Whip Cream with ice', 'menus/yoYCscydG4g0oIC0iDamwv08CtzBQFdDOCoLd4DC.jpg', NULL, 1, 36, '4.7', 75, '2026-06-07 02:20:09', '2026-07-05 08:15:46'),
(38, 'Matcha Sparkling', 'Matcha Sparkling', 6, 35000, 0, NULL, NULL, 1, 35000, 'Matcha Powder, Freshmilk, Condense Milk, Whip Cream, Soda Water with ice', 'menus/h2MtyBqmh9xlhUXRVZqA0is0dHsxgWksvE8SSxNN.jpg', NULL, 1, 37, '4.8', 80, '2026-06-07 02:20:09', '2026-07-05 08:15:55'),
(39, 'French Fries', 'French Fries', 7, 33000, 0, NULL, NULL, 0, NULL, NULL, 'menus/QOyoU2CEMAElCsCwlhuxssLbfXnjTOYrow7uj6Yg.jpg', NULL, 1, 38, '4.6', 40, '2026-06-07 02:20:09', '2026-07-05 08:16:06'),
(40, 'Cheese Fries', 'Cheese Fries', 7, 38000, 0, NULL, NULL, 0, NULL, NULL, 'menus/Ei9gwvPJvv2adPCSFC8xFsIm3JtEOjdACDmWpQhY.jpg', 'Hits', 1, 39, '4.8', 55, '2026-06-07 02:20:09', '2026-07-05 08:16:25'),
(41, 'Miso Honey Chicken Wings', 'Miso Honey Chicken Wings', 7, 48000, 0, NULL, NULL, 0, NULL, NULL, 'menus/L8b2uGLhqxc0oDv7ziyCPUfGrJzFlHEjQe21CGAB.jpg', 'Premium', 1, 40, '4.9', 70, '2026-06-07 02:20:09', '2026-07-05 08:16:35'),
(42, 'OG Chicken Wings', 'OG Chicken Wings', 7, 42000, 0, NULL, NULL, 0, NULL, NULL, 'menus/RVe3lGGIjPySP0K81ORxzVrSwrET8h149ydiyhbs.jpg', NULL, 1, 41, '4.7', 45, '2026-06-07 02:20:09', '2026-07-05 08:17:12'),
(43, 'Popcorn Chicken', 'Popcorn Chicken', 7, 48000, 0, NULL, NULL, 0, NULL, NULL, 'menus/SXMyOVwxjO62kF5INXRZcYt581KECK0bCw0gN0pp.jpg', NULL, 1, 42, '4.8', 60, '2026-06-07 02:20:09', '2026-07-05 08:17:23'),
(44, 'Skena Platter', 'Skena Platter', 7, 58000, 0, NULL, NULL, 0, NULL, NULL, 'menus/E1PtcNlMjbICsd1ifMBu4QpwZarZ0qSqZiTdUk4G.jpg', 'Terlaris', 1, 43, '4.9', 85, '2026-06-07 02:20:09', '2026-07-05 08:17:35'),
(45, 'Italian Potato Nachos', 'Italian Potato Nachos', 7, 42000, 0, NULL, NULL, 0, NULL, NULL, 'menus/UDPkoPucA18cnBbDhycAUiqeijDD1hDuPFMTr6fU.jpg', NULL, 1, 44, '4.7', 50, '2026-06-07 02:20:09', '2026-07-05 08:17:47'),
(46, 'Beef Burger', 'Beef Burger', 7, 45000, 0, NULL, NULL, 0, NULL, NULL, 'menus/laqAGxAoAJD4B9KNbsKUW2pCVSfsqK8i2aQr84YK.jpg', NULL, 1, 45, '4.8', 65, '2026-06-07 02:20:09', '2026-07-05 08:18:06'),
(47, 'Spring Roll', 'Spring Roll', 7, 31000, 0, NULL, NULL, 0, NULL, NULL, 'menus/405CnbNi1FqdoX8j1xcqOiLpq0phFY7qbQWdsek6.jpg', NULL, 1, 46, '4.6', 35, '2026-06-07 02:20:09', '2026-07-05 08:18:27'),
(48, 'Classic Indonesian Plater', 'Classic Indonesian Plater', 7, 38000, 0, NULL, NULL, 0, NULL, NULL, 'menus/REccU0cXr5HMb0AvMkZip22pDORWYfghYgv4GzRe.jpg', NULL, 1, 47, '4.7', 40, '2026-06-07 02:20:09', '2026-07-05 08:18:38'),
(49, 'Aglio e Olio Chicken', 'Aglio e Olio Chicken', 8, 48000, 0, NULL, NULL, 0, NULL, NULL, 'menus/7OzaVd19rVOn8QvIRdECtGdD93ME4mjCUsPuvyTL.jpg', NULL, 1, 48, '4.8', 60, '2026-06-07 02:20:09', '2026-07-05 08:18:50'),
(50, 'Cream Alfredo', 'Cream Alfredo', 8, 58000, 0, NULL, NULL, 0, NULL, NULL, 'menus/oRafrgos1WQlhbhSmuoUr613480ISU7EYn40UlqO.jpg', 'Premium', 1, 49, '4.9', 75, '2026-06-07 02:20:09', '2026-07-05 08:19:04'),
(51, 'Black Pasta with Seafood', 'Black Pasta with Seafood', 8, 68000, 0, NULL, NULL, 0, NULL, NULL, 'menus/jJ2HDYPtqlxpxcJQyVdsCKztKdJMpRnZoPZORLwF.jpg', 'Terlaris', 1, 50, '4.9', 80, '2026-06-07 02:20:09', '2026-07-05 08:19:18'),
(52, 'Nasi Goreng', 'Nasi Goreng', 9, 58000, 0, NULL, NULL, 0, NULL, NULL, 'menus/SR4QelS9n0B0YqrPs3kb3MJJ6MROJD6u9yJzFma2.jpg', NULL, 1, 51, '4.7', 65, '2026-06-07 02:20:09', '2026-07-05 08:19:31'),
(53, 'Chicken Nanban', 'Chicken Nanban', 9, 58000, 0, NULL, NULL, 0, NULL, NULL, 'menus/MrcjuEaAbTyKZgM358JAz6Y7ykz3k7qQa6iiZXq4.jpg', NULL, 1, 52, '4.8', 70, '2026-06-07 02:20:09', '2026-07-05 08:19:42'),
(54, 'Beef Bowl', 'Beef Bowl', 9, 68000, 0, NULL, NULL, 0, NULL, NULL, 'menus/7juKfW4hcNBxN2Z7pjQekYpSPvIGwW96vcsTlIJC.jpg', 'Terlaris', 1, 53, '4.9', 85, '2026-06-07 02:20:09', '2026-07-05 08:19:54'),
(55, 'Soto Betawi', 'Soto Betawi', 9, 72000, 0, NULL, NULL, 0, NULL, NULL, 'menus/QGwiEnedDzK0aoPF6aHNIkFmrAjcZ3TIPOzbkElu.jpg', NULL, 1, 54, '4.8', 60, '2026-06-07 02:20:09', '2026-07-05 08:20:09'),
(56, 'Sate Ayam', 'Sate Ayam', 9, 45000, 0, NULL, NULL, 0, NULL, NULL, 'menus/4VcDs68AbaceMttDr5naHapkKiCekLDBuoylqJb1.jpg', NULL, 1, 55, '4.7', 55, '2026-06-07 02:20:09', '2026-07-05 08:20:20'),
(57, 'Lodeh Grill Ribs', 'Lodeh Grill Ribs', 9, 70000, 0, NULL, NULL, 0, NULL, NULL, 'menus/Pw5lg3VWGs3xT3EUh6yRp9PUQgbuLVNGT5glWUeV.jpg', 'Premium', 1, 56, '4.9', 75, '2026-06-07 02:20:09', '2026-07-05 08:20:31'),
(58, 'Ayam Bakar Sambal Dadak', 'Ayam Bakar Sambal Dadak', 9, 45000, 0, NULL, NULL, 0, NULL, NULL, 'menus/2AzK4Fj1HKUzMMwLgY4e0jkixyJhxzFCO5ytyDW0.jpg', NULL, 1, 57, '4.8', 65, '2026-06-07 02:20:09', '2026-07-05 08:20:42'),
(59, 'Holly Meat', 'Holly Meat', 10, 85000, 0, NULL, NULL, 0, NULL, NULL, 'menus/LipIvwJ3d8qC4aLd2fTcBQOUDEqZMFpunGRWf291.jpg', 'Terlaris', 1, 58, '4.9', 80, '2026-06-07 02:20:09', '2026-07-05 08:20:56'),
(60, 'Truffle Mushroom', 'Truffle Mushroom', 10, 80000, 0, NULL, NULL, 0, NULL, NULL, 'menus/1CUcLSyKBomuRx7U66Hi3RDuWcsnVc8BCv4r0Cqj.jpg', 'Premium', 1, 59, '4.8', 65, '2026-06-07 02:20:09', '2026-07-05 08:21:12'),
(61, 'Margaritha', 'Margaritha', 10, 68000, 0, NULL, NULL, 0, NULL, NULL, 'menus/cySLszLS30quUwNgCKESRfp1qlgkQb1MUiijISng.jpg', NULL, 1, 60, '4.7', 50, '2026-06-07 02:20:09', '2026-07-05 08:21:25'),
(62, 'Pepperoni', 'Pepperoni', 10, 75000, 0, NULL, NULL, 0, NULL, NULL, 'menus/nAwXruDvJE5VwKtbAj0n4gH7QmF4xveMbnvp5U9z.jpg', 'Hits', 1, 61, '4.8', 70, '2026-06-07 02:20:09', '2026-07-05 08:21:39'),
(63, 'Chocolate Tiramisu', 'Classic Tiramisu Cake with Glass', 11, 35000, 0, NULL, NULL, 0, NULL, NULL, 'menus/fdBspmT05EDvlnIuG7vDqXQgFN61SuttwVwWmR8r.jpg', NULL, 1, 62, '4.8', 55, '2026-06-07 02:20:09', '2026-07-05 08:21:51'),
(64, 'Cheese Cake', 'Burn Cheese Cake with Ice Cream', 11, 45000, 0, NULL, NULL, 0, NULL, NULL, 'menus/7t8fQ5fZ4fgi2x7WTklauanZPaEMqnCrIvyMSjfy.jpg', 'Hits', 1, 63, '4.9', 80, '2026-06-07 02:20:09', '2026-07-05 08:22:03'),
(65, 'Matcha Strawberry', 'Matcha Strawberry Cake', 11, 45000, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, 64, '4.7', 60, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(66, 'Chocolate Cake', 'Chocolate paste Tulip, Butter President/Anchor un', 11, 45000, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, 65, '4.8', 45, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(67, 'Matcha Cheese Cake', 'Matcha Powder, Cream Cheese, Ice Cream', 11, 55000, 0, NULL, NULL, 0, NULL, NULL, NULL, 'Premium', 1, 66, '4.9', 70, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(68, 'Pannaconta', 'Fresh Milk, Milad Gold, Vanilla Essence, Ice Cream', 11, 35000, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, 67, '4.6', 35, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(69, 'Red Velvet Cake', 'Spon Cake, Milac Gold, Cream Cheese, Icing Sugar, White Butter', 11, 45000, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, 68, '4.8', 50, '2026-06-07 02:20:09', '2026-06-07 02:20:09'),
(70, 'Dadar Cheese Cream', 'Dadar Cheese Cream', 11, 35000, 0, NULL, NULL, 0, NULL, NULL, 'menus/fJnKWZ73hZpOHkO35NVxugINcckb8MiLPxXQxYW0.jpg', NULL, 1, 69, '4.7', 40, '2026-06-07 02:20:09', '2026-07-05 08:23:04'),
(71, 'Air Mineral', 'Air Mineral', 12, 18000, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, 70, '4.5', 20, '2026-06-07 02:20:09', '2026-06-07 02:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_07_091109_create_admin_users_table', 1),
(5, '2026_06_07_091124_create_categories_table', 1),
(6, '2026_06_07_091125_create_menus_table', 1),
(7, '2026_06_07_114044_create_orders_table', 2),
(8, '2026_07_03_000001_add_role_to_admin_users_table', 3),
(9, '2026_07_21_000001_add_kasir_role_to_admin_users_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items` json NOT NULL,
  `subtotal` int NOT NULL,
  `tax` int NOT NULL,
  `total` int NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qris',
  `midtrans_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_id`, `customer_name`, `customer_phone`, `customer_email`, `table_number`, `items`, `subtotal`, `tax`, `total`, `payment_method`, `midtrans_order_id`, `midtrans_token`, `midtrans_redirect_url`, `midtrans_transaction_id`, `midtrans_payment_type`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 'SKENA-20260607-OUDAIA', 'Dika', NULL, NULL, '1', '[{\"id\": \"3-hot\", \"qty\": 2, \"name\": \"Caffe Latte\", \"price\": 31000, \"variant\": \"Hot\"}]', 62000, 6200, 68200, 'qris', 'SKENA-20260607-OUDAIA', '182168d8-00bb-4dee-9b0c-c1df1c5b17c7', 'https://app.sandbox.midtrans.com/snap/v4/redirection/d6265e29-fb64-4675-b416-e14ead452350', NULL, NULL, 'pending', NULL, '2026-06-07 03:46:08', '2026-06-07 03:46:08'),
(2, 'SKENA-20260607-SBITZ4', 'dika', NULL, NULL, '1', '[{\"id\": \"3-hot\", \"qty\": 2, \"name\": \"Caffe Latte\", \"price\": 31000, \"variant\": \"Hot\"}]', 62000, 6200, 68200, 'qris', 'SKENA-20260607-SBITZ4', '2ee46acb-be89-4f98-8f6b-bd1d82841c3b', 'https://app.sandbox.midtrans.com/snap/v4/redirection/2ee46acb-be89-4f98-8f6b-bd1d82841c3b', NULL, NULL, 'pending', NULL, '2026-06-07 03:49:35', '2026-06-07 03:49:35'),
(3, 'SKENA-20260607-ZVRVFS', 'dk', NULL, NULL, '1', '[{\"id\": \"3-hot\", \"qty\": 2, \"name\": \"Caffe Latte\", \"price\": 31000, \"variant\": \"Hot\"}]', 62000, 6200, 68200, 'qris', 'SKENA-20260607-ZVRVFS', '32d5f5c5-1de1-49aa-b6de-f7a764f8134f', 'https://app.sandbox.midtrans.com/snap/v4/redirection/32d5f5c5-1de1-49aa-b6de-f7a764f8134f', NULL, NULL, 'done', '2026-06-07 03:58:30', '2026-06-07 03:57:52', '2026-06-07 04:21:49'),
(4, 'SKENA-20260607-OSDTF4', 'ok', NULL, NULL, '1', '[{\"id\": \"7-ice\", \"qty\": 1, \"name\": \"Americano Strawberry\", \"price\": 33000, \"variant\": \"Ice\"}]', 33000, 3300, 36300, 'qris', 'SKENA-20260607-OSDTF4', '41c42e33-801a-4e4b-a9e3-fde3c5288ccf', 'https://app.sandbox.midtrans.com/snap/v4/redirection/41c42e33-801a-4e4b-a9e3-fde3c5288ccf', NULL, NULL, 'done', '2026-06-07 04:03:05', '2026-06-07 04:02:44', '2026-06-07 04:21:41'),
(5, 'SKENA-20260607-POVCCN', 'Wandi', NULL, NULL, '1', '[{\"id\": \"2-hot\", \"qty\": 1, \"name\": \"Americano\", \"price\": 25000, \"variant\": \"Hot\"}, {\"id\": \"7-ice\", \"qty\": 1, \"name\": \"Americano Strawberry\", \"price\": 33000, \"variant\": \"Ice\"}, {\"id\": \"12-ice\", \"qty\": 1, \"name\": \"Mocha Deep Talk\", \"price\": 41000, \"variant\": \"Ice\"}]', 99000, 9900, 108900, 'qris', 'SKENA-20260607-POVCCN', 'c7f442e5-a88c-4f3f-929b-fbb2d7052a99', 'https://app.sandbox.midtrans.com/snap/v4/redirection/c7f442e5-a88c-4f3f-929b-fbb2d7052a99', NULL, NULL, 'done', '2026-06-07 04:29:57', '2026-06-07 04:29:21', '2026-06-07 04:31:11'),
(6, 'SKENA-20260703-DHQC4U', 'Wandi 2', '08576434919', 'wandi@gmail.com', '3', '[{\"id\": \"4-ice\", \"qty\": 1, \"name\": \"Cappucino\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"8-ice\", \"qty\": 1, \"name\": \"Cranberry Black Sparkling\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"3-ice\", \"qty\": 1, \"name\": \"Caffe Latte\", \"price\": 32000, \"variant\": \"Ice\"}]', 96000, 9600, 105600, 'qris', 'SKENA-20260703-DHQC4U', '4e01e2ff-8cb0-4f0b-9217-32f0c47b66ad', 'https://app.sandbox.midtrans.com/snap/v4/redirection/4e01e2ff-8cb0-4f0b-9217-32f0c47b66ad', NULL, NULL, 'pending', NULL, '2026-07-02 21:25:29', '2026-07-02 21:25:29'),
(7, 'SKENA-20260703-H42EI2', 'testing', '086463733838', NULL, '2', '[{\"id\": \"2-hot\", \"qty\": 1, \"name\": \"Americano\", \"price\": 25000, \"variant\": \"Hot\"}, {\"id\": \"8-ice\", \"qty\": 1, \"name\": \"Cranberry Black Sparkling\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"16-ice\", \"qty\": 1, \"name\": \"Ice Coffee Baileys\", \"price\": 39000, \"variant\": \"Ice\"}, {\"id\": \"20-ice\", \"qty\": 1, \"name\": \"Strawberry Snow Cream\", \"price\": 34000, \"variant\": \"Ice\"}]', 130000, 13000, 143000, 'qris', 'SKENA-20260703-H42EI2', '9af59c56-e32b-4d51-b968-c89a22e063f1', 'https://app.sandbox.midtrans.com/snap/v4/redirection/9af59c56-e32b-4d51-b968-c89a22e063f1', NULL, NULL, 'done', '2026-07-02 22:11:08', '2026-07-02 22:10:50', '2026-07-02 22:11:34'),
(8, 'SKENA-20260703-YV4SRD', 'dk', '08562864888', NULL, '3', '[{\"id\": \"18-ice\", \"qty\": 1, \"name\": \"Cream Bruelle Gwenchana\", \"price\": 35000, \"variant\": \"Ice\"}, {\"id\": 42, \"qty\": 1, \"name\": \"OG Chicken Wings\", \"price\": 42000, \"variant\": null}, {\"id\": \"29-ice\", \"qty\": 1, \"name\": \"Lemon Tea\", \"price\": 29000, \"variant\": \"Ice\"}]', 106000, 10600, 116600, 'qris', 'SKENA-20260703-YV4SRD', '693d1e39-5ff7-4d25-baf6-1c516717c636', 'https://app.sandbox.midtrans.com/snap/v4/redirection/693d1e39-5ff7-4d25-baf6-1c516717c636', NULL, NULL, 'pending', NULL, '2026-07-02 22:14:15', '2026-07-02 22:14:15'),
(9, 'SKENA-20260703-H4LLJW', 'bb', '0099000990', NULL, '3', '[{\"id\": \"11-ice\", \"qty\": 1, \"name\": \"Caramel Machiato Slay\", \"price\": 33000, \"variant\": \"Ice\"}]', 33000, 3300, 36300, 'qris', 'SKENA-20260703-H4LLJW', 'dbf92772-a3c4-46ff-a41d-bf3d0cf9ee1b', 'https://app.sandbox.midtrans.com/snap/v4/redirection/dbf92772-a3c4-46ff-a41d-bf3d0cf9ee1b', NULL, NULL, 'done', '2026-07-02 22:16:06', '2026-07-02 22:15:44', '2026-07-02 22:19:50'),
(10, 'SKENA-20260703-IDFHUH', 'rem', '08754646464', NULL, '1', '[{\"id\": \"2-ice\", \"qty\": 1, \"name\": \"Americano\", \"price\": 31000, \"variant\": \"Ice\"}, {\"id\": \"34-ice\", \"qty\": 1, \"name\": \"Matcha Flanky\", \"price\": 38000, \"variant\": \"Ice\"}, {\"id\": \"35-ice\", \"qty\": 1, \"name\": \"Matcha Earl Grey\", \"price\": 38000, \"variant\": \"Ice\"}]', 107000, 10700, 117700, 'qris', 'SKENA-20260703-IDFHUH', '05bd03de-696d-4b6f-8b6a-8668cb46db63', 'https://app.sandbox.midtrans.com/snap/v4/redirection/05bd03de-696d-4b6f-8b6a-8668cb46db63', NULL, NULL, 'done', '2026-07-02 23:19:28', '2026-07-02 23:18:41', '2026-07-02 23:20:04'),
(11, 'SKENA-20260703-EM1WDE', 'Wandi', '0875454545', NULL, '3', '[{\"id\": \"2-ice\", \"qty\": 1, \"name\": \"Americano\", \"price\": 31000, \"variant\": \"Ice\"}, {\"id\": 49, \"qty\": 1, \"name\": \"Aglio e Olio Chicken\", \"price\": 48000, \"variant\": null}, {\"id\": \"34-ice\", \"qty\": 2, \"name\": \"Matcha Flanky\", \"price\": 38000, \"variant\": \"Ice\"}]', 155000, 15500, 170500, 'qris', 'SKENA-20260703-EM1WDE', '41142ba1-3a88-4a42-b27f-36ad066ff4cd', 'https://app.sandbox.midtrans.com/snap/v4/redirection/41142ba1-3a88-4a42-b27f-36ad066ff4cd', NULL, NULL, 'done', '2026-07-02 23:56:25', '2026-07-02 23:55:29', '2026-07-02 23:56:48'),
(12, 'SKENA-20260721-JXPHCU', 'Asrini Muhsin', '083737737373', 'asrinimuhsin@gmail.com', '15', '[{\"id\": \"6-ice\", \"qty\": 1, \"name\": \"Apple Black Sparkling\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"1-hot\", \"qty\": 1, \"name\": \"Espresso\", \"price\": 15000, \"variant\": \"Hot\"}, {\"id\": \"2-hot\", \"qty\": 1, \"name\": \"Americano\", \"price\": 25000, \"variant\": \"Hot\"}, {\"id\": \"10-hot\", \"qty\": 1, \"name\": \"Coffee LDR\", \"price\": 30000, \"variant\": \"Hot\"}]', 102000, 10200, 112200, 'qris', 'SKENA-20260721-JXPHCU', '8a2f1200-80f1-4127-8d8e-557819a0a93f', 'https://app.sandbox.midtrans.com/snap/v4/redirection/8a2f1200-80f1-4127-8d8e-557819a0a93f', NULL, NULL, 'done', '2026-07-21 00:54:59', '2026-07-21 00:54:32', '2026-07-21 00:56:31'),
(13, 'SKENA-20260721-WXMYD8', 'Riliandi Saputra', '0863737737373', 'riliandapabundu@gmail.com', '15', '[{\"id\": \"3-ice\", \"qty\": 1, \"name\": \"Caffe Latte\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"9-ice\", \"qty\": 1, \"name\": \"Guava Black Sparkling\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"12-hot\", \"qty\": 1, \"name\": \"Mocha Deep Talk\", \"price\": 34000, \"variant\": \"Hot\"}, {\"id\": \"1-hot\", \"qty\": 1, \"name\": \"Espresso\", \"price\": 15000, \"variant\": \"Hot\"}]', 113000, 11300, 124300, 'qris', 'SKENA-20260721-WXMYD8', '4a4dc6f9-879c-4d00-92cc-5d5b6934af0c', 'https://app.sandbox.midtrans.com/snap/v4/redirection/4a4dc6f9-879c-4d00-92cc-5d5b6934af0c', NULL, NULL, 'pending', NULL, '2026-07-21 01:07:57', '2026-07-21 01:07:57'),
(14, 'SKENA-20260721-6B1P5O', 'Saputra', '08373773737', 'mangimangidaeng@gmail.com', '7', '[{\"id\": \"3-ice\", \"qty\": 1, \"name\": \"Caffe Latte\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"9-ice\", \"qty\": 1, \"name\": \"Guava Black Sparkling\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"12-hot\", \"qty\": 1, \"name\": \"Mocha Deep Talk\", \"price\": 34000, \"variant\": \"Hot\"}, {\"id\": \"1-hot\", \"qty\": 1, \"name\": \"Espresso\", \"price\": 15000, \"variant\": \"Hot\"}, {\"id\": \"6-ice\", \"qty\": 1, \"name\": \"Apple Black Sparkling\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"10-ice\", \"qty\": 1, \"name\": \"Coffee LDR\", \"price\": 32000, \"variant\": \"Ice\"}, {\"id\": \"19-ice\", \"qty\": 5, \"name\": \"Cream Manggo Coco Citrus\", \"price\": 34000, \"variant\": \"Ice\"}, {\"id\": \"36-ice\", \"qty\": 1, \"name\": \"Strawberry Matcha\", \"price\": 36000, \"variant\": \"Ice\"}]', 383000, 38300, 421300, 'qris', 'SKENA-20260721-6B1P5O', '25b73f24-67be-4635-96f2-fb66d5c80bc0', 'https://app.sandbox.midtrans.com/snap/v4/redirection/25b73f24-67be-4635-96f2-fb66d5c80bc0', NULL, NULL, 'done', '2026-07-21 01:09:28', '2026-07-21 01:09:13', '2026-07-21 01:10:16'),
(15, 'SKENA-20260721-HG6EHO', 'rini', '0877474774', NULL, '15', '[{\"id\": \"3-ice\", \"qty\": 1, \"name\": \"Caffe Latte\", \"price\": 32000, \"variant\": \"Ice\", \"image_url\": \"http://127.0.0.1:8000/storage/menus/0iuHrjhir4bDtYQiY5OZ78iOfw8UKXpKcDJ1KzjJ.jpg\"}]', 32000, 3200, 35200, 'qris', 'SKENA-20260721-HG6EHO', '76757ac7-6cbc-4d4f-a1b9-848d45c76b6b', 'https://app.midtrans.com/snap/v4/redirection/76757ac7-6cbc-4d4f-a1b9-848d45c76b6b', NULL, NULL, 'pending', NULL, '2026-07-21 01:23:23', '2026-07-21 01:23:23'),
(16, 'SKENA-20260721-DV8TIN', 'rini', '0877474774', NULL, '15', '[{\"id\": \"3-ice\", \"qty\": 1, \"name\": \"Caffe Latte\", \"price\": 32000, \"variant\": \"Ice\", \"image_url\": \"http://127.0.0.1:8000/storage/menus/0iuHrjhir4bDtYQiY5OZ78iOfw8UKXpKcDJ1KzjJ.jpg\"}]', 32000, 3200, 35200, 'qris', 'SKENA-20260721-DV8TIN', 'e85fd733-2084-42a0-93e1-b847a37e09bd', 'https://app.midtrans.com/snap/v4/redirection/e85fd733-2084-42a0-93e1-b847a37e09bd', NULL, NULL, 'pending', NULL, '2026-07-21 01:43:18', '2026-07-21 01:43:18'),
(17, 'SKENA-20260721-NBD0ZL', 'saaaaaaaa', NULL, NULL, '15', '[{\"id\": \"11-ice\", \"qty\": 1, \"name\": \"Caramel Machiato Slay\", \"price\": 33000, \"variant\": \"Ice\", \"image_url\": \"http://127.0.0.1:8000/storage/menus/miG4IAQcBC06YMez2LQ9GOv8JzxJ4WDKRlCgoaSx.jpg\"}]', 33000, 3300, 36300, 'qris', 'SKENA-20260721-NBD0ZL', 'a7afbbf8-6032-4e1e-8e5e-cb0681cff153', 'https://app.sandbox.midtrans.com/snap/v4/redirection/a7afbbf8-6032-4e1e-8e5e-cb0681cff153', NULL, NULL, 'pending', NULL, '2026-07-21 02:45:00', '2026-07-21 02:45:00'),
(18, 'SKENA-20260721-JKUEGP', 'kllll', NULL, NULL, '15', '[{\"id\": \"11-ice\", \"qty\": 1, \"name\": \"Caramel Machiato Slay\", \"price\": 33000, \"variant\": \"Ice\", \"image_url\": \"http://127.0.0.1:8000/storage/menus/miG4IAQcBC06YMez2LQ9GOv8JzxJ4WDKRlCgoaSx.jpg\"}]', 33000, 3300, 36300, 'qris', 'SKENA-20260721-JKUEGP', '17cb5eef-8dd5-45e5-a003-752ff7248018', 'https://app.sandbox.midtrans.com/snap/v4/redirection/17cb5eef-8dd5-45e5-a003-752ff7248018', NULL, NULL, 'done', '2026-07-21 02:46:54', '2026-07-21 02:46:25', '2026-07-21 02:47:55'),
(19, 'SKENA-20260721-PESJAY', 'kmkm', NULL, NULL, '1', '[{\"id\": \"3-hot\", \"qty\": 1, \"name\": \"Caffe Latte\", \"price\": 31000, \"variant\": \"Hot\", \"image_url\": \"http://127.0.0.1:8000/storage/menus/0iuHrjhir4bDtYQiY5OZ78iOfw8UKXpKcDJ1KzjJ.jpg\"}]', 31000, 3100, 34100, 'qris', 'SKENA-20260721-PESJAY', '582ae725-8e73-465c-b004-968dde096430', 'https://app.sandbox.midtrans.com/snap/v4/redirection/582ae725-8e73-465c-b004-968dde096430', NULL, NULL, 'paid', '2026-07-21 03:01:40', '2026-07-21 03:01:13', '2026-07-21 03:01:40');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_users_email_unique` (`email`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menus_category_id_foreign` (`category_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_id_unique` (`order_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
