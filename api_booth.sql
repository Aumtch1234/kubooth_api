-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2024 at 08:39 PM
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
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `booking_pay` timestamp NULL DEFAULT NULL,
  `booth_id` int(11) NOT NULL,
  `price` double NOT NULL,
  `bill_img` varchar(255) DEFAULT NULL,
  `booking_status` enum('อยู่ระหว่างการตรวจสอบ','ชำระเงินแล้ว','ยกเลิกการจอง') NOT NULL DEFAULT 'อยู่ระหว่างการตรวจสอบ',
  `products_data` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `booking_date`, `booking_pay`, `booth_id`, `price`, `bill_img`, `booking_status`, `products_data`, `user_id`, `event_id`) VALUES
(31, '2024-09-13 14:59:14', '2024-09-13 15:33:31', 42, 1500.5, 'x1.png', 'ชำระเงินแล้ว', 'ขายขนมขาไก่', 35, 9),
(32, '2024-09-13 14:59:45', NULL, 41, 1500.5, NULL, 'อยู่ระหว่างการตรวจสอบ', 'ขายขนมขาไก่', 34, 9),
(33, '2024-09-13 15:14:24', NULL, 41, 1500.5, NULL, 'อยู่ระหว่างการตรวจสอบ', 'ขายขนมขาไก่', 34, 9),
(34, '2024-09-13 15:41:36', NULL, 42, 1500.5, NULL, 'ยกเลิกการจอง', 'ขายขนมขาไก่', 34, 9),
(37, '2024-09-13 16:06:19', NULL, 42, 1500.5, NULL, 'ยกเลิกการจอง', 'ขายขนมขาไก่', 34, 9),
(38, '2024-09-13 16:19:31', NULL, 42, 1500.5, NULL, 'อยู่ระหว่างการตรวจสอบ', 'ขายขนมขาไก่', 34, 9),
(39, '2024-09-13 16:20:03', NULL, 41, 1500.5, NULL, 'ยกเลิกการจอง', 'ขายขนมขาไก่', 34, 9),
(40, '2024-09-13 16:22:17', NULL, 42, 1500.5, NULL, 'ยกเลิกการจอง', 'ขายขนมขาไก่', 34, 9),
(42, '2024-09-13 17:42:48', '2024-09-13 17:45:53', 42, 1500.5, 'x1.png', 'ชำระเงินแล้ว', 'ขายขนมขาไก่', 34, 10);

-- --------------------------------------------------------

--
-- Table structure for table `booth`
--

CREATE TABLE `booth` (
  `booth_id` int(11) NOT NULL,
  `booth_name` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `status` enum('ว่าง','อยู่ระหว่างการตรวจสอบ','จองแล้ว') NOT NULL DEFAULT 'ว่าง',
  `price` double NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booth`
--

INSERT INTO `booth` (`booth_id`, `booth_name`, `size`, `status`, `price`, `img`) VALUES
(41, 'Happy Place', '200*100', 'อยู่ระหว่างการตรวจสอบ', 1500.5, 'booth1.png'),
(42, 'Happy Places', '200*100', 'ว่าง', 1500.5, 'booth1.png');

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
(9, 'ขายยา', '2024-08-25', '2024-08-30'),
(10, 'ขายยา', '2024-09-19', '2024-08-30'),
(11, '', '0000-00-00', '0000-00-00'),
(12, 'ขายยา', '2024-09-14', '2024-09-20'),
(13, 'ขายยาพารา', '2024-09-14', '2024-09-20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL,
  `pname` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(255) NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `pname`, `fname`, `lname`, `email`, `password`, `phone`, `create_at`, `role`) VALUES
(34, 'นาย', 'ทวีโชค', 'คำภูษา', 'a1@gmail.com', '$2y$10$OoJVyyuL4FCsbV1cr0ElvOpk4NusbYO5ax0sciNpo5HWbHqrqleiy', '0989520103', '2024-09-08 16:41:20', 'customer'),
(35, '', 'สมศรี', 'บุญเรือง', 's1@gmail.com', '$2y$10$oPHXYO3S1YQbLj9LPhWS1u7NSqhm9SEcvPCwXNvxSVAod3jNwGgPK', '0989520103', '2024-09-10 13:26:10', 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `zone`
--

CREATE TABLE `zone` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(255) NOT NULL,
  `amount_booth` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zone`
--

INSERT INTO `zone` (`zone_id`, `zone_name`, `amount_booth`, `event_id`) VALUES
(22, 'อาคาร 7', 10, 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `booking_booth_FK` (`booth_id`),
  ADD KEY `booking_users_FK` (`user_id`),
  ADD KEY `booking_events_FK` (`event_id`);

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
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `zone`
--
ALTER TABLE `zone`
  ADD PRIMARY KEY (`zone_id`),
  ADD KEY `zone_events_FK` (`event_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `booth`
--
ALTER TABLE `booth`
  MODIFY `booth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `zone`
--
ALTER TABLE `zone`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_booth_FK` FOREIGN KEY (`booth_id`) REFERENCES `booth` (`booth_id`),
  ADD CONSTRAINT `booking_events_FK` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `booking_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `zone`
--
ALTER TABLE `zone`
  ADD CONSTRAINT `zone_events_FK` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
