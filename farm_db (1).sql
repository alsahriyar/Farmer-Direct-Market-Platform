-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 02, 2026 at 05:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `admin_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `admin_code`) VALUES
(1, '12731518');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crop_prices`
--

CREATE TABLE `crop_prices` (
  `id` int(11) NOT NULL,
  `crop_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_prices`
--

INSERT INTO `crop_prices` (`id`, `crop_name`, `price`, `updated_at`) VALUES
(1, 'Rice', 58.00, '2026-07-01 17:01:57'),
(2, 'Potato', 32.00, '2026-07-01 17:01:57'),
(3, 'Onion', 65.00, '2026-07-01 17:01:57'),
(4, 'Tomato', 40.00, '2026-07-01 17:01:57');

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rating` decimal(3,1) DEFAULT 4.5,
  `verified` tinyint(1) DEFAULT 1,
  `phone` varchar(15) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `name`, `rating`, `verified`, `phone`, `location`, `created_at`) VALUES
(1, 'Abdul Karim', 4.9, 1, NULL, 'Gazipur', '2026-07-01 13:59:57'),
(2, 'Rahman Mia', 4.7, 1, NULL, 'Narsingdi', '2026-07-01 13:59:57'),
(3, 'Hasan Ali', 4.8, 1, NULL, 'Mymensingh', '2026-07-01 13:59:57'),
(4, 'Nur Islam', 5.0, 1, NULL, 'Tangail', '2026-07-01 13:59:57'),
(8, 'Md Al Sahriyar Mim 1338 A', 4.5, 1, '01312731518', 'QF4Q+HMQ', '2026-07-03 07:22:44'),
(9, 'Md Al Sahriyar Mim 1338 A', 4.5, 1, '01312731518', 'QF4Q+HMQ', '2026-08-02 06:55:43'),
(12, 'Md Al Sahriyar Mim 1338 A', 4.5, 1, '01312731518', 'QF4Q+HMQ', '2026-08-02 08:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `featured_products`
--

CREATE TABLE `featured_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `total_amount`, `status`, `shipping_address`, `phone`, `created_at`) VALUES
(1, 11, 348.00, 'pending', NULL, NULL, '2026-08-02 08:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 25, 1, 58.00),
(2, 1, 17, 1, 120.00),
(3, 1, 26, 2, 85.00);

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price_history`
--

INSERT INTO `price_history` (`id`, `product_id`, `price`, `date`, `created_at`) VALUES
(1, 1, 38.00, '2026-06-24', '2026-07-01 14:23:54'),
(2, 1, 39.00, '2026-06-25', '2026-07-01 14:23:54'),
(3, 1, 37.00, '2026-06-26', '2026-07-01 14:23:54'),
(4, 1, 40.00, '2026-06-27', '2026-07-01 14:23:54'),
(5, 1, 42.00, '2026-06-28', '2026-07-01 14:23:54'),
(6, 1, 41.00, '2026-06-29', '2026-07-01 14:23:54'),
(7, 1, 40.00, '2026-06-30', '2026-07-01 14:23:54'),
(8, 2, 33.00, '2026-06-24', '2026-07-01 14:23:54'),
(9, 2, 32.00, '2026-06-25', '2026-07-01 14:23:54'),
(10, 2, 34.00, '2026-06-26', '2026-07-01 14:23:54'),
(11, 2, 31.00, '2026-06-27', '2026-07-01 14:23:54'),
(12, 2, 32.00, '2026-06-28', '2026-07-01 14:23:54'),
(13, 2, 32.00, '2026-06-29', '2026-07-01 14:23:54'),
(14, 2, 32.00, '2026-06-30', '2026-07-01 14:23:54'),
(15, 3, 55.00, '2026-06-24', '2026-07-01 14:23:54'),
(16, 3, 56.00, '2026-06-25', '2026-07-01 14:23:54'),
(17, 3, 57.00, '2026-06-26', '2026-07-01 14:23:54'),
(18, 3, 58.00, '2026-06-27', '2026-07-01 14:23:54'),
(19, 3, 59.00, '2026-06-28', '2026-07-01 14:23:54'),
(20, 3, 58.00, '2026-06-29', '2026-07-01 14:23:54'),
(21, 3, 58.00, '2026-06-30', '2026-07-01 14:23:54'),
(22, 4, 115.00, '2026-06-24', '2026-07-01 14:23:54'),
(23, 4, 118.00, '2026-06-25', '2026-07-01 14:23:54'),
(24, 4, 120.00, '2026-06-26', '2026-07-01 14:23:54'),
(25, 4, 125.00, '2026-06-27', '2026-07-01 14:23:54'),
(26, 4, 122.00, '2026-06-28', '2026-07-01 14:23:54'),
(27, 4, 120.00, '2026-06-29', '2026-07-01 14:23:54'),
(28, 4, 120.00, '2026-06-30', '2026-07-01 14:23:54'),
(29, 5, 32.00, '2026-06-24', '2026-07-01 14:23:54'),
(30, 5, 34.00, '2026-06-25', '2026-07-01 14:23:54'),
(31, 5, 35.00, '2026-06-26', '2026-07-01 14:23:54'),
(32, 5, 33.00, '2026-06-27', '2026-07-01 14:23:54'),
(33, 5, 36.00, '2026-06-28', '2026-07-01 14:23:54'),
(34, 5, 35.00, '2026-06-29', '2026-07-01 14:23:54'),
(35, 5, 35.00, '2026-06-30', '2026-07-01 14:23:54'),
(36, 6, 62.00, '2026-06-24', '2026-07-01 14:23:54'),
(37, 6, 65.00, '2026-06-25', '2026-07-01 14:23:54'),
(38, 6, 68.00, '2026-06-26', '2026-07-01 14:23:54'),
(39, 6, 65.00, '2026-06-27', '2026-07-01 14:23:54'),
(40, 6, 70.00, '2026-06-28', '2026-07-01 14:23:54'),
(41, 6, 68.00, '2026-06-29', '2026-07-01 14:23:54'),
(42, 6, 65.00, '2026-06-30', '2026-07-01 14:23:54'),
(43, 7, 75.00, '2026-06-24', '2026-07-01 14:23:54'),
(44, 7, 78.00, '2026-06-25', '2026-07-01 14:23:54'),
(45, 7, 82.00, '2026-06-26', '2026-07-01 14:23:54'),
(46, 7, 80.00, '2026-06-27', '2026-07-01 14:23:54'),
(47, 7, 85.00, '2026-06-28', '2026-07-01 14:23:54'),
(48, 7, 80.00, '2026-06-29', '2026-07-01 14:23:54'),
(49, 7, 80.00, '2026-06-30', '2026-07-01 14:23:54'),
(50, 8, 42.00, '2026-06-24', '2026-07-01 14:23:54'),
(51, 8, 45.00, '2026-06-25', '2026-07-01 14:23:54'),
(52, 8, 48.00, '2026-06-26', '2026-07-01 14:23:54'),
(53, 8, 45.00, '2026-06-27', '2026-07-01 14:23:54'),
(54, 8, 44.00, '2026-06-28', '2026-07-01 14:23:54'),
(55, 8, 45.00, '2026-06-29', '2026-07-01 14:23:54'),
(56, 8, 45.00, '2026-06-30', '2026-07-01 14:23:54'),
(57, 9, 28.00, '2026-06-24', '2026-07-01 14:23:54'),
(58, 9, 30.00, '2026-06-25', '2026-07-01 14:23:54'),
(59, 9, 32.00, '2026-06-26', '2026-07-01 14:23:54'),
(60, 9, 29.00, '2026-06-27', '2026-07-01 14:23:54'),
(61, 9, 30.00, '2026-06-28', '2026-07-01 14:23:54'),
(62, 9, 30.00, '2026-06-29', '2026-07-01 14:23:54'),
(63, 9, 30.00, '2026-06-30', '2026-07-01 14:23:54'),
(64, 10, 52.00, '2026-06-24', '2026-07-01 14:23:54'),
(65, 10, 55.00, '2026-06-25', '2026-07-01 14:23:54'),
(66, 10, 58.00, '2026-06-26', '2026-07-01 14:23:54'),
(67, 10, 55.00, '2026-06-27', '2026-07-01 14:23:54'),
(68, 10, 54.00, '2026-06-28', '2026-07-01 14:23:54'),
(69, 10, 55.00, '2026-06-29', '2026-07-01 14:23:54'),
(70, 10, 55.00, '2026-06-30', '2026-07-01 14:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT 'kg',
  `stock` int(11) DEFAULT 100,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `harvest_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `unit`, `stock`, `description`, `image`, `harvest_date`, `location`, `farmer_id`, `created_at`, `status`) VALUES
