-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2025 at 09:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pitstop pro`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admins`
--

CREATE TABLE `tbl_admins` (
  `admin_id` int(11) NOT NULL,
  `Username` varchar(60) NOT NULL,
  `password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admins`
--

INSERT INTO `tbl_admins` (`admin_id`, `Username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bill`
--

CREATE TABLE `tbl_bill` (
  `bill_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `Payment_status` enum('paid','not paid') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bookings`
--

CREATE TABLE `tbl_bookings` (
  `booking_id` int(11) NOT NULL,
  `Username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `User_id_` int(11) DEFAULT NULL,
  `Service_id` int(11) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `booking_time` time DEFAULT NULL,
  `b_status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `special_request` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `slot_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `User_id_` int(11) NOT NULL,
  `Username` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `Phone` varchar(10) NOT NULL,
  `Password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_services`
--

CREATE TABLE `tbl_services` (
  `Service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `Estimated_duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_services`
--

INSERT INTO `tbl_services` (`Service_id`, `service_name`, `description`, `price`, `Estimated_duration`) VALUES
(1, 'Engine Tuning', 'Complete engine tuning and optimization', 1500.00, 120),
(2, 'Paint work', 'Full car paint job and touch-up', 5000.00, 300),
(3, 'Break check', 'Brake system inspection and maintenance', 800.00, 60),
(4, 'Service', 'Regular car service and maintenance', 2000.00, 120),
(5, 'Wheel Alignment', 'Wheel alignment and balancing', 600.00, 45),
(6, 'Body Work', 'Dent removal and body repairs', 3500.00, 240),
(7, 'Accessories', 'Car accessories installation', 1000.00, 90),
(8, 'Washing', 'Complete car wash and detailing', 500.00, 60);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_slot`
--

CREATE TABLE `tbl_slot` (
  `slot_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_capacity` int(11) DEFAULT 3,
  `current_bookings` int(11) DEFAULT 0,
  `S_status` enum('Available','Fully Booked','Blocked') DEFAULT 'Available',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_slot`
--

INSERT INTO `tbl_slot` (`slot_id`, `slot_date`, `start_time`, `end_time`, `max_capacity`, `current_bookings`, `S_status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2025-11-01', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(2, '2025-11-01', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(3, '2025-11-01', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(4, '2025-11-01', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(5, '2025-11-01', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(6, '2025-11-01', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(7, '2025-11-01', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(8, '2025-11-01', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(9, '2025-11-01', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(10, '2025-11-01', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(11, '2025-11-02', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(12, '2025-11-02', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(13, '2025-11-02', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(14, '2025-11-02', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(15, '2025-11-02', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(16, '2025-11-02', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(17, '2025-11-02', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(18, '2025-11-02', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(19, '2025-11-02', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(20, '2025-11-02', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(21, '2025-11-03', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(22, '2025-11-03', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(23, '2025-11-03', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(24, '2025-11-03', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(25, '2025-11-03', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(26, '2025-11-03', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(27, '2025-11-03', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(28, '2025-11-03', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(29, '2025-11-03', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(30, '2025-11-03', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(31, '2025-11-04', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(32, '2025-11-04', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(33, '2025-11-04', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(34, '2025-11-04', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(35, '2025-11-04', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(36, '2025-11-04', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(37, '2025-11-04', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(38, '2025-11-04', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(39, '2025-11-04', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(40, '2025-11-04', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(41, '2025-11-05', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(42, '2025-11-05', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(43, '2025-11-05', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(44, '2025-11-05', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(45, '2025-11-05', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(46, '2025-11-05', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(47, '2025-11-05', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(48, '2025-11-05', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(49, '2025-11-05', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(50, '2025-11-05', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(51, '2025-11-06', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(52, '2025-11-06', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(53, '2025-11-06', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(54, '2025-11-06', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(55, '2025-11-06', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(56, '2025-11-06', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(57, '2025-11-06', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(58, '2025-11-06', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(59, '2025-11-06', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(60, '2025-11-06', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(61, '2025-11-07', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(62, '2025-11-07', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(63, '2025-11-07', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(64, '2025-11-07', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(65, '2025-11-07', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(66, '2025-11-07', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(67, '2025-11-07', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(68, '2025-11-07', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(69, '2025-11-07', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(70, '2025-11-07', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(71, '2025-11-08', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(72, '2025-11-08', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(73, '2025-11-08', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(74, '2025-11-08', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(75, '2025-11-08', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(76, '2025-11-08', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(77, '2025-11-08', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(78, '2025-11-08', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(79, '2025-11-08', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(80, '2025-11-08', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(81, '2025-11-09', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(82, '2025-11-09', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(83, '2025-11-09', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(84, '2025-11-09', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(85, '2025-11-09', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(86, '2025-11-09', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(87, '2025-11-09', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(88, '2025-11-09', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(89, '2025-11-09', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(90, '2025-11-09', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(91, '2025-11-10', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(92, '2025-11-10', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(93, '2025-11-10', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(94, '2025-11-10', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(95, '2025-11-10', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(96, '2025-11-10', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(97, '2025-11-10', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(98, '2025-11-10', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(99, '2025-11-10', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(100, '2025-11-10', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(101, '2025-11-11', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(102, '2025-11-11', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(103, '2025-11-11', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(104, '2025-11-11', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(105, '2025-11-11', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(106, '2025-11-11', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(107, '2025-11-11', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(108, '2025-11-11', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(109, '2025-11-11', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(110, '2025-11-11', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(111, '2025-11-12', '08:00:00', '09:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(112, '2025-11-12', '09:00:00', '10:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(113, '2025-11-12', '10:00:00', '11:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(114, '2025-11-12', '11:00:00', '12:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(115, '2025-11-12', '12:00:00', '13:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(116, '2025-11-12', '13:00:00', '14:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(117, '2025-11-12', '14:00:00', '15:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(118, '2025-11-12', '15:00:00', '16:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(119, '2025-11-12', '16:00:00', '17:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40'),
(120, '2025-11-12', '17:00:00', '18:00:00', 5, 0, 'Available', 1, '2025-11-08 19:16:40', '2025-11-08 19:16:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admins`
--
ALTER TABLE `tbl_admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `tbl_bill`
--
ALTER TABLE `tbl_bill`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `fk_tbl_bill_user` (`booking_id`);

--
-- Indexes for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_bookings_user` (`User_id_`),
  ADD KEY `fk_bookings_service` (`Service_id`),
  ADD KEY `fk_bookings_slot` (`slot_id`);

--
-- Indexes for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  ADD PRIMARY KEY (`User_id_`);

--
-- Indexes for table `tbl_services`
--
ALTER TABLE `tbl_services`
  ADD PRIMARY KEY (`Service_id`);

--
-- Indexes for table `tbl_slot`
--
ALTER TABLE `tbl_slot`
  ADD PRIMARY KEY (`slot_id`),
  ADD UNIQUE KEY `unique_slot_time` (`slot_date`,`start_time`),
  ADD KEY `fk_slot_created_by` (`created_by`),
  ADD KEY `idx_slot_date` (`slot_date`),
  ADD KEY `idx_slot_status` (`S_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admins`
--
ALTER TABLE `tbl_admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_bill`
--
ALTER TABLE `tbl_bill`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `User_id_` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_services`
--
ALTER TABLE `tbl_services`
  MODIFY `Service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_slot`
--
ALTER TABLE `tbl_slot`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_bill`
--
ALTER TABLE `tbl_bill`
  ADD CONSTRAINT `fk_tbl_bill_user` FOREIGN KEY (`booking_id`) REFERENCES `tbl_bookings` (`booking_id`);

--
-- Constraints for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD CONSTRAINT `fk_bookings_slot` FOREIGN KEY (`slot_id`) REFERENCES `tbl_slot` (`slot_id`);

--
-- Constraints for table `tbl_slot`
--
ALTER TABLE `tbl_slot`
  ADD CONSTRAINT `fk_slot_created_by` FOREIGN KEY (`created_by`) REFERENCES `tbl_admins` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
