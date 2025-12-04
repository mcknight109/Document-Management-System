-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 08:05 AM
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
-- Database: `document_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_designs`
--

CREATE TABLE `activity_designs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `control_no` varchar(50) NOT NULL,
  `department` varchar(255) NOT NULL,
  `activity_title` varchar(255) NOT NULL,
  `budget` decimal(12,2) NOT NULL,
  `date_out` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_designs`
--

INSERT INTO `activity_designs` (`id`, `user_id`, `control_no`, `department`, `activity_title`, `budget`, `date_out`, `created_at`) VALUES
(56, 5, '1', 'Finance Department', 'Budget Allocation Meeting', 15000.00, '2025-01-05', '2025-11-25 15:25:51'),
(57, 5, '2', 'HR Department', 'Employee Training Session', 8000.00, '2025-01-07', '2025-11-25 15:25:51'),
(58, 5, '3', 'IT Department', 'System Maintenance Activity', 12000.00, '2025-01-10', '2025-11-25 15:25:51'),
(59, 5, '4', 'Research Department', 'Field Research Activity', 22000.00, '2025-01-12', '2025-11-25 15:25:51'),
(60, 5, '5', 'Marketing Department', 'Marketing Campaign Launch', 30000.00, '2025-01-14', '2025-11-25 15:25:51'),
(61, 5, '6', 'Admin Department', 'Office Supplies Purchase', 5000.00, '2025-01-15', '2025-11-25 15:25:51'),
(62, 5, '7', 'Science Department', 'Lab Equipment Calibration', 18000.00, '2025-01-17', '2025-11-25 15:25:51'),
(63, 5, '8', 'Sports Department', 'Sports Event Preparation', 10000.00, '2025-01-18', '2025-11-25 15:25:51'),
(64, 5, '9', 'Library Department', 'Book Acquisition Activity', 7500.00, '2025-01-20', '2025-11-25 15:25:51'),
(65, 5, '10', 'Security Department', 'Campus Security Audit', 9000.00, '2025-01-21', '2025-11-25 15:25:51');

-- --------------------------------------------------------

--
-- Table structure for table `archived_users`
--

