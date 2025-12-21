-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2025 at 07:14 AM
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
-- Database: `mga_encoding_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `information`
--

CREATE TABLE `information` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `tin_number` varchar(12) NOT NULL,
  `month` varchar(20) NOT NULL,
  `year` year(4) NOT NULL,
  `authorized_employee` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `information`
--

INSERT INTO `information` (`id`, `user_id`, `company_name`, `address`, `tin_number`, `month`, `year`, `authorized_employee`, `contact_number`, `email`, `submitted_at`) VALUES
(2, 1, 'Mr Admin', 'Office', '123456789000', 'December', '2025', 'Juan Dela Cruz', '09626441490', 'aivanpaguntalan25@gmail.com', '2025-12-20 18:43:15'),
(3, 2, 'DEF Realty OPC', 'Manila', '668456345000', 'December', '2025', 'James Dela Cruz', '09626441490', 'aivanpaguntalan25@gmail.com', '2025-12-20 23:21:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `information`
--
ALTER TABLE `information`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `information`
--
ALTER TABLE `information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `information`
--
ALTER TABLE `information`
  ADD CONSTRAINT `information_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