(1, 'Fresh Organic Tomato', 'Vegetable', 40.00, 'kg', 500, 'Freshly harvested organic tomatoes. No chemicals.', 'https://images.unsplash.com/photo-1592841200221-a6898f307baa?w=1000', '2026-06-15', 'Gazipur', 1, '2026-07-01 13:59:57', 'active'),
(2, 'Fresh Potato', 'Vegetable', 32.00, 'kg', 800, 'High quality local potatoes.', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=800', '2026-06-10', 'Narsingdi', 2, '2026-07-01 13:59:57', 'active'),
(3, 'Premium Rice', 'Grain', 58.00, 'kg', 300, 'Aromatic premium rice from Bangladesh.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800', '2026-06-01', 'Mymensingh', 3, '2026-07-01 13:59:57', 'active'),
(4, 'Organic Mango', 'Fruit', 120.00, 'kg', 150, 'Sweet and juicy organic mangoes.', 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=800', '2026-06-20', 'Tangail', 4, '2026-07-01 13:59:57', 'active'),
(5, 'Fresh Carrot', 'Vegetable', 35.00, 'kg', 250, 'Crunchy and fresh carrots.', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=800', '2026-06-12', 'Gazipur', 1, '2026-07-01 13:59:57', 'active'),
(6, 'Red Onion', 'Vegetable', 65.00, 'kg', 400, 'Fresh and spicy red onions from local farm.', 'https://images.unsplash.com/photo-1508747703725-719777637510?w=1000', '2026-06-18', 'Gazipur', 1, '2026-07-01 14:08:18', 'active'),
(7, 'Green Chili', 'Vegetable', 80.00, 'kg', 250, 'Hot and fresh green chilies.', 'https://images.unsplash.com/photo-1530908295418-a12e326966ba?w=1000', '2026-06-16', 'Narsingdi', 2, '2026-07-01 14:08:18', 'active'),
(8, 'Cauliflower', 'Vegetable', 45.00, 'piece', 180, 'Big and fresh white cauliflower.', 'https://images.unsplash.com/photo-1510627498534-cf7e9002facc?w=1000', '2026-06-14', 'Gazipur', 1, '2026-07-01 14:08:18', 'active'),
(9, 'Spinach (Palong Shak)', 'Vegetable', 30.00, 'kg', 300, 'Fresh and nutritious spinach leaves.', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=1000', '2026-06-17', 'Mymensingh', 3, '2026-07-01 14:08:18', 'active'),
(10, 'Banana (Sagor Kola)', 'Fruit', 55.00, 'dozen', 200, 'Sweet and fresh bananas.', 'https://images.unsplash.com/photo-1603833665858-e61d17a86224?w=1000', '2026-06-19', 'Tangail', 4, '2026-07-01 14:08:18', 'active'),
(11, 'Guava', 'Fruit', 70.00, 'kg', 120, 'Fresh and juicy guava.', 'https://images.unsplash.com/photo-1605027990121-cbae9e0642df?w=1000', '2026-06-15', 'Gazipur', 2, '2026-07-01 14:08:18', 'active'),
(12, 'Eggplant (Begun)', 'Vegetable', 40.00, 'kg', 350, 'Shiny and fresh eggplants.', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=1000', '2026-06-13', 'Narsingdi', 1, '2026-07-01 14:08:18', 'active'),
(13, 'Pumpkin', 'Vegetable', 35.00, 'piece', 90, 'Big and sweet pumpkin.', 'https://images.unsplash.com/photo-1502741338009-cac2772e18bc?w=1000', '2026-06-20', 'Mymensingh', 3, '2026-07-01 14:08:18', 'active'),
(14, 'Coconut', 'Fruit', 90.00, 'piece', 80, 'Fresh green coconut.', 'https://images.unsplash.com/photo-1519985176271-adb1088fa94c?w=1000\r\n', '2026-06-10', 'Tangail', 4, '2026-07-01 14:08:18', 'active'),
(15, 'Turmeric', 'Spices', 150.00, 'kg', 60, 'Pure local turmeric powder.', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=800', '2026-06-05', 'Gazipur', 2, '2026-07-01 14:08:18', 'active'),
(16, 'Ginger', 'Spices', 85.00, 'kg', 220, 'Fresh and aromatic ginger.', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=1000', '2026-06-12', 'Narsingdi', 1, '2026-07-01 14:08:18', 'active'),
(17, 'Garlic', 'Spices', 120.00, 'kg', 149, 'Fresh garlic cloves.', 'https://images.unsplash.com/photo-1540148426945-6cf22a6b2383?w=1000', '2026-06-11', 'Mymensingh', 3, '2026-07-01 14:08:18', 'active'),
(18, 'BRRI Dhan 28', 'Grain', 62.00, 'kg', 450, 'High yielding aromatic rice variety.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-10', 'Mymensingh', 3, '2026-07-01 14:46:50', 'active'),
(19, 'BRRI Dhan 29', 'Grain', 65.00, 'kg', 380, 'Popular fine rice with excellent taste.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=1000', '2026-06-12', 'Gazipur', 1, '2026-07-01 14:46:50', 'active'),
(20, 'BRRI Dhan 50', 'Grain', 70.00, 'kg', 220, 'Zinc enriched nutritious rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-15', 'Narsingdi', 2, '2026-07-01 14:46:50', 'active'),
(21, 'BRRI Dhan 74', 'Grain', 68.00, 'kg', 300, 'Flood tolerant high yielding rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-08', 'Mymensingh', 3, '2026-07-01 14:46:50', 'active'),
(22, 'Pajam Rice', 'Grain', 55.00, 'kg', 600, 'Traditional soft and tasty local rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-18', 'Tangail', 4, '2026-07-01 14:46:50', 'active'),
(23, 'Chinigura Rice', 'Grain', 95.00, 'kg', 150, 'Premium aromatic fine rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-20', 'Gazipur', 1, '2026-07-01 14:46:50', 'active'),
(24, 'Kalijira Rice', 'Grain', 110.00, 'kg', 80, 'Super aromatic rice used in special occasions.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-22', 'Narsingdi', 2, '2026-07-01 14:46:50', 'active'),
(25, 'Minikit Rice', 'Grain', 58.00, 'kg', 519, 'Short duration high yielding rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-05', 'Mymensingh', 3, '2026-07-01 14:46:50', 'active'),
(26, 'Basmati Rice', 'Grain', 85.00, 'kg', 178, 'Long grain fragrant basmati rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-25', 'Tangail', 4, '2026-07-01 14:46:50', 'active'),
(27, 'Black Rice', 'Grain', 120.00, 'kg', 60, 'Antioxidant rich nutritious black rice.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&fit=crop', '2026-06-28', 'Gazipur', 1, '2026-07-01 14:46:50', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `user_type` enum('farmer','admin','buyer') NOT NULL DEFAULT 'farmer',
  `status` enum('pending','active','blocked') DEFAULT 'pending',
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `farm_name` varchar(100) DEFAULT NULL,
  `farm_type` varchar(100) DEFAULT NULL,
  `admin_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `user_type`, `status`, `email`, `phone`, `address`, `profile_image`, `password`, `farm_name`, `farm_type`, `admin_code`, `created_at`) VALUES
(1, 'Sian Ahmmed', '', 'farmer', 'pending', 'sian.100503@gmail.com', '01312731518', 'dhaka', NULL, '$2y$10$J9ALh4lme/x.sdAORFhSZ.QXCXj0hWZsbRNbr.5ZsGFO12N0QYkFS', 'Md abul', 'Vegetables', '', '2026-07-02 16:46:47'),
(2, 'Àl Sährïýař', 'Àl Sährïýař', 'farmer', 'pending', 'alsahriyarmim@gmail.com', '01312731518', 'Dhaka', NULL, '$2y$10$ENcF/DQUwmeIV2YI412DGuGn3oViRs8bDiq0/Bq7bznas/eJ6q8c.', 'Md Al Sahriyar', 'Fruits', '', '2026-07-02 16:55:43'),
(3, 'Àl Sährïýař', 'Àl Sährïýař', 'farmer', 'pending', 'alsahriyar@gmail.com', '01312731518', 'Dhaka', NULL, '$2y$10$sYRrEnmtaC4RCYyf1wehHeK7JcJm5qBcIwIG5x/9hpxa6fuP/4GKi', 'Md Al Sahriyar', 'Fruits', '', '2026-07-02 16:59:26'),
(4, 'Sian Ahmmed', 'Sian Ahmmed', 'buyer', 'pending', 'sian.100505@gmail.com', '01312731518', 'Dhaka', NULL, '$2y$10$6IGIeB2QhoHV1aFbYFjl3e4h4AH3Qd.mDBBCXFwXe/jfTzuot4idi', '', '', '', '2026-07-02 17:01:28'),
(5, 'Sian Ahmmed', 'Sian Ahmmed', 'admin', 'pending', 'sian.100504@gmail.com', '01312731518', 'Dhaka', NULL, '$2y$10$WB8PpOW3BIxOFWMA50EtNO1OPMLocNxIikZO32AO1nAMRStAqFMY6', '', '', '12731518', '2026-07-02 17:13:42'),
(6, 'Md Al Sahriyar Mim 1338 A', 'Md Al Sahriyar Mim 1338 A', 'admin', 'active', 'alsahriyar5@gmail.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$Mv8hbBleCK/dXDa19NXXNOUtaOsYLrvhraC6KZ0iJ7WEJUrnRoBLC', '', '', '12731518', '2026-07-03 04:32:08'),
(7, 'Md Al Sahriyar', 'Md Al Sahriyar', 'buyer', 'pending', 'alsahriyar10@gmail.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$zvW67/72lIpvmRKGINwCb.Guh8W6o205sGOJdIRCW3ZVuos9wKK/i', '', '', '', '2026-07-03 07:20:31'),
(8, 'Md Al Sahriyar Mim 1338 A', 'Md Al Sahriyar Mim 1338 A', 'farmer', 'blocked', 'alsahriyar11@gmail.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$X/RUmCcNKPMBqARE3IAxF.mqAcPWz4YtaqmCGz/Pe/42Drv1RQpbi', 'ASM', 'Dairy', '', '2026-07-03 07:22:44'),
(9, 'Md Al Sahriyar Mim 1338 A', 'Md Al Sahriyar Mim 1338 A', 'farmer', 'pending', 'alsahriyar300@gmail.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$CBvUr/M31WyO9XZSsZUqXeqzQZq/EwVWTn8iqXhAuT015dvsgwWw2', 'ASM', 'Dairy', '', '2026-08-02 06:55:43'),
(10, 'Md Al Sahriyar Mim 1338 A', 'Md Al Sahriyar Mim 1338 A', 'buyer', 'pending', 'alsahriyar345@gmil.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$4hioxj9Jb60gLYh940b8ouVNpzBvRoq5CNhDATJTlHwG5EBDPIeBq', '', '', '', '2026-08-02 07:06:34'),
(11, 'Md Al Sahriyar Mim 1338 A', 'Md Al Sahriyar Mim 1338 A', 'buyer', 'pending', 'alsahriyar3@gmail.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$qlgGv57qRSLRR/f3VO//0ONiLFWPpCzLIcaaRlBjv9CWqcCZpEHH2', '', '', '', '2026-08-02 07:09:37'),
(12, 'Md Al Sahriyar Mim 1338 A', 'Md Al Sahriyar Mim 1338 A', 'farmer', 'pending', 'alsahriyar38@gmail.com', '01312731518', 'QF4Q+HMQ', NULL, '$2y$10$2.oLF/HvK7wMOe077SHjZuuwYDYV7owQKoO3ZeIgSeJo8jLdMLhGC', 'ASM', 'Fishery', '', '2026-08-02 08:10:58'),
(13, 'Àl Sährïýař', 'Àl Sährïýař', 'admin', 'pending', 'alsahriyar1338@gmail.com', '01312731518', 'Dhaka', NULL, '$2y$10$mW2BquW8X8ZS8k0ch/CKT./U5GUQAgdDUsSBJa.ziMI6GvtTTYgxO', '', '', '12731518', '2026-08-02 14:13:34');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `crop_prices`
--
ALTER TABLE `crop_prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `featured_products`
--
ALTER TABLE `featured_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `crop_prices`
--
ALTER TABLE `crop_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `featured_products`
--
ALTER TABLE `featured_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `price_history`
--
ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `price_history`
--
ALTER TABLE `price_history`
  ADD CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
