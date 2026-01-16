-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 08:26 PM
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
-- Database: `lost_found_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `item_id`, `claimant_id`, `status`, `created_at`) VALUES
(1, 2, 1, 'pending', '2025-10-14 08:23:39'),
(3, 2, 3, 'pending', '2025-12-13 12:04:59'),
(7, 2, 8, 'rejected', '2025-12-13 12:58:22'),
(8, 6, 9, 'rejected', '2025-12-13 13:10:50'),
(9, 6, 3, 'pending', '2025-12-13 16:18:38'),
(10, 2, 4, 'pending', '2025-12-13 16:44:10'),
(11, 6, 4, 'rejected', '2025-12-13 16:44:32'),
(15, 2, 10, 'rejected', '2025-12-13 16:55:21'),
(16, 6, 10, 'approved', '2025-12-13 16:57:24'),
(17, 10, 12, 'rejected', '2025-12-13 17:17:30'),
(18, 10, 11, 'approved', '2025-12-13 17:38:40'),
(19, 9, 4, 'pending', '2025-12-16 14:05:07'),
(20, 11, 3, 'pending', '2025-12-16 14:07:01'),
(21, 9, 1, 'pending', '2025-12-16 14:33:05'),
(22, 11, 4, 'pending', '2025-12-16 14:33:34'),
(23, 11, 1, 'pending', '2025-12-16 14:46:14'),
(24, 15, 4, 'pending', '2025-12-16 15:22:42'),
(25, 15, 3, 'pending', '2025-12-16 18:00:40'),
(26, 15, 11, 'pending', '2025-12-17 16:28:11');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('lost','found') NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `is_sensitive` tinyint(1) DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('active','verification_pending','resolved') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_incident` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `user_id`, `type`, `title`, `description`, `is_sensitive`, `image_path`, `status`, `created_at`, `date_incident`) VALUES
(2, 2, 'found', 'blue purse', 'blue, library', 1, '', 'active', '2025-10-14 08:16:41', NULL),
(5, 9, 'lost', 'Laptop ', 'Black colour ', 0, '', 'active', '2025-12-13 13:09:08', NULL),
(6, 9, 'found', 'Phone', 'Blue', 0, '', 'resolved', '2025-12-13 13:09:33', NULL),
(8, 12, 'lost', 'Lenevo legion laptop ', 'Wadia College, 1:30pm, black', 0, '', 'active', '2025-12-13 17:13:49', NULL),
(9, 4, 'found', 'Lenovo laptop ', 'Black, in wadia clg', 0, '', 'active', '2025-12-13 17:15:59', NULL),
(10, 4, 'found', 'Lenovo laptop ', 'Black, in wadia clg', 0, '', 'resolved', '2025-12-13 17:16:00', NULL),
(11, 4, 'found', 'Lenovo laptop ', 'Black, in wadia clg', 1, 'uploads/1765646177_17656991574098678880339524998052.jpg', 'active', '2025-12-13 17:16:17', NULL),
(13, 4, 'lost', 'Crocodile ', 'Ledger green', 0, '', 'active', '2025-12-16 15:20:23', '2025-12-16'),
(14, 4, 'lost', 'Crocodile ', 'Ledger green ', 1, 'uploads/1765898463_20251211_230753.jpg', 'active', '2025-12-16 15:21:03', '2025-12-16'),
(15, 4, 'found', 'Blue crocodile ', 'Ledger green ', 0, '', 'active', '2025-12-16 15:21:39', '2025-12-16');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attachment_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `claim_id`, `sender_id`, `message_text`, `created_at`, `attachment_path`) VALUES
(1, 1, 1, 'helo', '2025-10-14 08:23:39', NULL),
(4, 3, 3, 'Hahahaah', '2025-12-13 12:04:59', NULL),
(5, 1, 1, 'what', '2025-12-13 12:05:33', NULL),
(6, 3, 1, 'whatttt', '2025-12-13 12:06:01', NULL),
(13, 7, 8, 'my purse is of blue colour which is small in in size like wallet', '2025-12-13 12:58:22', NULL),
(14, 7, 1, 'kontya color cha aahe', '2025-12-13 12:58:41', NULL),
(15, 7, 8, 'is it red in colour ', '2025-12-13 12:59:39', NULL),
(16, 7, 8, 'is it red in colour ', '2025-12-13 12:59:39', NULL),
(17, 7, 1, 'System: Claim REJECTED.', '2025-12-13 12:59:50', NULL),
(18, 8, 9, 'Daimond seaded phone', '2025-12-13 13:10:50', NULL),
(19, 8, 1, 'pakka?', '2025-12-13 13:11:05', NULL),
(20, 8, 9, 'Any dought?', '2025-12-13 13:11:37', NULL),
(21, 8, 1, 'System: Claim REJECTED.', '2025-12-13 13:11:47', NULL),
(22, 9, 3, 'Blue', '2025-12-13 16:18:38', NULL),
(23, 10, 4, 'Red', '2025-12-13 16:44:10', NULL),
(24, 11, 4, 'Ytrrrr', '2025-12-13 16:44:32', NULL),
(25, 11, 1, 'no its not', '2025-12-13 16:45:20', NULL),
(26, 11, 4, 'It issss', '2025-12-13 16:45:32', NULL),
(27, 11, 1, 'System: Claim REJECTED.', '2025-12-13 16:53:13', NULL),
(28, 15, 10, 'It was having a photo in it and it has an 1500 rs', '2025-12-13 16:55:21', NULL),
(29, 15, 1, 'is it', '2025-12-13 16:55:40', NULL),
(30, 15, 10, 'Yes', '2025-12-13 16:56:07', NULL),
(31, 15, 1, 'no i reject', '2025-12-13 16:56:19', NULL),
(32, 15, 1, 'System: Claim REJECTED.', '2025-12-13 16:56:24', NULL),
(33, 16, 10, 'It was m 14 model', '2025-12-13 16:57:24', NULL),
(34, 16, 1, 'System: Claim APPROVED.', '2025-12-13 16:57:34', NULL),
(35, 17, 12, 'Wadias clg , 1:30pm , black', '2025-12-13 17:17:30', NULL),
(36, 17, 1, 'khara kaa', '2025-12-13 17:17:54', NULL),
(37, 17, 1, 'nahi tujha nahi e ', '2025-12-13 17:18:26', NULL),
(38, 17, 12, 'Majhach aahe de ki🤣🤣', '2025-12-13 17:20:29', NULL),
(39, 17, 1, 'mech khota bollelo', '2025-12-13 17:20:43', NULL),
(40, 17, 1, 'reject aahe tu ', '2025-12-13 17:20:59', NULL),
(41, 17, 1, 'System: Claim REJECTED.', '2025-12-13 17:21:02', NULL),
(42, 18, 11, 'Black color', '2025-12-13 17:38:40', NULL),
(43, 18, 1, 'hmm', '2025-12-13 17:39:08', NULL),
(44, 18, 1, 'suspisius', '2025-12-13 17:39:18', NULL),
(45, 18, 1, 'youre not even in my clg', '2025-12-13 17:39:28', NULL),
(46, 18, 11, 'Oh', '2025-12-13 17:41:28', NULL),
(47, 18, 1, 'everthing is yours', '2025-12-13 17:41:49', NULL),
(48, 18, 1, 'System: Claim APPROVED.', '2025-12-13 17:41:52', NULL),
(49, 10, 4, 'Hello', '2025-12-13 18:18:00', NULL),
(50, 10, 1, 'System: Admin changed item visibility to PUBLIC (Visible).', '2025-12-15 14:55:12', NULL),
(51, 10, 1, 'System: Admin changed item visibility to SENSITIVE (Hidden).', '2025-12-15 14:55:19', NULL),
(52, 19, 4, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 14:05:07', NULL),
(53, 20, 3, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 14:07:01', NULL),
(54, 21, 1, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 14:33:06', NULL),
(55, 22, 4, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 14:33:34', NULL),
(56, 23, 1, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 14:46:14', NULL),
(57, 22, 4, 'Hello', '2025-12-16 14:53:06', NULL),
(58, 24, 4, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 15:22:43', NULL),
(59, 24, 1, 'System: Admin changed item visibility.', '2025-12-16 15:23:21', NULL),
(60, 25, 3, 'System: New claim started. Please provide proof of ownership.', '2025-12-16 18:00:41', NULL),
(61, 23, 1, 'helo wahts my status', '2025-12-16 18:25:48', NULL),
(62, 23, 1, 'System: Admin changed item visibility.', '2025-12-16 18:32:02', NULL),
(63, 26, 11, 'System: New claim started. Please provide proof of ownership.', '2025-12-17 16:28:11', NULL),
(64, 26, 11, 'Hellooo give me back my crocodile pls', '2025-12-17 16:28:49', NULL),
(65, 26, 1, 'send me a pic first mam', '2025-12-17 16:29:02', NULL),
(66, 26, 1, 'you got an upload image option right', '2025-12-17 16:29:19', NULL),
(67, 26, 11, '', '2025-12-17 16:30:08', 'uploads/chat/1765989008_IMG_0130.jpeg'),
(68, 26, 1, 'woww', '2025-12-17 16:30:17', 'uploads/chat/1765989017_1760430406_20250220_212337[1].jpg'),
(69, 26, 11, 'What have you done to my crocodile???', '2025-12-17 16:30:36', NULL),
(70, 26, 1, 'someone else claimed it :', '2025-12-17 16:31:43', NULL),
(71, 26, 11, 'Oh no my crocodile is stolen again?', '2025-12-17 16:32:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `name_updated` tinyint(1) DEFAULT 0,
  `mobile` varchar(15) NOT NULL DEFAULT '',
  `department` varchar(100) DEFAULT '',
  `year_study` varchar(50) DEFAULT '',
  `address` text DEFAULT NULL,
  `is_banned` tinyint(1) DEFAULT 0,
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `student_id`, `role`, `created_at`, `reset_token`, `token_expiry`, `name_updated`, `mobile`, `department`, `year_study`, `address`, `is_banned`, `gender`) VALUES
(1, 'admin', 'keshhaavvvv@gmain.com', '$2y$10$RnARuokPBNe2tRVYrsYy9uYtnFHkdK19bVWtfYYjiUApJJZWxFQ8O', 'admin01', 'admin', '2025-10-14 07:56:26', NULL, NULL, 0, '7972560349', '', '', '', 0, 'Male'),
(2, 'student', 'abc@g', '$2y$10$oEXKFIm7k0LkwIhhYiTxI.TRw/3.ItC.2fU1rjLioHz2BcpI/b.Tu', '176', 'student', '2025-10-14 08:15:16', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(3, 'student A', 'studentA@gmail.com', '$2y$10$faxCVpuY8yrFboHxZH/UmO/u5JPN6GNwFJYafiUS3HshE5kdosCYq', '1', 'student', '2025-10-14 08:25:31', '157f8dad74217561fc905b3f654a219d9eddfc1951dcb0ca6ca46ff5fcb5a0ef', '2025-12-18 21:19:59', 0, '', '', '', NULL, 0, NULL),
(4, 'student B', 'studentB@gmail.com', '$2y$10$kyycq4IK9e0ZfdekVwivMuhaF7sxF3XLxMR2TyhriioLQixCsesim', '2', 'student', '2025-10-14 08:27:20', 'b1c5d9b906519f96f5d84c55f3f35d1b8523541e47ea77a899ebac341fc8adee', '2025-12-17 18:51:34', 0, '', '', '', NULL, 0, NULL),
(5, 'Vasant waghule', 'vasantwaghule64@gmail.com', '$2y$10$6g2AnxAP9DYBsnIZtcEsyuyaAZ3fYkhYm/pcModk566wtusXobEhG', '176', 'student', '2025-12-13 11:56:02', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(6, 'Test78', 'Test78@gmail.com', '$2y$10$DCGgaflTf8UTCIZwVc..q.mVcasXMPHSlWok6oU//iZzT4Kdaq4dq', '78', 'student', '2025-12-13 11:58:56', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(7, 'test90', 'test90@gmail.com', '$2y$10$HxyfDoj7hQmFkII08Rt4GucGIEcj6uzuQI6Jjr80MJuMrbP.m5vSO', '90', 'student', '2025-12-13 12:43:49', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(8, 'Vedant Sanjay Tashildar', 'tashildarvedant21@gmail.com', '$2y$10$GDUaWVs5.aAyPfFcPTnkO.YEj2w3HW6V7wN835v7xgV2j.7ft7oUy', '170', 'student', '2025-12-13 12:50:50', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(9, 'Rohan Sonawane', 'rohansonawane1002@gmail.com', '$2y$10$M5GbEAl2j08Lh/E7HZf83eIFvnm6aZY89rNA3jOIHmig9ZnBjHlRi', '168', 'student', '2025-12-13 13:08:42', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(10, 'Harish kumbhar', 'harish.kumbhar3447@gmail.com', '$2y$10$YsOh90XMh.k8a5BnewfrPOTLABDj.V2lWvAVM5VPimqMCeF4eGjxK', '36', 'student', '2025-12-13 16:49:40', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(11, 'Aishi Mandal', 'aishimandal78@gmail.com', '$2y$10$dmw.4XhoFjjfIJXFnVL3aet7F0tsJ.fbn55sM/Q75D0AUqhq4guW2', '12345', 'student', '2025-12-13 17:09:14', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(12, 'Vedant Taru', 'vedanttaru26@gmail.com', '$2y$10$RGXhUgv2jadNiydPEBJxtOFyOtcYGRceXiEZ8p2mWzTUsNxUE.qpK', '169', 'student', '2025-12-13 17:11:45', NULL, NULL, 0, '', '', '', NULL, 0, NULL),
(13, 'testing', 'testing@gmail.com', '$2y$10$M4mV6Q5wAqv50v5UMcoMWuQ0iNYKYn5yd2E4dAzDJQYR3wgeDRb6e', '176', 'student', '2025-12-16 04:43:37', NULL, NULL, 0, '7972560349', '', '', NULL, 0, NULL),
(14, 'keshav vasant waghule', 'itzkeshav006@gmail.com', '$2y$10$/DoqhgTO.8Hgtyrq1NALTuWKoIpQ5OSY0AQB7AVLkS7viTJeOSZji', '176', 'student', '2025-12-18 19:08:58', '1748db3f02b96e34464f7f1e63474ba045fcc931a3cb6735015bf2ff8249781e', '2025-12-18 21:23:26', 0, '7972560340', '', '', NULL, 0, 'Male');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `claimant_id` (`claimant_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
