-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2024 at 06:04 PM
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
  `booking_status` enum('ชำระเงินแล้ว','ยกเลิกการจอง','อนุมัติแล้ว','จอง') NOT NULL DEFAULT 'จอง',
  `products_data` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `booking_date`, `booking_pay`, `booth_id`, `price`, `bill_img`, `booking_status`, `products_data`, `user_id`, `event_id`) VALUES
(44, '2024-09-14 15:49:58', '2024-09-14 15:53:17', 49, 2500, 'bill_01.png', 'อนุมัติแล้ว', 'ขายหมูปิ้ง', 40, 16),
(45, '2024-09-14 15:55:08', '2024-09-14 15:55:32', 48, 5000, 'bill_02.png', 'ชำระเงินแล้ว', 'ขายไอศกรีม', 40, 16);

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
  `zone_id` int(11) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booth`
--

INSERT INTO `booth` (`booth_id`, `booth_name`, `size`, `status`, `price`, `zone_id`, `img`) VALUES
(46, 'ขายขนมขาไก่ ขายไก่ทอด ขายทุกอย่างเพราะเหมาหมด', '2เมตร* 1เมตร', 'ว่าง', 1500000, 27, 'booth_01.png'),
(47, 'ไก่ทอดหาดเล็ก', '2เมตร* 2เมตร', 'ว่าง', 5000, 28, 'booth_02.png'),
(48, 'Dairy King', '1เมตร* 1เมตร', 'อยู่ระหว่างการตรวจสอบ', 5000, 28, 'booth_03.png'),
(49, 'B1', '1เมตร* 2เมตร', 'จองแล้ว', 2500, 29, 'booth_03.png');

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
(16, 'เกษตรแฟร์ 2024', '2024-09-20', '2024-10-03'),
(17, 'Robot Adventure 2024', '2024-09-14', '2024-09-17');

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
  `update_at` timestamp NULL DEFAULT NULL,
  `role` enum('customer','admin','','') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `pname`, `fname`, `lname`, `email`, `password`, `phone`, `create_at`, `update_at`, `role`) VALUES
(38, 'นาย', 'แอดมิน', 'แอดมิน', 'admin@gmail.com', '$2y$10$E9e9OnNInyykK1PKPr84peY89xvDY53pphehlregSoV0239dPRXzm', '0989520103', '2024-09-14 14:50:18', NULL, 'admin'),
(39, 'นาย', 'เสกสรรค์', 'ปันสุข', 'sek@gmail.com', '$2y$10$ATNfNXFBa1GtXsY4aviGdOw2PrpMXvSl2.lU08OvMFiigUWKPrOnm', '099999999', '2024-09-14 14:55:02', NULL, 'customer'),
(40, 'นาง', 'สร้างสรรค์', 'เจริญพันธ์', 'jlern@gmail.com', '$2y$10$EqL4JClqr6/myO8jOU4Ibec0nXiTUv4QD8YYqNtnv8eVneb4c03Y6', '0988888888', '2024-09-14 14:56:23', NULL, 'customer'),
(41, 'นาง', 'เจเจ', 'โจโจ', '่jojo@gmail.com', '$2y$10$0P/AG7UFfEJkKZoBViJ6nePu/p0WRqiWfqTdaZM/KiA.jIhPPAX0m', '0977777777', '2024-09-14 14:57:25', NULL, 'customer'),
(42, 'นาย', 'แอด', 'นาแก', '่add@gmail.com', '$2y$10$4NSCh2Y.yEQZ0VHpHGTlZOSSRG7Ies1O/VsNvp7M5xiQQ8LdDk70W', '0966666666', '2024-09-14 14:58:40', NULL, 'customer');

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
(26, 'อาคาร 14', 10, 17),
(27, 'ถนนบริเวณ ลานหน้าพระพิรุณ ถึง สะดือมอ', 50, 16),
(28, 'โซน A1 (ด้านซ้ายจากทางเข้ามอ หรือ ริมอ่างสกล)', 3, 16),
(29, 'โซน B1 (ด้านขวาจากทางเข้ามอ ถนนฝั่งหน้าพระพิรุณ)', 1, 16);

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
  ADD PRIMARY KEY (`booth_id`),
  ADD KEY `booth_zone_FK` (`zone_id`);

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
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `booth`
--
ALTER TABLE `booth`
  MODIFY `booth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `zone`
--
ALTER TABLE `zone`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
-- Constraints for table `booth`
--
ALTER TABLE `booth`
  ADD CONSTRAINT `booth_zone_FK` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`zone_id`);

--
-- Constraints for table `zone`
--
ALTER TABLE `zone`
  ADD CONSTRAINT `zone_events_FK` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
