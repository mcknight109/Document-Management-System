-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2025 at 05:27 AM
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
  `budget` decimal(20,2) NOT NULL,
  `date_out` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_designs`
--

INSERT INTO `activity_designs` (`id`, `user_id`, `control_no`, `department`, `activity_title`, `budget`, `date_out`, `created_at`) VALUES
(69, 5, '1', 'Marketing', 'Social Media Campaign', 5000.00, '2025-12-01', '2025-12-11 03:17:30'),
(70, 5, '2', 'Finance', 'Quarterly Budget Review', 12000.00, '2025-12-02', '2025-12-11 03:17:30'),
(71, 5, '3', 'HR', 'Employee Training', 8000.00, '2025-12-03', '2025-12-11 03:17:30'),
(72, 5, '4', 'IT', 'Website Redesign', 15000.00, '2025-12-04', '2025-12-11 03:17:30'),
(73, 5, '5', 'Operations', 'Warehouse Audit', 6000.00, '2025-12-05', '2025-12-11 03:17:30'),
(74, 5, '6', 'Marketing', 'Email Marketing Blast', 4000.00, '2025-12-06', '2025-12-11 03:17:30'),
(75, 5, '7', 'Finance', 'Payroll Processing', 10000.00, '2025-12-07', '2025-12-11 03:17:30'),
(76, 5, '8', 'HR', 'Team Building Activity', 7000.00, '2025-12-08', '2025-12-11 03:17:30'),
(77, 5, '9', 'IT', 'Server Maintenance', 9000.00, '2025-12-09', '2025-12-11 03:17:30'),
(78, 5, '10', 'Operations', 'Inventory Management', 11000.00, '2025-12-10', '2025-12-11 03:17:30'),
(79, 5, '11', 'Marketing', 'Product Launch Event', 20000.00, '2025-12-11', '2025-12-11 03:17:30'),
(80, 5, '12', 'Finance', 'Audit Preparation', 13000.00, '2025-12-12', '2025-12-11 03:17:30'),
(81, 5, '13', 'HR', 'Recruitment Drive', 6000.00, '2025-12-13', '2025-12-11 03:17:30'),
(82, 5, '14', 'IT', 'Software Upgrade', 14000.00, '2025-12-14', '2025-12-11 03:17:30'),
(83, 5, '15', 'Operations', 'Logistics Planning', 8000.00, '2025-12-15', '2025-12-11 03:17:30'),
(84, 5, '16', 'Marketing', 'Brand Awareness Campaign', 7500.00, '2025-12-16', '2025-12-11 03:17:30'),
(85, 5, '17', 'Finance', 'Tax Filing', 9000.00, '2025-12-17', '2025-12-11 03:17:30'),
(86, 5, '18', 'HR', 'Performance Appraisal', 5000.00, '2025-12-18', '2025-12-11 03:17:30'),
(87, 5, '19', 'IT', 'Cybersecurity Audit', 16000.00, '2025-12-19', '2025-12-11 03:17:30'),
(88, 5, '20', 'Operations', 'Facility Inspection', 7000.00, '2025-12-20', '2025-12-11 03:17:30');

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
(69, 5, '1', 'Project Alpha', 'Office A', '2025-01-10', 'John Doe', '2025-12-11 03:23:06'),
(70, 5, '2', 'Project Beta', 'Office B', '2025-01-12', 'Jane Smith', '2025-12-11 03:23:06'),
(71, 5, '3', 'Project Gamma', 'Office C', '2025-01-15', 'Alice Brown', '2025-12-11 03:23:06'),
(72, 5, '4', 'Project Delta', 'Office D', '2025-01-18', 'Bob Johnson', '2025-12-11 03:23:06'),
(73, 5, '5', 'Project Epsilon', 'Office A', '2025-01-20', 'Charlie Lee', '2025-12-11 03:23:06'),
(74, 5, '6', 'Project Zeta', 'Office B', '2025-01-22', 'Diana Prince', '2025-12-11 03:23:06'),
(75, 5, '7', 'Project Eta', 'Office C', '2025-01-25', 'Ethan Hunt', '2025-12-11 03:23:06'),
(76, 5, '8', 'Project Theta', 'Office D', '2025-01-28', 'Fiona Glenanne', '2025-12-11 03:23:06'),
(77, 5, '9', 'Project Iota', 'Office A', '2025-02-01', 'George Michael', '2025-12-11 03:23:06'),
(78, 5, '10', 'Project Kappa', 'Office B', '2025-02-05', 'Hannah Montana', '2025-12-11 03:23:06'),
(79, 5, '11', 'Project Lambda', 'Office C', '2025-02-08', 'Ian Fleming', '2025-12-11 03:23:06'),
(80, 5, '12', 'Project Mu', 'Office D', '2025-02-12', 'Jessica Alba', '2025-12-11 03:23:06'),
(81, 5, '13', 'Project Nu', 'Office A', '2025-02-15', 'Kevin Hart', '2025-12-11 03:23:06'),
(82, 5, '14', 'Project Xi', 'Office B', '2025-02-18', 'Laura Croft', '2025-12-11 03:23:06'),
(83, 5, '15', 'Project Omicron', 'Office C', '2025-02-22', 'Michael Scott', '2025-12-11 03:23:06'),
(84, 5, '16', 'Project Pi', 'Office D', '2025-02-25', 'Nancy Drew', '2025-12-11 03:23:06'),
(85, 5, '17', 'Project Rho', 'Office A', '2025-03-01', 'Oscar Wilde', '2025-12-11 03:23:06'),
(86, 5, '18', 'Project Sigma', 'Office B', '2025-03-05', 'Pam Beesly', '2025-12-11 03:23:06'),
(87, 5, '19', 'Project Tau', 'Office C', '2025-03-08', 'Quentin Tarantino', '2025-12-11 03:23:06'),
(88, 5, '20', 'Project Upsilon', 'Office D', '2025-03-12', 'Rachel Green', '2025-12-11 03:23:06');

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
(1, 5, '1', '2025-01-03', 'DILG Cebu', 'Request for updated tax clearance.', 'ADMIN DIVISION', '2025-01-03', 'Atty. Cruz', 'APPROPRIATE ACTION', 'Processed.', '2025-12-11 03:02:39'),
(2, 5, '2', '2025-01-05', 'Barangay Poblacion', 'Submission of annual report.', 'LANDTAX DIVISION', '2025-01-05', 'Mr. Santos', 'FILE', 'Filed in records.', '2025-12-11 03:02:39'),
(3, 5, '3', '2025-01-06', 'Business Permit Office', 'Inquiry regarding permit verification.', 'LICENSE DIVISION', '2025-01-06', 'Ms. Reyes', 'COMMENT/SAND/OR', 'Forwarded.', '2025-12-11 03:02:39'),
(4, 5, '4', '2025-01-07', 'City Treasurer', 'Notice for fund allocation.', 'CASH DIVISION', '2025-01-07', 'Mr. Lopez', 'U-R-G-E-N-T', 'Immediate attention.', '2025-12-11 03:02:39'),
(5, 5, '5', '2025-01-08', 'Civil Service Commission', 'Guidelines on new memo.', 'TORU DIVISION', '2025-01-08', 'Atty. Ramos', 'FURNISH COPY', 'Distributed.', '2025-12-11 03:02:39'),
(6, 5, '6', '2025-01-09', 'DepEd Cebu', 'Request for budget certification.', 'RECORDS SECTION', '2025-01-09', 'Ms. Flores', 'APPROPRIATE ACTION', 'Ongoing review.', '2025-12-11 03:02:39'),
(7, 5, '7', '2025-01-10', 'LTO Region 7', 'Coordination for joint inspection.', 'BUS. TAX MAPPING SECTION', '2025-01-10', 'Mr. Garcia', 'REPRESENT THIS OFFICE', 'Coordinated.', '2025-12-11 03:02:39'),
(8, 5, '8', '2025-01-11', 'Office of the Mayor', 'Urgent endorsement to finance.', 'GRACE ATTY. TERENCE', '2025-01-11', 'Atty. Mendoza', 'U-R-G-E-N-T', 'Handled immediately.', '2025-12-11 03:02:39'),
(9, 5, '9', '2025-01-12', 'Barangay Tayud', 'Submission of permit requests.', 'LICENSE DIVISION', '2025-01-12', 'Mr. Chan', 'FILE', 'Completed.', '2025-12-11 03:02:39'),
(10, 5, '10', '2025-01-13', 'DBM Region VII', 'Clarification on budget circular.', 'ADMIN DIVISION', '2025-01-13', 'Ms. Domingo', 'COMMENT/SAND/OR', 'Awaiting reply.', '2025-12-11 03:02:39'),
(11, 5, '11', '2025-01-14', 'DOT Cebu', 'Endorsement for tourism support.', 'RECORDS SECTION', '2025-01-14', 'Mr. Manuel', 'FURNISH COPY', 'Copy furnished.', '2025-12-11 03:02:39'),
(12, 5, '12', '2025-01-15', 'DSWD Region 7', 'Requesting tax exemption assessment.', 'LANDTAX DIVISION', '2025-01-15', 'Ms. Hernandez', 'APPROPRIATE ACTION', 'Forwarded.', '2025-12-11 03:02:39'),
(13, 5, '13', '2025-01-16', 'BIR Cebu', 'Assessment for delinquent accounts.', 'CASH DIVISION', '2025-01-16', 'Mr. Rivera', 'U-R-G-E-N-T', 'Handled.', '2025-12-11 03:02:39'),
(14, 5, '14', '2025-01-17', 'PhilHealth', 'Letter for coordination meeting.', 'TORU DIVISION', '2025-01-17', 'Atty. Dizon', 'REPRESENT THIS OFFICE', 'Scheduled.', '2025-12-11 03:02:39'),
(15, 5, '15', '2025-01-18', 'GSIS Cebu', 'Document request for review.', 'BUS. TAX MAPPING SECTION', '2025-01-18', 'Mr. Bautista', 'FILE', 'Archived.', '2025-12-11 03:02:39'),
(16, 5, '16', '2025-01-19', 'PNP Cebu City', 'Coordination for certification.', 'ADMIN DIVISION', '2025-01-19', 'Ms. Aquino', 'COMMENT/SAND/OR', 'Submitted to chief.', '2025-12-11 03:02:39'),
(17, 5, '17', '2025-01-20', 'CHED Region 7', 'Request for validation.', 'LICENSE DIVISION', '2025-01-20', 'Atty. Franco', 'APPROPRIATE ACTION', 'Processing.', '2025-12-11 03:02:39'),
(18, 5, '18', '2025-01-21', 'Lapu-Lapu City Hall', 'Support letter for project.', 'GRACE ATTY. TERENCE', '2025-01-21', 'Ms. Santos', 'TAKE UP WITH ME', 'For review.', '2025-12-11 03:02:39'),
(19, 5, '19', '2025-01-22', 'Barangay Cotcot', 'Clarification on tax dues.', 'LANDTAX DIVISION', '2025-01-22', 'Mr. Perez', 'APPROPRIATE ACTION', 'Forwarded.', '2025-12-11 03:02:39'),
(20, 5, '20', '2025-01-23', 'Cebu Water District', 'Follow-up on unpaid accounts.', 'CASH DIVISION', '2025-01-23', 'Ms. Gomez', 'U-R-G-E-N-T', 'Settled.', '2025-12-11 03:02:39');

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
  `check_no` varchar(50) DEFAULT NULL,
  `transmittal_id` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `control_no`, `payee`, `description`, `amount`, `date_in`, `date_out`, `fund_type`, `bank_channel`, `check_date`, `status`, `check_no`, `transmittal_id`, `created_at`) VALUES
(82, 5, '1', 'ABC Supplies', 'Office supplies purchase', 1500.00, '2025-01-02 09:25:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(83, 5, '2', 'XYZ Trading', 'Printer ink cartridges', 3200.00, '2025-01-03 10:12:00', NULL, 'General', NULL, NULL, NULL, NULL, '59405', '2025-12-11 03:57:28'),
(84, 5, '3', 'Juan Dela Cruz', 'Honorarium payment', 5000.00, '2025-01-04 11:45:00', NULL, 'Special Fund', NULL, NULL, NULL, NULL, '59405', '2025-12-11 03:57:28'),
(85, 5, '4', 'Mandaue Electric', 'Electric bill payment', 8400.00, '2025-01-05 08:55:00', NULL, 'General', NULL, NULL, NULL, NULL, '59405', '2025-12-11 03:57:28'),
(86, 5, '5', 'City Water Dept.', 'Water bill payment', 2300.00, '2025-01-06 09:35:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(87, 5, '6', 'Smart Communications', 'Internet payment', 2800.00, '2025-01-07 14:15:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(88, 5, '7', 'Globe Telecom', 'Mobile load subscription', 1500.00, '2025-01-08 13:40:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(89, 5, '8', 'ACLC Printing', 'Document printing services', 4200.00, '2025-01-09 15:22:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(90, 5, '9', 'Office Depot', 'Office chairs purchase', 12000.00, '2025-01-10 08:18:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(91, 5, '10', 'Tech Builders', 'Laptop repair service', 3500.00, '2025-01-11 10:05:00', NULL, 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(92, 5, '11', 'Green Supply Co.', 'Indoor plants for office', 2600.00, '2025-01-12 13:05:00', '2025-12-11 00:40:44', 'Special Fund', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(93, 5, '12', 'Mandaue Catering', 'Event catering service', 7500.00, '2025-01-13 09:48:00', '2025-12-11 00:40:41', 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(94, 5, '13', 'City Courier', 'Courier delivery service', 900.00, '2025-01-14 10:14:00', '2025-12-11 00:40:36', 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(95, 5, '14', 'Office Warehouse', 'Paper reams purchase', 1800.00, '2025-01-15 11:35:00', '2025-12-11 00:40:32', 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(96, 5, '15', 'I.T Services Co.', 'Software license renewal', 12500.00, '2025-01-16 14:55:00', '2025-12-11 00:40:29', 'Special Fund', NULL, NULL, NULL, NULL, '47618', '2025-12-11 03:57:28'),
(97, 5, '16', 'ACLC Rentals', 'Tent and chairs rental', 6500.00, '2025-01-17 15:33:00', '2025-12-11 00:40:26', 'General', NULL, NULL, NULL, NULL, '47618', '2025-12-11 03:57:28'),
(98, 5, '17', 'Office Fixers', 'Aircon maintenance', 4200.00, '2025-01-18 08:22:00', '2025-12-11 00:40:23', 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(99, 5, '18', 'Tech Parts', 'Computer parts purchase', 8500.00, '2025-01-19 09:10:00', '2025-12-11 00:40:20', 'General', NULL, NULL, NULL, NULL, '95250', '2025-12-11 03:57:28'),
(100, 5, '19', 'Mandaue Transport', 'Vehicle fuel assistance', 4500.00, '2025-01-20 10:28:00', '2025-12-11 00:40:16', 'General', NULL, NULL, NULL, NULL, NULL, '2025-12-11 03:57:28'),
(101, 5, '20', 'Security Guard Co.', 'Security service payment', 19000.00, '2025-01-21 11:40:00', '2025-12-11 00:40:01', 'Special Fund', 'Land Bank', '2025-12-11 00:00:00', 'Check Out', '31528139', '59540', '2025-12-11 03:57:28');

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
(56, 5, 'mcky', 'encoder', '2025-12-04 02:40:31'),
(57, 5, 'mcky', 'encoder', '2025-12-07 05:09:28'),
(58, 1, 'admin', 'admin', '2025-12-07 07:17:59'),
(59, 1, 'admin', 'admin', '2025-12-07 07:58:46'),
(60, 1, 'admin', 'admin', '2025-12-07 08:00:40'),
(62, 1, 'admin', 'admin', '2025-12-08 03:33:56'),
(63, 5, 'mcky', 'encoder', '2025-12-08 03:34:37'),
(64, 5, 'mcky', 'encoder', '2025-12-08 03:36:17'),
(65, 5, 'mcky', 'encoder', '2025-12-08 03:36:37'),
(66, 5, 'mcky', 'encoder', '2025-12-08 05:41:14'),
(67, 5, 'mcky', 'encoder', '2025-12-08 13:10:27'),
(68, 5, 'mcky', 'encoder', '2025-12-09 04:55:07'),
(69, 5, 'mcky', 'encoder', '2025-12-10 15:11:27'),
(70, 5, 'mcky', 'encoder', '2025-12-11 02:39:53'),
(71, 5, 'mcky', 'encoder', '2025-12-11 02:48:03'),
(72, 1, 'admin', 'admin', '2025-12-11 03:50:25'),
(73, 5, 'mcky', 'encoder', '2025-12-11 04:17:09');

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

INSERT INTO `users` (`id`, `first_name`, `middle_initial`, `last_name`, `username`, `email`, `password`, `role`, `status`, `permissions`, `created_at`) VALUES
(1, 'Admin', 'A.', 'Administrator', 'admin', 'user1@example.com', 'admin123', 'admin', '', NULL, '2025-10-29 03:21:47'),
(3, 'Example', 'M.', 'Sample', 'encoder', 'user3@example.com', 'encoder123', 'encoder', 'Inactive', '[]', '2025-10-29 03:35:55'),
(5, 'Neil', 'M.', 'Alferez', 'mcky', 'user5@example.com', 'neil123', 'encoder', '', '[\"voucher_records\",\"check_records\",\"communications_records\",\"activity_records\",\"certificate_records\"]', '2025-11-11 06:26:55'),
(6, 'Marc', 'M.', 'Epe', 'marcky', 'marc@gmail.com', 'marc123', 'encoder', 'Active', NULL, '2025-11-24 03:52:03'),
(7, 'Johnna', 'M', 'Quevedo', 'johnna', 'johnna@gmail.com', 'johnna123', 'encoder', 'Active', NULL, '2025-11-24 11:05:04'),
(8, 'Diane', 'M', 'Alferez', 'diane', 'diane@gmail.com', 'diane123', 'encoder', 'Active', '[\"voucher_records\",\"check_records\",\"communications_records\",\"activity_records\",\"certificate_records\"]', '2025-11-24 11:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `full_name`, `action`, `module`, `reference_id`, `reference_no`, `description`, `created_at`) VALUES
(34, 5, 'Neil M. Alferez', 'Updated Check Record', 'Check Document Records', 101, '31528139', 'Check record updated, Document ID', '2025-12-11 00:31:18'),
(35, 5, 'Neil M. Alferez', 'Checked Out Document', 'Check Document Records', 101, '20', 'Document marked as checked out, Check Num', '2025-12-11 00:31:22'),
(36, 5, 'Neil M. Alferez', 'Checked Out Document', 'Check Document Records', 101, '20', 'Document marked as checked out, Check Num: ', '2025-12-11 00:39:03'),
(37, 5, 'Neil M. Alferez', 'Checked Out Document', 'Check Document Records', 101, '20', 'Document marked as checked out, Check Num', '2025-12-11 00:40:01'),
(38, 5, 'Neil M. Alferez', 'Updated Out Form Details', 'Communication Records', 53, '4', 'Out form details updated, Communication ID: ', '2025-12-11 01:16:41'),
(39, 5, 'Neil M. Alferez', 'Added Certificate Record', 'Certificate Records', 0, '16', 'New Certificate record added, Control No: 16.', '2025-12-11 01:25:41');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `archived_users`
--
ALTER TABLE `archived_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificate_records`
--
ALTER TABLE `certificate_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

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
