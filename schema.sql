-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2025 at 12:53 PM
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
-- Database: `hospital_queue`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `meta` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `meta`, `created_at`) VALUES
(5, 3, 'login', '{\"ip\":\"::1\"}', '2025-08-20 10:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `created_at`) VALUES
(1, 'MAIFIP', 'MAIFIP', '2025-08-20 07:22:07'),
(2, 'CASHIER', 'CASHIER', '2025-08-20 07:22:07'),
(3, 'NOCHP', 'NOCHP', '2025-08-20 07:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `queue_number` varchar(30) NOT NULL,
  `transaction_type_id` int(11) NOT NULL,
  `client_type` enum('regular','priority') NOT NULL,
  `status` enum('waiting','serving','done','hold','cancelled') DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `served_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `queue_number`, `transaction_type_id`, `client_type`, `status`, `created_at`, `served_at`, `cancelled_at`) VALUES
(1, 'MAI-001', 1, 'regular', 'done', '2025-08-20 07:22:52', '2025-08-20 07:46:23', NULL),
(2, 'CASH-001', 2, 'priority', 'waiting', '2025-08-20 07:28:43', NULL, NULL),
(3, 'NOCHP-001', 3, 'regular', 'done', '2025-08-20 07:51:47', '2025-08-20 09:50:14', NULL),
(4, 'NOCHP-002', 3, 'regular', 'done', '2025-08-20 07:54:45', '2025-08-20 09:57:07', NULL),
(5, 'NOCHP-003', 3, 'priority', 'cancelled', '2025-08-20 08:34:57', NULL, '2025-08-20 09:11:47'),
(6, 'NOCHP-004', 3, 'regular', 'cancelled', '2025-08-20 08:35:29', NULL, '2025-08-20 09:10:50'),
(7, 'NOCHP005', 3, 'priority', 'cancelled', '2025-08-20 09:12:03', NULL, '2025-08-20 09:26:45'),
(8, 'CASHIER002', 2, 'regular', 'cancelled', '2025-08-20 09:39:53', NULL, '2025-08-20 09:46:34'),
(9, 'NOCHP006', 3, 'priority', 'hold', '2025-08-20 09:47:06', NULL, NULL),
(10, 'NOCHP007', 3, 'priority', 'done', '2025-08-20 09:54:06', '2025-08-20 09:58:04', NULL),
(11, 'MAIFIP002', 1, 'regular', 'cancelled', '2025-08-20 10:16:34', NULL, '2025-08-20 10:20:40'),
(12, '003', 1, 'priority', 'done', '2025-08-20 10:20:44', '2025-08-20 10:21:21', NULL),
(13, '004', 1, 'regular', 'waiting', '2025-08-20 10:22:32', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `department_id`, `created_at`) VALUES
(1, 'BCHOSPITAL', '$2y$10$OwB0HJqYcNth0OjYYQL4v.Yz4OHVm5DyjpMNR5fC7wZ/daANFMNsu', 'superadmin', NULL, '2025-08-20 07:33:09'),
(3, 'MAIFIP', '$2y$10$F5M7o.aLD/q7Y/TXl7KBw.b8OzrkH4QCcLQFlPSna30yaw5X7aylC', 'admin', 1, '2025-08-20 07:44:29'),
(4, 'CASHIER', '$2y$10$Iljd5977xq5EwtHK5mvfyueb4pK7Oh9NGlC2lJ1afO7V0YVcv/hEG', 'admin', 2, '2025-08-20 07:52:54'),
(5, 'NOCHP', '$2y$10$dkmLdj0/1Ji75Fgs1hEkmeG3B1VaRwbPoS9mwzlEfGrz.6xl6ZfnG', 'admin', 3, '2025-08-20 07:53:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_type_id` (`transaction_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`transaction_type_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
