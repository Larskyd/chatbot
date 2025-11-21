-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 12, 2025 at 04:15 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chatbot`
--

-- --------------------------------------------------------

--
-- Table structure for table `query_log`
--

CREATE TABLE `query_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query_text` text NOT NULL,
  `response_text` mediumtext DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `query_log`
--

INSERT INTO `query_log` (`id`, `user_id`, `query_text`, `response_text`, `metadata`, `created_at`) VALUES
(1, NULL, 'test_insert', 'response', '{\"note\":\"manual test\"}', '2025-10-22 14:02:33'),
(2, NULL, 'test_insert', 'response', '{\"note\":\"manual test\"}', '2025-10-22 14:03:13'),
(3, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-10-22 14:28:55'),
(4, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-10-22 14:28:56'),
(5, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-10-22 14:28:59'),
(6, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-10-23 12:46:35'),
(7, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-10-23 12:46:40'),
(8, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-10-23 12:47:31'),
(9, NULL, 'showRecipesByArea: russian', '{\"count\":7}', NULL, '2025-10-23 12:47:43'),
(10, NULL, 'showRecipesByArea: norwegian', '{\"count\":0}', NULL, '2025-10-23 12:47:51'),
(11, NULL, 'showRecipesByArea: lars', '{\"count\":0}', NULL, '2025-10-23 12:47:58'),
(12, NULL, 'showRecipesByArea: norwegian', '{\"count\":0}', NULL, '2025-10-23 13:34:46'),
(13, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-11-04 12:15:05'),
(14, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-11-04 12:15:11'),
(15, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-11-09 16:07:12'),
(16, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-11-09 16:07:12'),
(17, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-11-09 16:08:52'),
(18, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-11-09 16:09:49'),
(19, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:10:12'),
(20, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:12:22'),
(21, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:14:06'),
(22, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:19:47'),
(23, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:23:56'),
(24, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:25:07'),
(25, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:26:14'),
(26, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:26:49'),
(27, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:27:22'),
(28, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:27:25'),
(29, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:28:51'),
(30, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:29:09'),
(31, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:29:11'),
(32, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:30:26'),
(33, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:30:50'),
(34, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:31:02'),
(35, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:31:16'),
(36, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:31:38'),
(37, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:32:20'),
(38, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:33:36'),
(39, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:34:00'),
(40, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:34:48'),
(41, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:35:11'),
(42, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:39:52'),
(43, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:40:05'),
(44, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:40:24'),
(45, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:40:36'),
(46, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:40:38'),
(47, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:41:23'),
(48, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:49:10'),
(49, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:50:37'),
(50, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:51:33'),
(51, NULL, 'showRecipesByArea: s', '{\"count\":0}', NULL, '2025-11-09 16:59:33'),
(52, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-11-09 17:29:30'),
(53, NULL, 'showCategories', '{\"count\":14}', NULL, '2025-11-09 17:29:47'),
(54, NULL, 'showRecipesByArea: italian', '{\"count\":21}', NULL, '2025-11-09 17:29:55'),
(55, NULL, 'russian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":7}', '2025-11-09 21:45:36'),
(56, NULL, 'russian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":7}', '2025-11-09 21:53:22'),
(57, NULL, 'italian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":21}', '2025-11-09 22:00:12'),
(58, NULL, 'norwegian', '[\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":6}', '2025-11-09 22:40:15'),
(59, NULL, 'German', '[]', '{\"count\":0}', '2025-11-12 11:03:47'),
(60, 8, 'russian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":7}', '2025-11-12 11:34:12'),
(61, 8, 'russian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":7}', '2025-11-12 11:39:37'),
(62, 8, 'italian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":21}', '2025-11-12 11:40:02'),
(63, 8, 'norwegian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":13}', '2025-11-12 11:44:48'),
(64, 9, 'kristiansand', '[]', '{\"count\":0}', '2025-11-12 11:45:32'),
(65, 2, 'italian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":21}', '2025-11-12 11:52:49'),
(66, 10, 'Hva er noen italian matretter', '[]', '{\"count\":0}', '2025-11-12 11:55:02'),
(67, 2, 'italian', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '{\"count\":21}', '2025-11-12 12:49:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `failed_attempts`, `locked_until`) VALUES
(1, 'ivar@kartverket.no', '$2y$10$BNOoaKNRNj02RZqr/NjRQ.1QWGNEVROUjhBIGLKcGPweBEkXok7pG', '2025-11-09 22:20:43', 0, NULL),
(2, 'testbruker@mail.no', '$2y$10$Z02GCGgwowdXlA.tiBHi8OzsujiIMDla5Mcuipc0Y6mdpQjgX.mEC', '2025-11-09 22:32:22', 3, '2025-11-12 17:07:21'),
(3, 'ole@gmail.com', '$2y$10$5sLsUPJDF7TVerhL8dr18.IzO4rfNMogRfEarroX/Yry2OqGLIac2', '2025-11-09 23:00:34', 0, NULL),
(4, 'nybruker@uia.no', '$2y$10$mnFMAJ.13NB5i4xGpt880Ofeag.GuXFqm75AdaO5WkBUBWdpiK3VO', '2025-11-09 23:19:34', 0, NULL),
(5, 'qweqwe@gmail.bn', '$2y$10$i60Pwi6mKynuljfmp5gbLuLh6VGpubKDSz/kbBdIu07XvjjkJNjO6', '2025-11-09 23:25:37', 0, NULL),
(6, 'simenenn@mail.no', '$2y$10$mDsPu2DJ46AeoK6lDXEGCOaEXdyeTIXtpNE2GQvVmzFmiEb.iZbTC', '2025-11-12 12:01:58', 0, NULL),
(7, 'husk@mail.no', '$2y$10$EIy5HXIY1DMKKsR5RPCgJeKER.56JMbNXA9gpcRgV3TSK8LjV.w9q', '2025-11-12 12:02:31', 0, NULL),
(8, 'nymail@mail.no', '$2y$10$V2/IxlHoyokRFaa3akCMDeuN4tHln1yaVvXYUiOS.TK2P77xvfnfO', '2025-11-12 12:25:36', 0, NULL),
(9, 'jens@kartverket.no', '$2y$10$1FGWueUQdjXOHnlpw7BrNuGw3XQZvNee0Aw8.9JKnAfxJ.6w0RR7y', '2025-11-12 12:45:13', 0, NULL),
(10, 'zimma@admin.no', '$2y$10$xTNSI2KUEJ1y2kiM7HsINubleDii1Ki7rgGLsfPewcREA2USNrfK2', '2025-11-12 12:47:56', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `query_log`
--
ALTER TABLE `query_log`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `query_log`
--
ALTER TABLE `query_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