CREATE TABLE `archived_users` (
  `id` int(11) NOT NULL,
  `original_user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(5) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','encoder') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `permissions` longtext DEFAULT NULL,
  `transmittal_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_users`
--

INSERT INTO `archived_users` (`id`, `original_user_id`, `first_name`, `middle_initial`, `last_name`, `username`, `email`, `password`, `role`, `status`, `permissions`, `transmittal_id`, `created_at`) VALUES
(1, 9, 'John', 'M', 'Student', 'pyang', 'pyang@gmail.com', '$2y$10$X9.B9GhneNdkpKUI4NqsMOI9Gm/3AQffW1vLe6adSAUntJbeEUVp.', 'admin', 'Active', '[\"voucher_records\",\"check_records\",\"communications_records\",\"activity_records\",\"certificate_records\"]', 1116, '2025-11-26 02:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_records`
--

CREATE TABLE `certificate_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `control_no` varchar(100) NOT NULL,
  `project` varchar(255) NOT NULL,
  `office` varchar(255) NOT NULL,
  `date_out` date DEFAULT NULL,
  `claimed_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_records`
--

INSERT INTO `certificate_records` (`id`, `user_id`, `control_no`, `project`, `office`, `date_out`, `claimed_by`, `created_at`) VALUES
(52, 5, '1', 'Barangay Clearance Processing', 'Barangay Office', '2025-01-05', 'Juan Dela Cruz', '2025-11-25 15:46:39'),
(53, 5, '2', 'Construction Permit Certification', 'Engineering Office', '2025-01-06', 'Maria Santos', '2025-11-25 15:46:39'),
(54, 5, '3', 'Business Permit Renewal', 'Business Licensing Office', '2025-01-07', 'Carlos Gomez', '2025-11-25 15:46:39'),
(55, 5, '4', 'Residency Certificate Issuance', 'Municipal Hall', '2025-01-08', 'Ana Lopez', '2025-11-25 15:46:39'),
(56, 5, '5', 'Health Certificate Request', 'Health Office', '2025-01-09', 'Mark Reyes', '2025-11-25 15:46:39'),
(57, 5, '6', 'Senior Citizen ID Processing', 'Senior Citizens Office', '2025-01-10', 'Pedro Martinez', '2025-11-25 15:46:39'),
(58, 5, '7', 'Scholarship Document Filing', 'Education Office', '2025-01-11', 'Jasmine Cruz', '2025-11-25 15:46:39'),
(59, 5, '8', 'Employment Certificate Issuance', 'HR Department', '2025-01-12', 'Rafael Fernandez', '2025-11-25 15:46:39'),
(60, 5, '9', 'Police Clearance Request', 'Police Station', '2025-01-13', 'Lucia Mendoza', '2025-11-25 15:46:39'),
(61, 5, '10', 'Certificate of Appearance', 'Mayor’s Office', '2025-01-14', 'Miguel Ramos', '2025-11-25 15:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `com_id` varchar(100) NOT NULL,
  `date_received` date NOT NULL,
  `sender` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `indorse_to` varchar(255) DEFAULT NULL,
  `date_routed` date DEFAULT NULL,
  `routed_by` varchar(255) DEFAULT NULL,
  `action_taken` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communications`
--

INSERT INTO `communications` (`id`, `user_id`, `com_id`, `date_received`, `sender`, `description`, `indorse_to`, `date_routed`, `routed_by`, `action_taken`, `remarks`, `created_at`) VALUES
(50, 5, '1', '2025-01-05', 'Department of Education', 'Request for updated enrollment statistics.', 'Planning Office', '2025-01-06', NULL, 'Forwarded to Planning Office', 'Awaiting response', '2025-11-25 15:55:10'),
(51, 5, '2', '2025-01-06', 'Barangay Captain Lopez', 'Letter of request for additional streetlights.', 'Engineering Department', '2025-01-07', NULL, 'Endorsed to Engineering', 'For evaluation', '2025-11-25 15:55:10'),
(52, 5, '3', '2025-01-07', 'Police Station', 'Report on community safety meeting results.', 'Mayor’s Office', '2025-01-08', NULL, 'Forwarded to Mayor', 'No issues noted', '2025-11-25 15:55:10'),
(53, 5, '4', '2025-01-08', 'Municipal Treasurer', 'Submission of updated tax collection summary.', 'Accounting Office', '2025-01-09', NULL, 'Routed to Accounting', 'Filed for record', '2025-11-25 15:55:10'),
(54, 5, '5', '2025-01-09', 'DILG', 'Compliance reminder for annual report submission.', 'Administration Office', '2025-01-10', NULL, 'Noted and routed', 'Deadline approaching', '2025-11-25 15:55:10'),
(55, 5, '6', '2025-01-10', 'Environmental Office', 'Request for cleanup drive volunteers.', 'HR Department', '2025-01-11', NULL, 'Endorsed to HR', 'Pending approval', '2025-11-25 15:55:10'),
(56, 5, '7', '2025-01-11', 'Local Business Association', 'Invitation to business forum event.', 'Mayor’s Office', '2025-01-12', NULL, 'Forwarded to Mayor', 'Scheduled', '2025-11-25 15:55:10'),
(57, 5, '8', '2025-01-12', 'City Health Office', 'Report on recent medical outreach program.', 'Records Office', '2025-01-13', NULL, 'Filed', 'Document archived', '2025-11-25 15:55:10'),
(58, 5, '9', '2025-01-13', 'Municipal Agriculture Office', 'Proposal for farming assistance program.', 'Budget Office', '2025-01-14', 'Marc', 'Endorsed for review', 'Awaiting budget allocation', '2025-11-25 15:55:10'),
(59, 5, '10', '2025-01-14', 'Red Cross', 'Letter of appreciation for local disaster assistance.', 'Mayor’s Office', '2025-01-15', NULL, 'Received and acknowledged', 'For documentation', '2025-11-25 15:55:10');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `control_no` varchar(50) NOT NULL,
  `payee` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `date_in` datetime DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  `fund_type` varchar(100) DEFAULT NULL,
  `bank_channel` varchar(100) DEFAULT NULL,
  `check_date` datetime DEFAULT NULL,
  `status` enum('In','Out','Check Out','Check Release') DEFAULT 'In',
  `check_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `control_no`, `payee`, `description`, `amount`, `date_in`, `date_out`, `fund_type`, `bank_channel`, `check_date`, `status`, `check_number`) VALUES
(52, 3, '1', 'Neil', 'Alferez', 100000.00, '2025-11-24 14:28:13', '2025-12-03 11:50:53', 'Banko Central', NULL, NULL, NULL, NULL),
(53, 3, '2', 'Marc', 'Epe', 100.00, '2025-11-24 14:51:35', NULL, 'Banko Central', NULL, NULL, NULL, NULL),
(54, 5, '3', 'Marc', 'Marcky', 12312312.00, '2025-12-03 02:08:12', '2025-12-03 13:11:51', 'Land Bank', 'Land Bank', NULL, NULL, '98481493'),
(55, 5, '4', 'Gwapo ko', 'gwapo ko 123', 300.00, '2025-12-04 13:34:23', NULL, 'lank bank', NULL, NULL, 'In', NULL),
(56, 5, '5', 'Don', 'Epe', 10000.00, '2025-12-04 13:50:28', NULL, 'igot', NULL, NULL, 'In', NULL),
(57, 5, '6', 'Gwapo ko 123', '123', 10.00, '2025-12-04 13:51:27', NULL, 'Lank Bank', NULL, NULL, 'In', NULL),
(59, 5, '8', 'Maue', 'Nuwa', 1000.01, '2025-12-04 14:41:40', NULL, 'Banko Central', NULL, NULL, 'In', NULL),
(60, 5, '9', 'Ambot', 'Lang', 100000000000.00, '2025-12-04 14:43:58', NULL, 'Ani', NULL, NULL, 'In', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `role` enum('admin','encoder') NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `username`, `role`, `login_time`) VALUES
(1, 1, 'admin', 'admin', '2025-10-29 03:31:21'),
(2, 1, 'admin', 'admin', '2025-10-29 03:34:16'),
(3, 3, 'encoder', 'encoder', '2025-10-29 03:36:21'),
(4, 1, 'admin', 'admin', '2025-11-10 07:51:40'),
(5, 3, 'encoder', 'encoder', '2025-11-10 14:17:23'),
(6, 3, 'encoder', 'encoder', '2025-11-11 05:45:06'),
(8, 5, 'mcky', 'encoder', '2025-11-11 06:27:23'),
(9, 3, 'encoder', 'encoder', '2025-11-11 17:38:40'),
(10, 5, 'mcky', 'encoder', '2025-11-11 17:43:36'),
(11, 3, 'encoder', 'encoder', '2025-11-11 17:47:12'),
(12, 3, 'encoder', 'encoder', '2025-11-11 17:49:39'),
(13, 3, 'encoder', 'encoder', '2025-11-11 18:08:28'),
(14, 3, 'encoder', 'encoder', '2025-11-13 01:42:08'),
(15, 5, 'mcky', 'encoder', '2025-11-13 01:53:43'),
(16, 5, 'mcky', 'encoder', '2025-11-13 02:31:34'),
(17, 3, 'encoder', 'encoder', '2025-11-13 14:22:24'),
(18, 5, 'mcky', 'encoder', '2025-11-13 15:21:39'),
(19, 5, 'mcky', 'encoder', '2025-11-16 15:52:36'),
(20, 3, 'encoder', 'encoder', '2025-11-17 02:34:05'),
(21, 3, 'encoder', 'encoder', '2025-11-24 02:03:36'),
(22, 5, 'mcky', 'encoder', '2025-11-24 02:05:07'),
(23, 5, 'mcky', 'encoder', '2025-11-24 03:31:59'),
(24, 1, 'admin', 'admin', '2025-11-24 03:37:25'),
(25, 3, 'encoder', 'encoder', '2025-11-24 03:40:34'),
(26, 1, 'admin', 'admin', '2025-11-24 03:48:00'),
(27, 3, 'encoder', 'encoder', '2025-11-24 03:54:03'),
(28, 6, 'marcky', 'encoder', '2025-11-24 04:41:13'),
(29, 3, 'encoder', 'encoder', '2025-11-24 04:41:41'),
(30, 1, 'admin', 'admin', '2025-11-24 10:49:34'),
(31, 3, 'encoder', 'encoder', '2025-11-24 11:08:20'),
(32, 1, 'admin', 'admin', '2025-11-24 11:11:27'),
(33, 1, 'admin', 'admin', '2025-11-24 11:22:27'),
(34, 1, 'admin', 'admin', '2025-11-24 11:28:26'),
(35, 3, 'encoder', 'encoder', '2025-11-24 11:44:12'),
(36, 1, 'admin', 'admin', '2025-11-24 11:45:22'),
(37, 8, 'diane', 'admin', '2025-11-24 11:47:17'),
(38, 8, 'diane', 'encoder', '2025-11-24 11:47:36'),
(39, 5, 'mcky', 'encoder', '2025-11-24 12:01:53'),
(40, 8, 'diane', 'encoder', '2025-11-25 13:53:00'),
(41, 3, 'encoder', 'encoder', '2025-11-25 14:11:59'),
(42, 1, 'admin', 'admin', '2025-11-26 01:49:39'),
(43, 1, 'admin', 'admin', '2025-11-26 02:59:49'),
(44, 3, 'encoder', 'encoder', '2025-11-26 06:14:22'),
(45, 8, 'diane', 'encoder', '2025-11-26 06:17:26'),
(46, 1, 'admin', 'admin', '2025-11-26 06:28:31'),
(47, 3, 'encoder', 'encoder', '2025-11-26 06:32:55'),
(48, 1, 'admin', 'admin', '2025-11-26 06:39:53'),
(49, 6, 'marcky', 'encoder', '2025-11-26 06:40:21'),
(50, 1, 'admin', 'admin', '2025-11-26 08:29:02'),
(51, 3, 'encoder', 'encoder', '2025-11-26 08:29:08'),
(52, 5, 'mcky', 'encoder', '2025-12-02 16:24:05'),
(53, 1, 'admin', 'admin', '2025-12-02 16:27:11'),
(54, 5, 'mcky', 'encoder', '2025-12-03 02:09:33'),
(55, 1, 'admin', 'admin', '2025-12-03 02:12:15'),
(56, 5, 'mcky', 'encoder', '2025-12-04 02:40:31');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`) VALUES
(1, 1, 'ad8aa8cc01c9b9fd108dfd569d39b398', '2025-11-17 02:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `transmittal_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(5) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','encoder') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `transmittal_id`, `first_name`, `middle_initial`, `last_name`, `username`, `email`, `password`, `role`, `status`, `permissions`, `created_at`) VALUES
(1, 1110, 'Admin', 'A.', 'Administrator', 'admin', 'user1@example.com', 'admin123', 'admin', 'Active', NULL, '2025-10-29 03:21:47'),
(3, 1111, 'Example', 'M.', 'Sample', 'encoder', 'user3@example.com', 'encoder123', 'encoder', 'Active', NULL, '2025-10-29 03:35:55'),
(5, 1112, 'Neil', 'M.', 'Alferez', 'mcky', 'user5@example.com', 'neil123', 'encoder', 'Active', '[\"voucher_records\",\"check_records\",\"communications_records\",\"activity_records\",\"certificate_records\"]', '2025-11-11 06:26:55'),
(6, 1113, 'Marc', 'M.', 'Epe', 'marcky', 'marc@gmail.com', 'marc123', 'encoder', 'Active', NULL, '2025-11-24 03:52:03'),
(7, 1114, 'Johnna', 'M', 'Quevedo', 'johnna', 'johnna@gmail.com', 'johnna123', 'encoder', 'Active', NULL, '2025-11-24 11:05:04'),
(8, 1115, 'Diane', 'M', 'Alferez', 'diane', 'diane@gmail.com', 'diane123', 'encoder', 'Active', '[\"voucher_records\",\"check_records\",\"communications_records\",\"activity_records\",\"certificate_records\"]', '2025-11-24 11:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_designs`
--
ALTER TABLE `activity_designs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `archived_users`
--
ALTER TABLE `archived_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `certificate_records`
--
ALTER TABLE `certificate_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_designs`
--
ALTER TABLE `activity_designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `archived_users`
--
ALTER TABLE `archived_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificate_records`
--
ALTER TABLE `certificate_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_designs`
--
ALTER TABLE `activity_designs`
  ADD CONSTRAINT `activity_designs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `certificate_records`
--
ALTER TABLE `certificate_records`
  ADD CONSTRAINT `certificate_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `communications`
--
ALTER TABLE `communications`
  ADD CONSTRAINT `communications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
