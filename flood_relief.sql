-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 11:35 AM
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
-- Database: `flood_relief`
--

-- --------------------------------------------------------

--
-- Table structure for table `relief_requests`
--

CREATE TABLE `relief_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `relief_type` enum('Food','Water','Medicine','Shelter') NOT NULL,
  `district` varchar(100) NOT NULL,
  `divisional_sec` varchar(150) NOT NULL,
  `gn_division` varchar(150) NOT NULL,
  `contact_name` varchar(150) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `family_members` tinyint(3) UNSIGNED NOT NULL,
  `severity` enum('Low','Medium','High') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `relief_requests`
--

INSERT INTO `relief_requests` (`id`, `user_id`, `relief_type`, `district`, `divisional_sec`, `gn_division`, `contact_name`, `contact_number`, `address`, `family_members`, `severity`, `description`, `created_at`, `updated_at`) VALUES
(1, 2, 'Medicine', 'Kurunegala', 'Kuliyapitiya', 'Kuliyapitiya north', 'Nidula Yasanjith', '0776471928', 'Iddamalpitiya, Pahaladiyadora, Kuliyapitiya', 3, 'Medium', 'need help', '2026-03-18 13:20:43', '2026-03-18 13:20:43'),
(2, 3, 'Food', 'Kalutara', 'Bandaragama', 'Bandaragama', 'thamara', '0725049250', 'no.5/12, Kalidasa junction , Bandaragama', 10, 'High', 'Dry rations', '2026-03-18 13:37:28', '2026-03-18 13:37:28'),
(3, 4, 'Food', 'Galle', 'Bentara', 'Singharpagama', 'Thamara Kumari', '0718615511', 'Olu,Maddewela,Bentota', 3, 'Medium', '', '2026-03-18 15:52:39', '2026-03-18 15:52:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `nic`, `phone`, `role`, `created_at`) VALUES
(1, 'System Administrator', 'admin@floodrelief.lk', '$2y$12$.ZLOJ6uu0Zr7TOvZC48GtuL1YjVL2DQ.5JAxIxbA3y92QwGhBMALq', '000000000V', '0112345678', 'admin', '2026-03-18 13:01:08'),
(2, 'Nidula Yasanjith', 'nidulayasanjith11@gmail.com', '$2y$12$q05FeIPsrRTIWVlrxFuaAuBWmxvhn9hRA/OA3vmCVCTU0iMRQD0fi', '200312011150', '0776471928', 'user', '2026-03-18 13:16:50'),
(3, 'Thamara Eranda', 'thamaraailapperuma@gmail.com', '$2y$12$EdLMgaQtD7k/cpQbE5YT7.y5koHA6jy8sHVFm9R9Iy12/11iPmlhK', '200412344321', '0725049250', 'user', '2026-03-18 13:32:36'),
(4, 'Olu Anuradha', 'oluanuradha123@gmail.com', '$2y$12$mqqVKe7m6AGZtWgvWWdiau4HHNWsO9AsOmxYJihfnu1EPtXza8Z0G', '200466801121', '0766672101', 'user', '2026-03-18 15:45:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `relief_requests`
--
ALTER TABLE `relief_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_request_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nic` (`nic`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `relief_requests`
--
ALTER TABLE `relief_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `relief_requests`
--
ALTER TABLE `relief_requests`
  ADD CONSTRAINT `fk_request_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
