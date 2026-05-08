-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 08:19 PM
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
-- Database: `clinic`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','approved','completed','cancelled') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 1, '2026-04-12', '22:25:00', 'cancelled', NULL, '2026-04-12 14:25:03'),
(2, 1, 1, '2026-04-13', '13:00:00', 'cancelled', NULL, '2026-04-12 14:40:41'),
(3, 5, 1, '2026-05-01', '08:00:00', 'pending', NULL, '2026-04-12 16:52:48'),
(4, 1, 1, '2026-06-07', '08:00:00', 'cancelled', NULL, '2026-04-12 17:25:13'),
(5, 7, 1, '2026-04-14', '08:00:00', 'pending', NULL, '2026-04-12 17:47:14'),
(6, 8, 1, '2026-04-14', '07:00:00', 'approved', NULL, '2026-04-12 17:55:47'),
(7, 1, 1, '2026-04-14', '01:00:00', 'pending', NULL, '2026-04-12 17:57:50'),
(8, 1, 1, '2026-04-14', '06:00:00', 'pending', NULL, '2026-04-12 17:58:19'),
(9, 1, 1, '2026-04-19', '07:00:00', 'cancelled', NULL, '2026-04-12 17:58:49'),
(10, 1, 1, '2026-04-14', '10:00:00', 'pending', NULL, '2026-04-12 18:00:16'),
(11, 8, 1, '2026-04-25', '10:00:00', 'pending', NULL, '2026-04-12 18:03:34'),
(12, 9, 1, '2026-04-21', '13:00:00', 'approved', NULL, '2026-04-20 07:17:43'),
(13, 1, 1, '2026-05-01', '15:48:00', 'cancelled', NULL, '2026-04-27 15:44:07'),
(14, 1, 1, '2026-04-28', '07:00:00', 'approved', NULL, '2026-04-27 15:53:10'),
(15, 6, 1, '2026-04-29', '13:00:00', 'pending', NULL, '2026-04-28 03:17:42'),
(16, 10, 1, '2026-04-30', '13:27:00', 'approved', NULL, '2026-04-28 03:25:40'),
(17, 11, 1, '2026-04-01', '07:00:00', 'pending', NULL, '2026-04-28 08:02:03'),
(18, 11, 1, '2026-04-04', '07:00:00', 'approved', NULL, '2026-04-28 08:02:35'),
(19, 9, 1, '2026-04-26', '07:00:00', 'approved', NULL, '2026-04-28 08:11:11'),
(20, 1, 3, '2026-05-04', '12:00:00', 'pending', NULL, '2026-05-04 15:56:54'),
(21, 1, 1, '2026-05-04', '01:00:00', 'pending', NULL, '2026-05-04 15:59:02'),
(22, 1, 1, '2026-05-04', '14:00:00', 'pending', NULL, '2026-05-04 16:02:37'),
(23, 1, 1, '2026-05-05', '07:00:00', 'cancelled', NULL, '2026-05-04 16:03:30'),
(24, 1, 1, '2026-05-05', '08:00:00', 'approved', NULL, '2026-05-04 16:16:46'),
(25, 1, 3, '2026-05-05', '08:00:00', 'approved', NULL, '2026-05-04 16:18:32'),
(27, 1, 3, '2026-05-05', '09:00:00', 'approved', NULL, '2026-05-04 16:33:35'),
(28, 1, 1, '2026-05-05', '07:00:00', 'pending', NULL, '2026-05-04 17:49:55'),
(29, 14, 3, '2026-05-06', '02:30:00', 'pending', NULL, '2026-05-06 03:26:36'),
(30, 14, 1, '2026-05-06', '07:00:00', 'pending', NULL, '2026-05-06 03:27:44'),
(31, 14, 1, '2026-05-06', '09:00:00', 'pending', NULL, '2026-05-06 03:33:10'),
(32, 14, 3, '2026-05-06', '09:00:00', 'approved', NULL, '2026-05-06 03:33:48'),
(33, 15, 1, '2026-05-09', '07:00:00', 'approved', NULL, '2026-05-08 16:12:19');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `specialization`, `contact`, `status`, `created_at`) VALUES
(1, 'Jose Rizal', 'Technologist', '01234567890', 'available', '2026-04-10 17:49:31'),
(3, 'Dr. Jay Diaz', 'Technologist', '01234567890', 'available', '2026-04-28 03:26:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `type` enum('sms','email') NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `patient_id`, `appointment_id`, `type`, `message`, `status`, `sent_at`, `created_at`) VALUES
(1, 1, 2, 'sms', 'Your appointment is pending approval.', 'pending', NULL, '2026-04-12 14:40:41'),
(2, 1, 1, 'sms', 'Your appointment has been cancelled', 'pending', NULL, '2026-04-12 14:45:42'),
(3, 1, 2, 'sms', 'Your appointment has been approved', 'pending', NULL, '2026-04-12 14:45:46'),
(4, 1, 2, 'sms', 'Your appointment has been cancelled', 'pending', NULL, '2026-04-12 16:02:37'),
(5, 1, 2, 'sms', 'Your appointment has been cancelled', 'pending', NULL, '2026-04-12 16:03:32'),
(6, 1, 2, 'sms', 'Your appointment has been cancelled', 'pending', NULL, '2026-04-12 16:08:41'),
(7, 5, 3, 'sms', 'Your appointment is pending approval', 'pending', NULL, '2026-04-12 16:52:48'),
(8, 1, 4, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 17:25:19'),
(9, 1, 4, 'email', 'Approved', 'pending', NULL, '2026-04-12 17:25:38'),
(10, 1, 4, 'email', 'Cancelled', 'pending', NULL, '2026-04-12 17:26:14'),
(11, 7, 5, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 17:47:18'),
(12, 8, 6, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 17:55:53'),
(13, 8, 6, 'email', 'Approved', 'pending', NULL, '2026-04-12 17:56:16'),
(14, 1, 7, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 17:57:57'),
(15, 1, 8, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 17:58:27'),
(16, 1, 9, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 17:58:55'),
(17, 1, 9, 'email', 'Cancelled', 'pending', NULL, '2026-04-12 17:59:55'),
(18, 1, 10, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 18:00:23'),
(19, 8, 11, 'email', 'Pending Approval', 'pending', NULL, '2026-04-12 18:03:40'),
(20, 9, 12, 'email', 'Pending Approval', 'pending', NULL, '2026-04-20 07:18:19'),
(21, 9, 12, 'email', 'Approved', 'pending', NULL, '2026-04-20 07:18:49'),
(22, 9, 12, 'email', 'Approved', 'pending', NULL, '2026-04-20 07:18:54'),
(23, 1, 13, 'email', 'Pending Approval', 'pending', NULL, '2026-04-27 15:44:12'),
(24, 1, 13, 'email', 'Cancelled', 'pending', NULL, '2026-04-27 15:51:13'),
(25, 1, 13, 'email', 'Cancelled', 'pending', NULL, '2026-04-27 15:52:28'),
(26, 1, 13, 'email', 'Approved', 'pending', NULL, '2026-04-27 15:52:32'),
(27, 1, 13, 'email', 'Approved', 'pending', NULL, '2026-04-27 15:52:35'),
(28, 1, 14, 'email', 'Pending Approval', 'pending', NULL, '2026-04-27 15:53:13'),
(29, 1, 14, 'email', 'Approved', 'pending', NULL, '2026-04-27 15:57:02'),
(30, 1, 13, 'email', 'Cancelled', 'pending', NULL, '2026-04-27 15:57:43'),
(31, 6, 15, 'email', 'Pending Approval', 'pending', NULL, '2026-04-28 03:17:50'),
(32, 10, 16, 'email', 'Pending Approval', 'pending', NULL, '2026-04-28 03:25:46'),
(33, 10, 16, 'email', 'Approved', 'pending', NULL, '2026-04-28 03:27:02'),
(34, 10, 16, 'email', 'Approved', 'pending', NULL, '2026-04-28 03:27:06'),
(35, 11, 17, 'email', 'Pending Approval', 'pending', NULL, '2026-04-28 08:02:10'),
(36, 11, 18, 'email', 'Pending Approval', 'pending', NULL, '2026-04-28 08:02:43'),
(37, 11, 18, 'email', 'Approved', 'pending', NULL, '2026-04-28 08:03:23'),
(38, 9, 19, 'email', 'Pending Approval', 'pending', NULL, '2026-04-28 08:11:17'),
(39, 9, 19, 'email', 'Approved', 'pending', NULL, '2026-04-28 08:11:56'),
(40, 1, 20, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 15:56:59'),
(41, 1, 21, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 15:59:07'),
(42, 1, 22, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 16:02:41'),
(43, 1, 23, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 16:03:35'),
(44, 1, 24, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 16:16:51'),
(45, 1, 25, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 16:18:36'),
(46, 1, 25, 'email', 'Approved', 'pending', NULL, '2026-05-04 16:19:15'),
(47, 1, 27, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 16:33:40'),
(48, 1, 27, 'email', 'Approved', 'pending', NULL, '2026-05-04 16:39:32'),
(49, 1, 27, 'email', 'Approved', 'pending', NULL, '2026-05-04 16:44:39'),
(50, 1, 24, 'email', 'Approved', 'pending', NULL, '2026-05-04 17:04:33'),
(51, 1, 23, 'email', 'Cancelled', 'pending', NULL, '2026-05-04 17:20:47'),
(52, 1, 28, 'email', 'Pending Approval', 'pending', NULL, '2026-05-04 17:49:59'),
(53, 14, 29, 'email', 'Pending Approval', 'pending', NULL, '2026-05-06 03:26:41'),
(54, 14, 30, 'email', 'Pending Approval', 'pending', NULL, '2026-05-06 03:27:49'),
(55, 14, 31, 'email', 'Pending Approval', 'pending', NULL, '2026-05-06 03:33:14'),
(56, 14, 32, 'email', 'Pending Approval', 'pending', NULL, '2026-05-06 03:33:52'),
(57, 15, 33, 'email', 'Pending Approval', 'pending', NULL, '2026-05-08 16:12:24'),
(58, 15, 33, 'email', 'Approved', 'pending', NULL, '2026-05-08 16:13:04'),
(59, 14, 32, 'email', 'Approved', 'pending', NULL, '2026-05-08 16:15:00'),
(60, 15, 33, 'email', 'Approved', 'pending', NULL, '2026-05-08 16:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `contact` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `age`, `gender`, `contact`, `email`, `address`, `status`, `created_at`) VALUES
(1, 'Arjay Balmores G.', 20, 'Male', '09750529890', 'arjaybalmores1@gmail.com', 'Purok2, Rang-Ayan, Isabela', 'Pending', '2026-04-08 16:00:24'),
(2, 'Joshua Garcia', 20, 'Male', '01234567890', 'jusua@gmail.com', 'RA', 'Pending', '2026-04-09 06:06:14'),
(3, 'Daniel Padilia', 21, 'Male', '01234567890', 'daniel@gmail.com', 'RA', 'Pending', '2026-04-09 06:33:36'),
(4, 'Dwight Ramos', 25, 'Male', '09750529890', 'dwight@123gmail.com', 'RA', 'Pending', '2026-04-12 16:09:49'),
(5, 'Lebron James', 40, 'Male', '01234567890', 'lebron1@gmail.com', 'LA', 'Pending', '2026-04-12 16:10:52'),
(6, 'Jisun Titum', 30, 'Male', '01234567890', 'jisuntitum@gmail.com', 'LA', 'Pending', '2026-04-12 16:15:45'),
(7, 'Dominic Balmores', 45, 'Male', '09750529890', 'balmoresdominicesmer@gmail.com', 'Rang_Ayan', 'Pending', '2026-04-12 17:46:48'),
(8, 'Rj Balmores', 20, 'Male', '09750529890', 'arjaybalmores02gamail.c@gmail.com', 'RA', 'Pending', '2026-04-12 17:55:19'),
(9, 'Deo Pacis', 50, 'Male', '01234567890', 'pacisdeojames@gmail.com', 'Ragan Sur, Delfin Albano, Isabela', 'Pending', '2026-04-20 07:16:51'),
(10, 'Gweyne Gangan', 20, 'Male', '01234567890', 'gweyngangan@gmail.com', 'Brgy. Pitipiwiw', 'Pending', '2026-04-28 03:24:16'),
(11, 'Brix', 19, 'Male', '09750529890', 'gantalajohnbrix12@gmail.com', 'RA', 'Pending', '2026-04-28 08:01:27'),
(12, 'Luna', 20, 'Male', '01234567890', 'arjaybalmores1@gmail.com', 'Rang-Ayan', 'Pending', '2026-05-06 02:53:04'),
(13, 'Juann Guada', 45, 'Female', '09750529890', 'ayyo@gamil.com', 'Rang Ayan\\r\\n', 'Pending', '2026-05-06 03:10:51'),
(14, 'Arjay Balmores', 25, 'Male', '01234567890', 'arjaybalmores1@gmail.com', 'Rang-Ayan', 'Pending', '2026-05-06 03:22:19'),
(15, 'Dominic Balmores', 25, 'Male', '09750529890', 'dominicbalmores6@gmail.com', 'Rang-Ayang', 'Pending', '2026-05-08 15:51:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','patient') DEFAULT 'patient',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, '', 'arjaybalmores1@gmail.com', '$2y$10$k6nRDDYOaPEPNt6O7Jb8zOD3uCA4lc6KlKyK3CO3uojwvmTG1LEhu', 'admin', '2026-03-27 02:07:57'),
(2, 'Rj', 'rj@balmoresgmail.com', '$2y$10$BLM6ItjAU/2IZv9AZvp/5e0AzTAz4/A40lv9SIHqEfGJ55p84d76S', 'admin', '2026-03-27 02:20:52'),
(3, 'Arjay Balmores', 'arjaybalmores1@gmail.com', '$2y$10$rkMgIsdUuGhIpjhQw4eSAObFY78viD92hOHJc92zjzyAPRt4JByY6', 'admin', '2026-03-27 02:25:01'),
(4, 'Joe', 'ayyo@gamil.com', '$2y$10$Qlg6A8tOE0dQLzdR9zc2pexD0I8Gfa6cs60GiN4r/2YzYCIrVLOIG', 'admin', '2026-04-28 03:14:29'),
(5, 'Minik', 'dominicbalmores6@gmail.com', '$2y$10$iUsCwgLpZTt0sc8xckdPf.JA1zhu7vGYAof0PSaxmEsjx23cbxx4a', 'patient', '2026-05-08 15:51:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
