-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2026 at 07:15 AM
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
-- Database: `smart_sakay_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `employee_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `contact_number` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Super Admin','Staff') DEFAULT 'Super Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `complaint_id` int(11) NOT NULL,
  `trip_id` varchar(10) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `complaint_text` text NOT NULL,
  `status` enum('Pending','Resolved','Dismissed') DEFAULT 'Pending',
  `handled_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `license_number` varchar(30) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `status` enum('Active','Inactive','On Leave') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `full_name`, `license_number`, `contact_number`, `status`, `created_at`) VALUES
(1, 'Juan Dela Cruz', 'LIC-001', '09171234567', 'Active', '2026-04-26 10:04:26'),
(2, 'Pedro Santos', 'LIC-002', '09181234567', 'Active', '2026-04-26 10:04:26'),
(3, 'Quezon Ramos', 'LIC-003', '09141234567', 'Active', '2026-04-26 10:04:26'),
(4, 'Ramon Magsaysay', 'LIC-004', '09181734567', 'Active', '2026-04-26 10:04:26'),
(5, 'Santo Tomas', 'LIC-005', '09171201167', 'Active', '2026-04-26 10:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `passenger_log`
--

CREATE TABLE `passenger_log` (
  `log_id` int(11) NOT NULL,
  `trip_id` varchar(10) NOT NULL,
  `stop_id` int(11) NOT NULL,
  `passenger_type` enum('Regular','Student','Senior') NOT NULL,
  `action` enum('Board','Drop') NOT NULL,
  `quantity` int(11) NOT NULL,
  `payment_status` enum('Paid','Unpaid') DEFAULT 'Paid',
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passenger_log`
--

