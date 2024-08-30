-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2024 at 12:06 PM
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
-- Database: `api_booth`
--

-- --------------------------------------------------------

--
-- Table structure for table `booth`
--

CREATE TABLE `booth` (
  `booth_id` int(11) NOT NULL,
  `booth_name` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `products` varchar(255) NOT NULL,
  `zone_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booth`
--

INSERT INTO `booth` (`booth_id`, `booth_name`, `size`, `products`, `zone_id`) VALUES
(13, 'PB', '100x100', 'บัตรเติมเกมส์', 1),
(14, 'Sab Place', '200*100', 'ปูม้า แต่ปูหมดเหลือแต่ม้า', 16);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `start_at_date` date NOT NULL,
  `end_at_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `start_at_date`, `end_at_date`) VALUES
(4, 'ขายการ์ดจอ', '2024-08-23', '2024-08-31'),
(5, 'ผ้าป่า', '2024-08-09', '2024-08-29'),
(7, 'ขายปูม้า แต่ปูหมด เหลือแต่ม้า', '2024-08-25', '2024-08-30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `password`, `phone`, `create_at`, `role`) VALUES
(6, 'Aum', 'kphs', 'aumt1569@gmail.com', '$2y$10$.Gk1yEh9QcXxH94hMV6eOOAF6RWC9tlBghaHFC4GRusXFv5NWvkN2', '0989520103', '2024-08-02 14:58:47', 'user'),
(7, 'Aum', 'kphs', 'taweechok.k@ku.th', '$2y$10$2GC.qVVPThiAMQAj8M2TSekn9PNctWKM/HkWkMtILPYgjsxxum9xa', '0989520103', '2024-08-04 07:41:22', 'user'),
(23, 'Taweechok', 'Kphs', 'taweechok.kh@ku.th', '$2y$10$eAD8iRyfSHlTG8zTZ6fJ1ub2.mNGvYYSAcrezprbCE7Lso6oY7sNe', '0989520101', '2024-08-04 08:24:12', 'user'),
(26, 'arm', 'KHPS', 'a@gmail.com', '123', '669520103', '2024-08-29 06:36:26', 'user'),
(27, 'arm', 'KHPS', 'a@gmail.com', '$2y$10$skqaFACzsOA/2alFfci0EOww8k7AI2l4oIjPCR6L/n/OuQ0HXfN32', '669520103', '2024-08-29 06:42:49', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `zone`
--

CREATE TABLE `zone` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `event_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zone`
--

INSERT INTO `zone` (`zone_id`, `zone_name`, `amount`, `event_id`) VALUES
(18, 'อาคาร 7', 10, '4'),
(19, 'อาคาร 14', 10, '4');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booth`
--
ALTER TABLE `booth`
  ADD PRIMARY KEY (`booth_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zone`
--
ALTER TABLE `zone`
  ADD PRIMARY KEY (`zone_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booth`
--
ALTER TABLE `booth`
  MODIFY `booth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `zone`
--
ALTER TABLE `zone`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