INSERT INTO `passenger_log` (`log_id`, `trip_id`, `stop_id`, `passenger_type`, `action`, `quantity`, `payment_status`, `logged_at`) VALUES
(48, 'T-001', 58, 'Regular', 'Board', 5, 'Paid', '2026-04-28 00:00:00'),
(49, 'T-001', 58, 'Student', 'Board', 3, 'Paid', '2026-04-28 00:02:00'),
(50, 'T-001', 59, 'Regular', 'Drop', 2, 'Paid', '2026-04-28 00:15:00'),
(51, 'T-001', 59, 'Student', 'Drop', 1, 'Paid', '2026-04-28 00:17:00'),
(52, 'T-002', 60, 'Regular', 'Board', 4, 'Paid', '2026-04-28 01:10:00'),
(53, 'T-002', 60, 'Senior', 'Board', 2, 'Paid', '2026-04-28 01:12:00'),
(54, 'T-003', 62, 'Regular', 'Board', 6, 'Paid', '2026-04-28 02:00:00'),
(55, 'T-003', 63, 'Student', 'Board', 2, 'Paid', '2026-04-28 02:05:00'),
(56, 'T-003', 63, 'Regular', 'Drop', 3, 'Paid', '2026-04-28 02:20:00'),
(57, 'T-004', 64, 'Regular', 'Board', 3, 'Paid', '2026-04-28 03:00:00'),
(58, 'T-004', 65, 'Student', 'Board', 2, 'Paid', '2026-04-28 03:03:00'),
(59, 'T-005', 66, 'Regular', 'Board', 4, 'Paid', '2026-04-28 05:00:00'),
(60, 'T-005', 67, 'Senior', 'Board', 1, 'Paid', '2026-04-28 05:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `route`
--

CREATE TABLE `route` (
  `route_id` int(11) NOT NULL,
  `route_name` varchar(150) NOT NULL,
  `distance_km` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route`
--

INSERT INTO `route` (`route_id`, `route_name`, `distance_km`, `created_at`) VALUES
(1, 'Valencia — Malaybalay', 30.90, '2026-04-26 10:04:26'),
(2, 'Aglayan — Malaybalay', 21.00, '2026-04-26 10:04:26'),
(3, 'Malaybalay — Lantapan', 15.00, '2026-04-26 10:04:26'),
(4, 'Malaybalay — San Jose', 5.20, '2026-04-26 10:04:26'),
(5, 'Malaybalay — Impasug-ong', 45.80, '2026-04-26 10:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `route_fare`
--

CREATE TABLE `route_fare` (
  `fare_id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `from_stop_id` int(11) DEFAULT NULL,
  `to_stop_id` int(11) DEFAULT NULL,
  `student_fare` decimal(10,2) DEFAULT NULL,
  `regular_fare` decimal(10,2) DEFAULT NULL,
  `senior_fare` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_fare`
--

INSERT INTO `route_fare` (`fare_id`, `route_id`, `from_stop_id`, `to_stop_id`, `student_fare`, `regular_fare`, `senior_fare`) VALUES
(1, 1, 58, 59, 10.00, 13.00, 8.00),
(2, 1, 58, 60, 15.00, 20.00, 12.00),
(3, 1, 58, 61, 20.00, 26.00, 16.00),
(4, 1, 58, 62, 25.00, 33.00, 20.00),
(5, 1, 58, 63, 30.00, 39.00, 24.00),
(6, 1, 58, 64, 35.00, 46.00, 28.00),
(7, 1, 58, 65, 40.00, 52.00, 32.00),
(8, 1, 59, 60, 10.00, 13.00, 8.00),
(9, 1, 59, 61, 15.00, 20.00, 12.00),
(10, 1, 59, 62, 20.00, 26.00, 16.00),
(11, 1, 59, 63, 25.00, 33.00, 20.00),
(12, 1, 59, 64, 30.00, 39.00, 24.00),
(13, 1, 59, 65, 35.00, 46.00, 28.00),
(14, 1, 60, 61, 10.00, 13.00, 8.00),
(15, 1, 60, 62, 15.00, 20.00, 12.00),
(16, 1, 60, 63, 20.00, 26.00, 16.00),
(17, 1, 60, 64, 25.00, 33.00, 20.00),
(18, 1, 60, 65, 30.00, 39.00, 24.00),
(19, 1, 61, 62, 10.00, 13.00, 8.00),
(20, 1, 61, 63, 15.00, 20.00, 12.00),
(21, 1, 61, 64, 20.00, 26.00, 16.00),
(22, 1, 61, 65, 25.00, 33.00, 20.00),
(23, 1, 62, 63, 10.00, 13.00, 8.00),
(24, 1, 62, 64, 15.00, 20.00, 12.00),
(25, 1, 62, 65, 20.00, 26.00, 16.00),
(26, 1, 63, 64, 10.00, 13.00, 8.00),
(27, 1, 63, 65, 15.00, 20.00, 12.00),
(28, 1, 64, 65, 10.00, 13.00, 8.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stop`
--

CREATE TABLE `stop` (
  `stop_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_name` varchar(100) NOT NULL,
  `stop_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stop`
--

INSERT INTO `stop` (`stop_id`, `route_id`, `stop_name`, `stop_order`) VALUES
(58, 1, 'Valencia (Pob.)', 1),
(59, 1, 'Bagontaas', 2),
(60, 1, 'Cabangahan', 3),
(61, 1, 'Bangcud', 4),
(62, 1, 'Aglayan', 5),
(63, 1, 'San Jose', 6),
(64, 1, 'Casisang', 7),
(65, 1, 'Malaybalay', 8),
(66, 2, 'Aglayan', 1),
(67, 2, 'San Jose', 2),
(68, 2, 'Casisang', 3),
(69, 2, 'Malaybalay', 4),
(70, 3, 'Malaybalay', 1),
(71, 3, 'Casisang', 2),
(72, 3, 'San Jose', 3),
(73, 3, 'Aglayan', 4),
(74, 3, 'Lantapan', 5),
(75, 4, 'Malaybalayr', 1),
(76, 4, 'Casisang', 2),
(77, 4, 'San Jose', 3),
(78, 5, 'Malaybalay', 1),
(79, 5, 'Kalasungay', 2),
(80, 5, 'Pat-pat', 3),
(81, 5, 'Dalwangan', 4),
(82, 5, 'Impalutao', 5),
(83, 5, 'Impasug-ong', 6);

-- --------------------------------------------------------

--
-- Table structure for table `trip`
--

CREATE TABLE `trip` (
  `trip_id` varchar(10) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `status` enum('Active','Completed') DEFAULT 'Active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_fare` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip`
--

INSERT INTO `trip` (`trip_id`, `driver_id`, `vehicle_id`, `route_id`, `status`, `started_at`, `completed_at`, `total_fare`, `created_at`) VALUES
('T-001', 1, 1, 1, 'Completed', '2026-04-26 10:04:26', NULL, 120.00, '2026-04-26 10:04:26'),
('T-002', 2, 2, 2, 'Active', '2026-04-26 10:04:26', NULL, 80.00, '2026-04-26 10:04:26'),
('T-003', 3, 3, 3, 'Completed', '2026-04-26 10:04:26', NULL, 150.00, '2026-04-26 10:04:26'),
('T-004', 4, 4, 4, 'Active', '2026-04-26 10:04:26', NULL, 95.00, '2026-04-26 10:04:26'),
('T-005', 5, 5, 5, 'Completed', '2026-04-26 10:04:26', NULL, 110.00, '2026-04-26 10:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `vehicle_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vehicle_type` enum('Bus','Multicab','Van') NOT NULL,
  `capacity` int(11) NOT NULL,
  `color_markings` varchar(100) DEFAULT NULL,
  `status` enum('Operational','Under Maintenance','Decommissioned') DEFAULT 'Operational',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`vehicle_id`, `plate_number`, `vehicle_type`, `capacity`, `color_markings`, `status`, `created_at`) VALUES
(1, 'ABC123', 'Multicab', 12, 'Blue/White', 'Operational', '2026-04-26 10:04:26'),
(2, 'XYZ789', 'Bus', 20, 'Red/Yellow', 'Operational', '2026-04-26 10:04:26'),
(3, 'LOK223', 'Van', 12, 'White', 'Operational', '2026-04-26 10:04:26'),
(4, 'HAZ900', 'Bus', 20, 'Red/White', 'Operational', '2026-04-26 10:04:26'),
(5, 'XEN554', 'Multicab', 12, 'Green/White', 'Operational', '2026-04-26 10:04:26'),
(6, 'SZA967', 'Multicab', 18, 'Red/Yellow', 'Under Maintenance', '2026-04-27 15:44:35'),
(7, 'TKS166', 'Bus', 22, 'White/Blue', 'Decommissioned', '2026-04-27 15:46:08');

-- --------------------------------------------------------

--
-- Table structure for table `violation`
--

CREATE TABLE `violation` (
  `violation_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `trip_id` varchar(10) DEFAULT NULL,
  `violation_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `severity` enum('Low','Medium','High') DEFAULT 'Low',
  `penalty_amount` decimal(10,2) DEFAULT 0.00,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `handled_by` (`handled_by`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `license_number` (`license_number`);

--
-- Indexes for table `passenger_log`
--
ALTER TABLE `passenger_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `stop_id` (`stop_id`);

--
-- Indexes for table `route`
--
ALTER TABLE `route`
  ADD PRIMARY KEY (`route_id`),
  ADD UNIQUE KEY `route_name` (`route_name`);

--
-- Indexes for table `route_fare`
--
ALTER TABLE `route_fare`
  ADD PRIMARY KEY (`fare_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `from_stop_id` (`from_stop_id`),
  ADD KEY `to_stop_id` (`to_stop_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `stop`
--
ALTER TABLE `stop`
  ADD PRIMARY KEY (`stop_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `trip`
--
ALTER TABLE `trip`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- Indexes for table `violation`
--
ALTER TABLE `violation`
  ADD PRIMARY KEY (`violation_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaint`
--
ALTER TABLE `complaint`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `passenger_log`
--
ALTER TABLE `passenger_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `route`
--
ALTER TABLE `route`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `route_fare`
--
ALTER TABLE `route_fare`
  MODIFY `fare_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stop`
--
ALTER TABLE `stop`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `violation`
--
ALTER TABLE `violation`
  MODIFY `violation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `complaint_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`trip_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_ibfk_3` FOREIGN KEY (`handled_by`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `passenger_log`
--
ALTER TABLE `passenger_log`
  ADD CONSTRAINT `passenger_log_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`trip_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `passenger_log_ibfk_2` FOREIGN KEY (`stop_id`) REFERENCES `stop` (`stop_id`) ON DELETE CASCADE;

--
-- Constraints for table `route_fare`
--
ALTER TABLE `route_fare`
  ADD CONSTRAINT `route_fare_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `route` (`route_id`),
  ADD CONSTRAINT `route_fare_ibfk_2` FOREIGN KEY (`from_stop_id`) REFERENCES `stop` (`stop_id`),
  ADD CONSTRAINT `route_fare_ibfk_3` FOREIGN KEY (`to_stop_id`) REFERENCES `stop` (`stop_id`);

--
-- Constraints for table `stop`
--
ALTER TABLE `stop`
  ADD CONSTRAINT `stop_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `route` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `trip`
--
ALTER TABLE `trip`
  ADD CONSTRAINT `trip_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  ADD CONSTRAINT `trip_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
  ADD CONSTRAINT `trip_ibfk_3` FOREIGN KEY (`route_id`) REFERENCES `route` (`route_id`);

--
-- Constraints for table `violation`
--
ALTER TABLE `violation`
  ADD CONSTRAINT `violation_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `violation_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`trip_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `violation_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
