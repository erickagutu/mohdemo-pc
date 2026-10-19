-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 19, 2026 at 07:41 PM
-- Server version: 10.11.19-MariaDB-cll-lve
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chasonet_mohdemo`
--

-- --------------------------------------------------------

--
-- Table structure for table `action_points`
--

CREATE TABLE `action_points` (
  `id` int(11) NOT NULL,
  `ref_no` varchar(50) NOT NULL,
  `action_date` date NOT NULL,
  `meeting_title` varchar(255) DEFAULT NULL,
  `issue` text NOT NULL,
  `person_responsible` varchar(150) NOT NULL,
  `timeline` varchar(100) DEFAULT NULL,
  `status` enum('ON TRACK','NOT STARTED','DELAYED','STALLED','COMPLETED','CANCELLED') DEFAULT 'ON TRACK',
  `action` text NOT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `action_points`
--

INSERT INTO `action_points` (`id`, `ref_no`, `action_date`, `meeting_title`, `issue`, `person_responsible`, `timeline`, `status`, `action`, `resolution_notes`, `created_at`) VALUES
(1, 'NASCOP-APT-202602', '2026-01-25', 'NASCOP Quarterly Review Meeting', 'Viral load suppression at 94%, below 95% target in high-burden counties', 'NASCOP Program Manager', '2026-03-31', 'ON TRACK', 'Implement targeted adherence counseling and community support programs', 'Scale up viral load sample courier networks in ASAL counties.', '2026-10-07 04:53:24'),
(2, 'APT-20260120-327', '2026-01-20', 'Awareness Campaign Review', 'Create awareness on HIV testing and self-test kit distribution', 'Dan Mugo', '2026-02-28', 'NOT STARTED', 'Finalize mass media spot campaign and social mobilization tools', 'Pending procurement approval for printing materials.', '2026-10-07 04:53:24'),
(3, 'APT-010', '2025-03-05', 'Risk Assessment Workshop', 'Address identified cyber security vulnerabilities in Taifa Care server', 'Jennifer Taylor', '10 days', 'DELAYED', 'Security patch deployment and penetration re-test', 'Vulnerability mitigated in staging environment; production scheduled for weekend maintenance.', '2026-10-07 04:53:24'),
(4, 'APT-009', '2025-03-01', 'Financial Audit Meeting', 'Review quarterly expense reports and reconcile exchequer releases', 'James Miller', '2 days', 'COMPLETED', 'Report review and submit documentation to National Treasury', 'Reconciliation approved by Chief Finance Officer on March 3.', '2026-10-07 04:53:24'),
(5, 'APT-008', '2025-02-25', 'Strategic Planning Session', 'Implement new workflow processes for medical equipment service approval', 'Maria Garcia', '3 weeks', 'NOT STARTED', 'Process documentation and Directorate validation workshop', 'Awaiting PS approval of the revised circular.', '2026-10-07 04:53:24'),
(6, 'APT-007', '2025-02-20', 'Quality Assurance Review', 'Update security protocols and access controls at KEMSA Embakasi store', 'David Wilson', '1 week', 'ON TRACK', 'Documentation update and physical security audit', 'Biometric turnstiles inspected and operational.', '2026-10-07 04:53:24'),
(7, 'APT-006', '2025-02-15', 'Client Progress Meeting', 'Deliver Phase 1 prototype of Blood Transfusion Damu-KE App', 'Lisa Wang', '5 days', 'DELAYED', 'Prototype development and user acceptance testing', 'Delayed due to API integration with national citizen registry e-Citizen.', '2026-10-07 04:53:24'),
(8, 'APT-005', '2025-02-10', 'Technical Review Board', 'Resolve server compatibility issues on LINAC radiation treatment stations', 'Robert Brown', '1 week', 'STALLED', 'Vendor coordination with Elekta engineers', 'Hardware part import delayed at customs in Mombasa.', '2026-10-07 04:53:24'),
(9, 'APT-004', '2025-02-01', 'Stakeholder Consultation', 'Market research initiative for local medical gloves production', 'Emily Davis', '2 weeks', 'CANCELLED', 'Data collection replaced by UNIDO joint industrial study', 'Merged into UNIDO local manufacturing roadmap.', '2026-10-07 04:53:24'),
(10, 'APT-003', '2025-01-25', 'Team Progress Update', 'Complete user acceptance testing for KNH Burns ICU monitors', 'Mike Chen', '3 days', 'COMPLETED', 'Testing Phase completed with clinical staff sign-off', 'Tested by Head of Anesthesia and Certified Fit for Use.', '2026-10-07 04:53:24'),
(11, 'APT-002', '2025-01-20', 'Budget Review Session', 'Secure vendor quotations for equipment at Mathari hospital modern wing', 'Sarah Johnson', '1 week', 'ON TRACK', 'Vendor coordination and market price survey', 'Three compliant quotations received and forwarded to evaluation committee.', '2026-10-07 04:53:24'),
(12, 'APT-001', '2025-01-15', 'SAGAs CEO Meeting with PS-SDMS', 'Pediatric Oncology Centre at MTRH: secure dedicated funding allocation', 'PS-SDMS, CEO MTRH', '4 weeks', 'STALLED', 'Treasury engagement for supplementary budget line item', 'Meeting with Principal Secretary National Treasury pending confirmation.', '2026-10-07 04:53:24'),
(13, '1', '2025-01-01', 'KNH Executive Inspection', 'KNH bed capacity and patient congestion in emergency surgical admissions', 'Patrick Mwamburi', '2 weeks', 'NOT STARTED', 'Requisition raised for 50 additional overflow recovery beds', 'Awaiting delivery of approved beds from approved framework contract.', '2026-10-07 04:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_name` varchar(150) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT '127.0.0.1',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_name`, `user_email`, `action`, `module`, `record_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-14 11:59:25'),
(2, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-14 13:03:07'),
(3, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.95.108', '2026-10-14 14:09:52'),
(4, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-14 19:49:44'),
(5, 'System Administrator', 'admin@health.go.ke', 'LOGOUT', 'Auth', 1, 'User logged out', '102.203.101.253', '2026-10-14 19:50:19'),
(6, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-14 19:50:23'),
(7, 'System Administrator', 'admin@health.go.ke', 'LOGOUT', 'Auth', 1, 'User logged out', '102.203.101.253', '2026-10-14 20:30:34'),
(8, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-14 20:36:30'),
(9, 'System Administrator', 'admin@health.go.ke', 'LOGOUT', 'Auth', 1, 'User logged out', '102.203.101.253', '2026-10-14 21:23:00'),
(10, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-14 21:23:05'),
(11, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-15 14:33:48'),
(12, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-15 18:36:54'),
(13, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.95.108', '2026-10-15 22:30:57'),
(14, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.95.108', '2026-10-15 23:03:45'),
(15, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 06:12:36'),
(16, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 07:50:34'),
(17, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 09:36:40'),
(18, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 10:35:28'),
(19, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.60.144.83', '2026-10-16 13:40:24'),
(20, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 14:28:49'),
(21, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.60.144.83', '2026-10-16 15:49:25'),
(22, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.60.144.83', '2026-10-16 16:34:27'),
(23, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 16:57:46'),
(24, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.60.144.83', '2026-10-16 18:59:54'),
(25, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-16 19:00:56'),
(26, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.60.144.83', '2026-10-16 19:27:04'),
(27, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.92.20', '2026-10-17 07:25:46'),
(28, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-17 07:37:16'),
(29, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-17 07:42:35'),
(30, 'System Administrator', 'admin@health.go.ke', 'LOGOUT', 'Auth', 1, 'User logged out', '102.203.101.253', '2026-10-17 09:53:30'),
(31, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.92.20', '2026-10-17 10:08:08'),
(32, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.92.20', '2026-10-17 11:17:59'),
(33, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '196.216.92.20', '2026-10-17 11:18:12'),
(34, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-18 07:38:30'),
(35, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-18 08:01:45'),
(36, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-18 08:43:00'),
(37, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.203.101.253', '2026-10-18 08:58:16'),
(38, 'System Administrator', 'admin@health.go.ke', 'CREATE', 'Activities & Events', 5, 'Created activity: KNH Inspection', '102.212.237.135', '2026-10-18 19:06:31'),
(39, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.173.173.193', '2026-10-18 19:50:01'),
(40, 'System Administrator', 'admin@health.go.ke', 'EXPORT', 'action_points', NULL, 'Exported action points as PDF', '41.173.173.193', '2026-10-18 20:00:32'),
(41, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '41.173.173.193', '2026-10-19 02:46:05'),
(42, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.212.236.152', '2026-10-19 07:59:29'),
(43, 'System Administrator', 'admin@health.go.ke', 'CREATE', 'Performance Contract', 1, 'Uploaded report PC_Summary.xlsx', '102.212.236.152', '2026-10-19 08:29:02'),
(44, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '105.161.88.146', '2026-10-19 09:18:45'),
(45, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.212.236.152', '2026-10-19 10:44:05'),
(46, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '105.161.134.164', '2026-10-19 13:33:15'),
(47, 'System Administrator', 'admin@health.go.ke', 'LOGIN', 'Auth', 1, 'User logged into PP-SDOMS from cPanel web portal', '102.212.236.152', '2026-10-19 13:37:57');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_units`
--

CREATE TABLE `delivery_units` (
  `id` varchar(50) NOT NULL,
  `sub_programme_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lead_person` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diary_events`
--

CREATE TABLE `diary_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` varchar(50) DEFAULT 'Event',
  `event_date` date NOT NULL,
  `event_time` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `host` varchar(150) DEFAULT NULL,
  `attendees` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'UPCOMING',
  `priority` varchar(30) DEFAULT 'Medium',
  `project_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diary_events`
--

INSERT INTO `diary_events` (`id`, `title`, `description`, `event_type`, `event_date`, `event_time`, `location`, `host`, `attendees`, `status`, `priority`, `project_id`, `created_at`) VALUES
(1, 'System Status Report', 'Technical requirements verification.', 'Meeting', '2026-10-19', '09:00', 'Afya House', 'Operations Manager', 'Collins Mwangi, Erick Agutu', 'UPCOMING', 'High', 31, '2026-10-17 08:55:56'),
(2, 'Auditor Consultation', 'Audit Report', 'Meeting', '2026-10-19', '09:00', 'Afya House', 'MOH Operations Manager', 'Collins Mwangi, Erick Agutu', 'UPCOMING', 'Medium', 27, '2026-10-17 08:59:56'),
(3, 'Status Update  on Theatre Refurbishment', 'Update on  renovation & construction progress', 'Task', '2026-10-19', '15:30', 'Afya House', 'Morris', 'Collins Mwangi, Erick Agutu', 'UPCOMING', 'High', 19, '2026-10-17 09:02:56'),
(4, 'System Deployment', 'ERP System Deployment', 'Deadline', '2026-10-19', '09:00', NULL, 'MOH Director', 'CS Health, Collins Mwangi', 'UPCOMING', 'High', 21, '2026-10-17 09:06:23'),
(5, 'KNH Inspection', 'Inspection', 'Field Visit', '2026-10-21', '10:07:00', 'Nairobi County', 'Principal Secretary', 'Collins', 'UPCOMING', 'Medium', NULL, '2026-10-18 19:06:31');

-- --------------------------------------------------------

--
-- Table structure for table `directorates`
--

CREATE TABLE `directorates` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(20) NOT NULL,
  `head` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `directorates`
--

INSERT INTO `directorates` (`id`, `name`, `code`, `head`, `description`, `created_at`) VALUES
(1, 'Directorate for Curative Services', 'CS', 'Dr. Peter Mwangi', 'Curative health and tertiary care services', '2026-10-15 16:03:04'),
(2, 'Directorate for Family Health', 'FH', 'Dr. Jane Kimani', 'Reproductive, maternal, newborn, child and adolescent health', '2026-10-15 16:03:04'),
(3, 'Directorate for Health Products and Technologies', 'HPT', 'Dr. Fredrick Ochieng', 'Pharmaceuticals, medical supplies and cold-chain supply chain', '2026-10-15 16:03:04'),
(4, 'Directorate for Policy, Digital Health and Innovations', 'PDHI', 'Eng. Kiprono Koech', 'E-Health, Digital Health Superhighway, m-Health, and data platforms', '2026-10-15 16:03:04'),
(5, 'Directorate for Kenya National Blood Tissues & Transfusion Services', 'KNBTTS', 'Dr. Angela Waweru', 'Blood safety, DAMU-KE, and human organ transplants', '2026-10-15 16:03:04'),
(6, 'Public Health & Sanitation Directorate', 'PHS', NULL, NULL, '2026-10-16 17:24:45'),
(7, 'Primary Healthcare Directorate', 'PHC', NULL, NULL, '2026-10-16 17:24:45'),
(8, 'Health Standards, Regulation & Quality Assurance Directorate', 'HSRQA', NULL, NULL, '2026-10-16 17:24:45'),
(9, 'Health Sector Coordination & Research Directorate', 'HSCR', NULL, NULL, '2026-10-16 17:24:45');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` int(11) NOT NULL,
  `directorate_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`id`, `directorate_id`, `name`, `created_at`) VALUES
(1, 1, 'Nursing Services Division', '2026-10-16 17:24:45'),
(2, 1, 'Healthy Aging, Palliative Care & Older Persons', '2026-10-16 17:24:45'),
(3, 1, 'Oral Health, Eye & Hearing Care Division', '2026-10-16 17:24:45'),
(4, 1, 'Pathology & Forensic Services', '2026-10-16 17:24:45'),
(5, 1, 'National Health Referral & Emergency Services', '2026-10-16 17:24:45'),
(6, 1, 'Clinical Services Division', '2026-10-16 17:24:45'),
(7, 2, 'Mental Health Division', '2026-10-16 17:24:45'),
(8, 2, 'National Vaccination Services Division', '2026-10-16 17:24:45'),
(9, 2, 'Cancer & Non-Communicable Diseases Management Division', '2026-10-16 17:24:45'),
(10, 2, 'HIV/STI Prevention & Control Division', '2026-10-16 17:24:45'),
(11, 2, 'Reproductive, Maternal, Neonatal, Child & Adolescent Health Division', '2026-10-16 17:24:45'),
(12, 3, 'Quality Assurance & Market Shaping Division', '2026-10-16 17:24:45'),
(13, 3, 'Traditional & Alternative Medicine Division', '2026-10-16 17:24:45'),
(14, 3, 'HPT Management & Therapeutics Division', '2026-10-16 17:24:45'),
(15, 9, 'Health Sector Research and Development Division', '2026-10-16 17:24:45'),
(16, 9, 'International Health Relations Division', '2026-10-16 17:24:45'),
(17, 9, 'Health Sector Coordination & Intergovernmental Relations Division', '2026-10-16 17:24:45'),
(18, 8, 'Health Sector Monitoring & Quality Assurance Division', '2026-10-16 17:24:45'),
(19, 8, 'Human Resource for Health & Professional Standards Division', '2026-10-16 17:24:45'),
(20, 8, 'Health Standards & Compliance Division', '2026-10-16 17:24:45'),
(21, 8, 'Health Policy & Regulations Division', '2026-10-16 17:24:45'),
(22, 4, 'Policy, Health Research & Relations Division', '2026-10-16 17:24:45'),
(23, 4, 'Digital Health, Informatics & Innovations Division', '2026-10-16 17:24:45'),
(24, 4, 'Health Financing Division', '2026-10-16 17:24:45'),
(25, 7, 'Nutrition & Dietetics Division', '2026-10-16 17:24:45'),
(26, 7, 'School Health Division', '2026-10-16 17:24:45'),
(27, 7, 'Primary Care Networks Division', '2026-10-16 17:24:45'),
(28, 7, 'Community Health Division', '2026-10-16 17:24:45'),
(29, 7, 'Health & Wellness Division', '2026-10-16 17:24:45'),
(30, 7, 'Health Promotion & Education Management Division', '2026-10-16 17:24:45'),
(31, 7, 'TB & Other Lung Diseases Division', '2026-10-16 17:24:45'),
(32, 7, 'Drug Control & Substance Abuse Division', '2026-10-16 17:24:45'),
(33, 7, 'Malaria Control & Management Division', '2026-10-16 17:24:45'),
(34, 6, 'Port Health Division', '2026-10-16 17:24:45'),
(35, 6, 'National Laboratory Services Division', '2026-10-16 17:24:45'),
(36, 6, 'Environmental Health & Sanitation Division', '2026-10-16 17:24:45'),
(37, 6, 'National Emergency & Preparedness', '2026-10-16 17:24:45'),
(38, 6, 'Disease Surveillance Division', '2026-10-16 17:24:45');

-- --------------------------------------------------------

--
-- Table structure for table `flagships`
--

CREATE TABLE `flagships` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `lead_directorate` varchar(150) NOT NULL,
  `progress_pct` decimal(5,2) DEFAULT 0.00,
  `risk_level` enum('Low','Medium','High','Severe') DEFAULT 'Low',
  `priority_tier` enum('Critical','High','Medium','Low') DEFAULT 'Medium',
  `allocated_budget` decimal(15,2) DEFAULT 0.00,
  `utilized_budget` decimal(15,2) DEFAULT 0.00,
  `lead_officer` varchar(150) DEFAULT NULL,
  `team_name` varchar(150) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'IN PROGRESS',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `flagships`
--

INSERT INTO `flagships` (`id`, `title`, `description`, `lead_directorate`, `progress_pct`, `risk_level`, `priority_tier`, `allocated_budget`, `utilized_budget`, `lead_officer`, `team_name`, `start_date`, `target_date`, `status`, `created_at`) VALUES
(1, 'Universal Health Coverage (UHC) Rollout', 'Nationwide implementation of the digital health superhighway, community health promoter digitization and Primary Care Networks.', 'Directorate for Policy, Digital Health and Innovations', 45.00, 'Medium', 'Critical', 45000000000.00, 18500000000.00, 'Dr. Timothy Wafula', 'Team UHC Delivery', '2023-07-01', '2026-12-31', 'IN PROGRESS', '2026-10-07 04:53:24'),
(2, 'Establishment of Regional Cancer Centres', 'Operationalizing comprehensive oncology diagnostic, radiotherapy and chemotherapy hubs in Kisii, Meru, Garissa and Nyeri.', 'National Cancer Institute Kenya (NCIK)', 25.00, 'High', 'Critical', 8000000000.00, 2140000000.00, 'Dr. Rose Njeru', 'Team Oncology', '2021-01-01', '2027-06-30', 'IN PROGRESS', '2026-10-07 04:53:24'),
(3, 'HIV Epidemic Control through NASCOP', 'Achieve 95-95-95 UNAIDS targets through universal access to ART, PMTCT elimination, and transitioning to local sustainable financing.', 'National Syndemic Diseases Control Council (NSDCC)', 94.00, 'Medium', 'Critical', 37641000000.00, 31500000000.00, 'Dr. Ruth Masha', 'NASCOP Steering Unit', '2021-07-01', '2026-12-31', 'IN PROGRESS', '2026-10-07 04:53:24'),
(4, 'Expansion of KNH Burns and Specialized Surgical Unit', 'Construction, equipping and staffing of modern 150-bed specialized Burns and Pediatric Care Centre with dedicated ICUs and surgical suites.', 'Kenyatta National Hospital (KNH)', 43.00, 'Low', 'High', 5459000000.00, 2350000000.00, 'Dr. Patrick Mwamburi', 'KNH Project Team', '2018-03-03', '2026-06-30', 'IN PROGRESS', '2026-10-07 04:53:24'),
(5, 'National Health Products Security & Local Manufacturing', 'Strategic buffer stock enhancement, KEMSA modernization, and establishing local human vaccine manufacturing via BIOVAX Institute.', 'Directorate for Health Products and Technologies', 35.00, 'High', 'High', 16400000000.00, 4800000000.00, 'Dr. Fredrick Ochieng', 'BIOVAX Taskforce', '2022-01-01', '2028-06-30', 'IN PROGRESS', '2026-10-07 04:53:24');

-- --------------------------------------------------------

--
-- Table structure for table `kpis_and_sub_kpis`
--

CREATE TABLE `kpis_and_sub_kpis` (
  `id` int(11) NOT NULL,
  `serial_no` varchar(20) DEFAULT NULL,
  `performance_criteria` text DEFAULT NULL,
  `unit_of_measure` varchar(50) DEFAULT NULL,
  `weight_percent` decimal(5,2) DEFAULT NULL,
  `target` varchar(50) DEFAULT NULL,
  `achievement_before_review` varchar(50) DEFAULT NULL,
  `achievement_after_review` varchar(50) DEFAULT NULL,
  `weight_achieved_percent` decimal(5,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `reports` varchar(255) DEFAULT NULL,
  `actions` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kpis_and_sub_kpis`
--

INSERT INTO `kpis_and_sub_kpis` (`id`, `serial_no`, `performance_criteria`, `unit_of_measure`, `weight_percent`, `target`, `achievement_before_review`, `achievement_after_review`, `weight_achieved_percent`, `status`, `reports`, `actions`) VALUES
(1, 'A', 'Financial Stewardship', NULL, 0.00, '10', NULL, NULL, 9.53, 'Reviewed', NULL, NULL),
(2, 'A1', 'Absorption of Allocated Funds ( GoK )', '%', 2.00, '95', '90', '92', 1.94, 'Reviewed', NULL, NULL),
(3, 'A2', 'Appropriation -In-Aid', 'Kshs', 2.00, '500M', '480M', '490M', 1.96, 'Reviewed', NULL, NULL),
(4, 'A3', 'Absorption of Externally Mobilized Funds', '%', 3.00, '90', '85', '88', 2.93, 'Reviewed', NULL, NULL),
(5, 'A4', 'Pending Bills Ratio', '%', 3.00, '10', '12', '11', 2.70, 'Reviewed', NULL, NULL),
(6, 'B', 'Service Delivery', NULL, NULL, NULL, NULL, NULL, 14.39, 'Reviewed', NULL, NULL),
(7, 'B1', 'Implementation of Citizens\' Service Delivery Charter', '%', 4.00, '100', '95', '98', 3.92, 'Reviewed', NULL, NULL),
(8, 'B2', 'Digitalization of Government Services', '%', 7.00, '80', '70', '75', 6.56, 'Reviewed', NULL, NULL),
(9, 'B3', 'Resolution of Public Complaints', '%', 4.00, '90', '85', '88', 3.91, 'Reviewed', NULL, NULL),
(10, 'C', 'Core Mandate', NULL, NULL, NULL, NULL, NULL, 62.73, 'Reviewed', NULL, NULL),
(11, 'C9_2', 'Universal Health Coverage in Counties Rolled Out', '%', 6.00, '100', '90', '95', 5.70, 'Reviewed', NULL, NULL),
(12, 'C9_3', 'Access to Health Products and Technologies Enhanced', '%', 2.00, '85', '80', '82', 1.93, 'Reviewed', NULL, NULL),
(13, 'C9_4', 'Strategic Plan for The Local Manufacture of HPTs Developed', 'No', 3.00, 'Yes', 'No', 'Yes', 3.00, 'Reviewed', NULL, NULL),
(14, 'C9_5', 'Kenya Health Sector Strategic Plan Developed', 'No', 3.00, 'Yes', 'No', 'Yes', 3.00, 'Reviewed', NULL, NULL),
(15, 'C9_6', 'Proportion of Deliveries Performed by Skilled Birth Attendants Increased', '%', 3.00, '75', '70', '72', 2.88, 'Reviewed', NULL, NULL),
(16, 'C9_7', 'Mother to Child Transmission of HIV Rate Halted and Reversed', '%', 4.00, '5', '6', '5', 4.00, 'Reviewed', NULL, NULL),
(17, 'C9_8', 'Number of HIV Patients on Anti-Retroviral Therapy Increased', 'No', 3.00, 'Yes', 'Yes', 'Yes', 3.00, 'Reviewed', NULL, NULL),
(18, 'C9_9', 'STEPS Survey for NCD Risk Factors Conducted', '%', 4.00, '100', '90', '95', 3.80, 'Reviewed', NULL, NULL),
(19, 'C9_10', 'Number of Blood and Blood Components for Transfusion Availed', 'No', 3.00, '50000', '45000', '48000', 2.88, 'Reviewed', NULL, NULL),
(20, 'C9_11', 'Percentage of Health Facilities Offering Adolescent and Youth Friendly Services Increased', '%', 4.00, '70', '65', '68', 3.89, 'Reviewed', NULL, NULL),
(21, 'C9_13', 'Total Confirmed Malaria Cases ( Per 1000 Population) Reduced', 'Index', 4.00, '50', '55', '52', 3.84, 'Reviewed', NULL, NULL),
(22, 'C9_14', 'Number of People Diagnosed with Drug Susceptible TB (All forms) and Put on Treatment Increased', 'No', 3.00, '80000', '75000', '78000', 2.93, 'Reviewed', NULL, NULL),
(23, 'C9_15', 'Number of Patients with Drug Resistant Tuberculosis Started on Treatment Increased', 'No', 3.00, '2000', '1800', '1900', 2.85, 'Reviewed', NULL, NULL),
(24, 'C9_16', 'Percentage of Eligible School Age Children Dewormed Annually Increased', '%', 3.00, '85', '80', '83', 2.93, 'Reviewed', NULL, NULL),
(25, 'C9_17', 'Regional Cancer Centre at Kisii Level 5 Hospital Constructed', '%', 3.00, '100', '70', '80', 2.40, 'Reviewed', NULL, NULL),
(26, 'C9_18', 'Science, Technology and Innovation Mainstreaming', '%', 2.00, '90', '85', '88', 1.96, 'Reviewed', NULL, NULL),
(27, 'C9_19', 'Productivity Improvement', '%', 3.00, '75', '70', '72', 2.88, 'Reviewed', NULL, NULL),
(28, 'C9_20', 'Typhoid Conjugate Vaccine to Children Aged 9 Months Introduced', 'No', 2.00, 'Yes', 'No', 'Yes', 2.00, 'Reviewed', NULL, NULL),
(29, 'C9_23', 'Internship Opportunities to Medical Trainees Availed', 'No', 2.00, '500', '450', '480', 1.92, 'Reviewed', NULL, NULL),
(30, 'C9_24', 'New Structure for the Acquisition of Medical Equipment Designed', 'No', 3.00, 'Yes', 'No', 'Yes', 3.00, 'Reviewed', NULL, NULL),
(31, 'C9_25', 'Project Completion Rate', '%', 2.00, '95', '90', '92', 1.94, 'Reviewed', NULL, NULL),
(32, 'D', 'Implementation of Presidential Directives', NULL, NULL, NULL, NULL, NULL, 1.96, 'Reviewed', NULL, NULL),
(33, 'D1', 'Implementation of Presidential Directives', '%', 2.00, '100', '95', '98', 1.96, 'Reviewed', NULL, NULL),
(34, 'E', 'Affirmative Action in Procurement', NULL, NULL, NULL, NULL, NULL, 3.83, 'Reviewed', NULL, NULL),
(35, 'E1', 'Access to Government Procurement Opportunities', 'Kshs', 2.00, '200M', '180M', '190M', 1.90, 'Reviewed', NULL, NULL),
(36, 'E2', 'Promotion of Local Content in Procurement', 'Kshs', 2.00, '150M', '140M', '145M', 1.93, 'Reviewed', NULL, NULL),
(37, 'F', 'Cross - Cutting', NULL, NULL, NULL, NULL, NULL, 3.87, 'Reviewed', NULL, NULL),
(38, 'F1', 'Asset Management', '%', 1.00, '90', '85', '88', 0.98, 'Reviewed', NULL, NULL),
(39, 'F2', 'Youth Internships/ Industrial Attachments/ Apprenticeships', 'No', 1.00, '1000', '900', '950', 0.95, 'Reviewed', NULL, NULL),
(40, 'F3', 'Competence Development', '%', 1.00, '85', '80', '82', 0.96, 'Reviewed', NULL, NULL),
(41, 'F4', 'National Values and Principles of Governance', '%', 1.00, '100', '95', '98', 0.98, 'Reviewed', NULL, NULL),
(42, NULL, 'Total Weight Achieved [(Achievement ÷ Target)×Weight]', NULL, NULL, NULL, NULL, NULL, 96.31, 'Reviewed', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `meeting_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `financier` varchar(150) DEFAULT NULL,
  `venue` varchar(150) DEFAULT NULL,
  `attendees` text DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `decisions` text DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_progress`
--

CREATE TABLE `monthly_progress` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `year_month` varchar(7) NOT NULL,
  `kpi_code` varchar(50) NOT NULL,
  `kpi_name` varchar(255) NOT NULL,
  `weight` decimal(5,2) DEFAULT 0.00,
  `progress_pct` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outputs`
--

CREATE TABLE `outputs` (
  `id` varchar(50) NOT NULL,
  `delivery_unit_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `indicator` varchar(255) DEFAULT NULL,
  `target_value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pc_report_uploads`
--

CREATE TABLE `pc_report_uploads` (
  `id` int(11) NOT NULL,
  `serial_no` varchar(20) DEFAULT NULL,
  `financial_year` varchar(9) NOT NULL,
  `quarter` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pc_report_uploads`
--

INSERT INTO `pc_report_uploads` (`id`, `serial_no`, `financial_year`, `quarter`, `title`, `original_name`, `stored_name`, `file_size`, `uploaded_by`, `created_at`) VALUES
(1, NULL, '2025/2026', 1, 'PC_Summary.xlsx', 'PC_Summary.xlsx', '4d0f61b24c943529e35f5252f0bdcec4.xlsx', 14405, 'System Administrator', '2026-10-19 08:29:02');

-- --------------------------------------------------------

--
-- Table structure for table `performance_kpis`
--

CREATE TABLE `performance_kpis` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `kpi_name` varchar(255) NOT NULL,
  `category` varchar(150) NOT NULL,
  `delivery_unit` varchar(150) DEFAULT NULL,
  `parent_kpi_name` varchar(255) DEFAULT NULL,
  `sub_kpi_weight` decimal(5,2) DEFAULT 0.00,
  `target_baseline` varchar(100) DEFAULT NULL,
  `target_2025_26` varchar(100) DEFAULT NULL,
  `target_baseline_2026_27` varchar(100) DEFAULT NULL,
  `target_2027_28` varchar(100) DEFAULT NULL,
  `target_2028_29` varchar(100) DEFAULT NULL,
  `target_2029_30` varchar(100) DEFAULT NULL,
  `actual_2025_26` varchar(100) DEFAULT NULL,
  `current_achievement` decimal(5,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Achieved',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `performance_kpis`
--

INSERT INTO `performance_kpis` (`id`, `code`, `kpi_name`, `category`, `delivery_unit`, `parent_kpi_name`, `sub_kpi_weight`, `target_baseline`, `target_2025_26`, `target_baseline_2026_27`, `target_2027_28`, `target_2028_29`, `target_2029_30`, `actual_2025_26`, `current_achievement`, `status`, `remarks`) VALUES
(1, 'KNH-DEMO-A', 'Financial Stewardship', 'Financial Stewardship', 'Kenyatta National Hospital', 'KNH Strategic KPI Pillars', 10.00, NULL, '100%', '100%', '100%', '100%', '100%', '72%', 72.00, 'In Progress', 'DEMO DATA: illustrative financial stewardship achievement; replace with approved KNH reporting.'),
(2, 'KNH-DEMO-B', 'Service Delivery', 'Service Delivery', 'Kenyatta National Hospital', 'KNH Strategic KPI Pillars', 15.00, NULL, '100%', '100%', '100%', '100%', '100%', '68%', 68.00, 'In Progress', 'DEMO DATA: illustrative service delivery achievement; replace with approved KNH reporting.'),
(3, 'KNH-DEMO-C', 'Core Mandate', 'Core Mandate', 'Kenyatta National Hospital', 'KNH Strategic KPI Pillars', 65.00, NULL, '100%', '100%', '100%', '100%', '100%', '74%', 74.00, 'In Progress', 'DEMO DATA: illustrative core mandate achievement; replace with approved KNH reporting.'),
(4, 'KNH-DEMO-D', 'Implementation of Presidential Directives', 'Implementation of Presidential Directives', 'Kenyatta National Hospital', 'KNH Strategic KPI Pillars', 2.00, NULL, '100%', '100%', '100%', '100%', '100%', '80%', 80.00, 'In Progress', 'DEMO DATA: illustrative directives achievement; replace with approved KNH reporting.'),
(5, 'KNH-DEMO-E', 'Affirmative Action in Procurement', 'Affirmative Action in Procurement', 'Kenyatta National Hospital', 'KNH Strategic KPI Pillars', 4.00, NULL, '100%', '100%', '100%', '100%', '100%', '63%', 63.00, 'In Progress', 'DEMO DATA: illustrative affirmative procurement achievement; replace with approved KNH reporting.'),
(6, 'KNH-DEMO-F', 'Cross-Cutting', 'Cross-Cutting', 'Kenyatta National Hospital', 'KNH Strategic KPI Pillars', 4.00, NULL, '100%', '100%', '100%', '100%', '100%', '70%', 70.00, 'In Progress', 'DEMO DATA: illustrative cross-cutting achievement; replace with approved KNH reporting.');

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `objective` text DEFAULT NULL,
  `allocated_budget` decimal(15,2) DEFAULT 0.00,
  `lead_officer` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `project_code` varchar(50) DEFAULT NULL,
  `project_type` varchar(30) NOT NULL DEFAULT 'Project',
  `programme_title` varchar(255) NOT NULL,
  `sub_programme` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `category` enum('Directorate','Saga','Division') NOT NULL DEFAULT 'Directorate',
  `directorate_name` varchar(150) DEFAULT NULL,
  `saga_name` varchar(150) DEFAULT NULL,
  `division_name` varchar(150) DEFAULT NULL,
  `delivery_unit` varchar(150) DEFAULT NULL,
  `priority_tier` varchar(100) DEFAULT 'Tier 1 - High Priority',
  `color` varchar(20) DEFAULT '#1976d2',
  `flagship_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'IN PROGRESS',
  `progress_status` varchar(50) DEFAULT 'On Track',
  `overall_progress_pct` decimal(5,2) DEFAULT NULL,
  `progress_calculation_mode` enum('kpi_derived','manual') DEFAULT 'kpi_derived',
  `total_budget` decimal(15,2) DEFAULT 0.00,
  `gok_financing` decimal(15,2) DEFAULT NULL,
  `foreign_financing` decimal(15,2) DEFAULT NULL,
  `funds_outstanding` decimal(15,2) DEFAULT NULL,
  `funds_disbursed` decimal(15,2) DEFAULT 0.00,
  `funds_utilized` decimal(15,2) DEFAULT 0.00,
  `milestones_completed` int(11) DEFAULT 0,
  `milestones_total` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_code`, `project_type`, `programme_title`, `sub_programme`, `description`, `remarks`, `category`, `directorate_name`, `saga_name`, `division_name`, `delivery_unit`, `priority_tier`, `color`, `flagship_id`, `start_date`, `end_date`, `status`, `progress_status`, `overall_progress_pct`, `progress_calculation_mode`, `total_budget`, `gok_financing`, `foreign_financing`, `funds_outstanding`, `funds_disbursed`, `funds_utilized`, `milestones_completed`, `milestones_total`, `created_at`) VALUES
(17, '1082100100', 'Project', 'Kenyatta National Hospital', 'KNH Burns and Pediatrics Centre', NULL, NULL, 'Saga', NULL, 'Kenyatta National Hospital', NULL, 'KNH', NULL, '#1976d2', NULL, '2018-03-03', '2027-08-30', 'In Progress', '69.00%', 69.00, 'kpi_derived', 5459000000.00, 3482000000.00, 1977000000.00, 1710000000.00, NULL, 3749000000.00, 0, 0, '2026-10-15 14:32:15'),
(18, '1082101100', 'Project', 'Moi Teaching and Referral Hospital', 'Cancer & Chronic Disease Management Centre – MTRH', NULL, NULL, 'Saga', NULL, 'Moi Teaching and Referral Hospital', NULL, 'MTRH', NULL, '#1976d2', NULL, '2013-01-07', '2028-06-30', 'In Progress', 'Not recorded', NULL, 'kpi_derived', 1843000000.00, 1393000000.00, 450000000.00, 473000000.00, NULL, 1370000000.00, 0, 0, '2026-10-15 14:32:15'),
(19, '1082101400', 'Project', 'Moi Teaching and Referral Hospital', 'Expansion and Equipping of ICU-MTRH', NULL, NULL, 'Saga', NULL, 'Moi Teaching and Referral Hospital', NULL, 'MTRH', NULL, '#1976d2', NULL, '2015-07-01', '2028-12-01', 'In Progress', '59.00%', 59.00, 'kpi_derived', 484000000.00, 439000000.00, 0.00, 200000000.00, NULL, 284000000.00, 0, 0, '2026-10-15 14:32:15'),
(20, '1082101200', 'Project', 'Moi Teaching and Referral Hospital', 'Construction and Equipping Children Hospital- MTRH', NULL, NULL, 'Saga', NULL, 'Moi Teaching and Referral Hospital', NULL, 'MTRH', NULL, '#1976d2', NULL, '2014-01-01', '2028-06-30', 'In Progress', '53.00%', 53.00, 'kpi_derived', 1080000000.00, 830000000.00, 250000000.00, 507000000.00, NULL, 573000000.00, 0, 0, '2026-10-15 14:32:15'),
(21, NULL, 'Project', 'Mathari National Teaching and Referral Hospital', 'Procurement of the Hospital ERP & HMIS System and Associated ICT Infrastructure Project-MNTRH', NULL, NULL, 'Saga', NULL, 'Mathari National Teaching and Referral Hospital', NULL, 'MNTRH', NULL, '#1976d2', NULL, '2027-01-07', '2030-06-30', 'Planning', '0.00%', 0.00, 'kpi_derived', 800000000.00, 800000000.00, 0.00, 800000000.00, NULL, 0.00, 0, 0, '2026-10-15 14:32:15'),
(22, '1082108800', 'Project', 'Moi Teaching and Referral Hospital', 'New 2,000 Bed Multi-Specialty Hospital MTRH.', NULL, NULL, 'Saga', NULL, 'Moi Teaching and Referral Hospital', NULL, 'MTRH', NULL, '#1976d2', NULL, '2025-10-17', '2027-04-17', 'In Progress', '4.00%', 4.00, 'kpi_derived', 44701000000.00, 44701000000.00, 0.00, 42701000000.00, NULL, 2000000000.00, 0, 0, '2026-10-15 14:32:15'),
(23, NULL, 'Project', 'Mathari National Teaching and Referral Hospital', 'Construction of Acute and Intensive Care Ward-MNTRH', NULL, NULL, 'Saga', NULL, 'Mathari National Teaching and Referral Hospital', NULL, 'MNTRH', NULL, '#1976d2', NULL, '2027-01-07', '2030-06-30', 'Planning', '0.00%', 0.00, 'kpi_derived', 300000000.00, 300000000.00, 0.00, 300000000.00, NULL, 0.00, 0, 0, '2026-10-15 14:32:15'),
(24, NULL, 'Project', 'Mathari National Teaching and Referral Hospital', 'Modernization and Refurbishment of the Forensic Psychiatric Unit -MNTRH', NULL, NULL, 'Saga', NULL, 'Mathari National Teaching and Referral Hospital', NULL, 'MNTRH', NULL, '#1976d2', NULL, '2027-01-07', '2030-06-30', 'Planning', '0.00%', 0.00, 'kpi_derived', 700000000.00, 700000000.00, 0.00, 700000000.00, NULL, 0.00, 0, 0, '2026-10-15 14:32:15'),
(25, NULL, 'Project', 'Mathari National Teaching and Referral Hospital', 'Construction of MNTRH Administration Block and Doctors Plaza', NULL, NULL, 'Saga', NULL, 'Mathari National Teaching and Referral Hospital', NULL, 'MNTRH', NULL, '#1976d2', NULL, '2027-01-07', '2028-06-30', 'Planning', '0.00%', 0.00, 'kpi_derived', 450000000.00, 450000000.00, 0.00, 450000000.00, NULL, 0.00, 0, 0, '2026-10-15 14:32:15'),
(26, NULL, 'Project', 'Mathari National Teaching and Referral Hospital', 'Construction of MNTRH Warehouse', NULL, NULL, 'Saga', NULL, 'Mathari National Teaching and Referral Hospital', NULL, 'MNTRH', NULL, '#1976d2', NULL, '2027-01-07', '2028-06-30', 'Planning', '0.00%', 0.00, 'kpi_derived', 150000000.00, 150000000.00, 0.00, 150000000.00, NULL, 0.00, 0, 0, '2026-10-15 14:32:15'),
(27, '1082107703', 'Programme', 'Family Health', 'Special Global Fund HIV Grant NFM4- NASCOP', NULL, NULL, 'Directorate', 'Family Health', NULL, NULL, 'NASCOP', NULL, '#1976d2', NULL, '2024-01-07', '2028-06-30', 'In Progress', '74.00%', 74.00, 'kpi_derived', 3100000000.00, 2500000000.00, 600000000.00, 795000000.00, NULL, 2305000000.00, 0, 0, '2026-10-15 14:32:15'),
(28, '1082104102', 'Programme', 'Family Health', 'Special Global Fund HIV Grant NFM4-NSDCC', NULL, NULL, 'Directorate', 'Family Health', NULL, NULL, 'NSDCC', NULL, '#1976d2', NULL, '2022-07-01', '2028-06-30', 'In Progress', '75.00%', 75.00, 'kpi_derived', 1216000000.00, NULL, 1216000000.00, 304000000.00, NULL, 912000000.00, 0, 0, '2026-10-15 14:32:15'),
(29, '1082104800', 'Programme', 'Family Health', '9TH GoK/ UNFPA 10th County Programmes MOH NSDCC HIV -UNFPA', NULL, NULL, 'Directorate', 'Family Health', NULL, NULL, 'NSDCC', NULL, '#1976d2', NULL, '2027-01-01', '2027-06-30', 'Planning', '34.00%', 34.00, 'kpi_derived', 15000000.00, 0.00, 5000000.00, 10000000.00, NULL, 5000000.00, 0, 0, '2026-10-15 14:32:15'),
(30, '1081107400', 'Programme', 'Family Health', 'Acquisition of space by the National Syndemic Diseases Control Council', NULL, NULL, 'Directorate', 'Family Health', NULL, NULL, 'NSDCC', NULL, '#1976d2', NULL, '2017-08-29', '2028-06-30', 'In Progress', '17.00%', 17.00, 'kpi_derived', 600000000.00, 600000000.00, 0.00, 500000000.00, NULL, 100000000.00, 0, 0, '2026-10-15 14:32:15'),
(31, '1082109200', 'Programme', 'Family Health', 'NASCOP HIV Program.', NULL, NULL, 'Directorate', 'Family Health', NULL, NULL, 'NASCOP', NULL, '#1976d2', NULL, '2026-01-07', '2027-06-30', 'In Progress', '0.00%', 0.00, 'kpi_derived', 201000000.00, 201000000.00, NULL, 200800000.00, NULL, NULL, 0, 0, '2026-10-15 14:32:15');

-- --------------------------------------------------------

--
-- Table structure for table `project_financial_projections`
--

CREATE TABLE `project_financial_projections` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `fiscal_year` varchar(9) NOT NULL,
  `gok_financing` decimal(15,2) DEFAULT NULL,
  `foreign_financing` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_financial_projections`
--

INSERT INTO `project_financial_projections` (`id`, `project_id`, `fiscal_year`, `gok_financing`, `foreign_financing`) VALUES
(1, 17, '2027/28', 1137000000.00, 573000000.00),
(2, 18, '2027/28', 150000000.00, NULL),
(3, 19, '2027/28', 100000000.00, NULL),
(4, 20, '2027/28', 150000000.00, NULL),
(5, 21, '2027/28', 800000000.00, NULL),
(6, 22, '2027/28', 2000000000.00, NULL),
(7, 23, '2027/28', 300000000.00, NULL),
(8, 24, '2027/28', 700000000.00, NULL),
(9, 25, '2027/28', 450000000.00, NULL),
(10, 26, '2027/28', 150000000.00, NULL),
(11, 27, '2027/28', 2500000000.00, 300000000.00),
(12, 28, '2027/28', NULL, 150000000.00),
(13, 29, '2027/28', NULL, 15000000.00),
(14, 30, '2027/28', 200000000.00, NULL),
(15, 31, '2027/28', 200800000.00, NULL),
(16, 17, '2028/29', NULL, NULL),
(17, 18, '2028/29', 100000000.00, NULL),
(18, 19, '2028/29', NULL, NULL),
(19, 20, '2028/29', 100000000.00, NULL),
(20, 21, '2028/29', NULL, NULL),
(21, 22, '2028/29', 2000000000.00, NULL),
(22, 23, '2028/29', NULL, NULL),
(23, 24, '2028/29', NULL, NULL),
(24, 25, '2028/29', NULL, NULL),
(25, 26, '2028/29', NULL, NULL),
(26, 27, '2028/29', NULL, NULL),
(27, 28, '2028/29', NULL, 150000000.00),
(28, 29, '2028/29', NULL, NULL),
(29, 30, '2028/29', NULL, NULL),
(30, 31, '2028/29', NULL, NULL),
(31, 17, '2029/30', NULL, NULL),
(32, 18, '2029/30', 51000000.00, NULL),
(33, 19, '2029/30', NULL, NULL),
(34, 20, '2029/30', 157000000.00, NULL),
(35, 21, '2029/30', NULL, NULL),
(36, 22, '2029/30', 2000000000.00, NULL),
(37, 23, '2029/30', NULL, NULL),
(38, 24, '2029/30', NULL, NULL),
(39, 25, '2029/30', NULL, NULL),
(40, 26, '2029/30', NULL, NULL),
(41, 27, '2029/30', NULL, NULL),
(42, 28, '2029/30', NULL, NULL),
(43, 29, '2029/30', NULL, NULL),
(44, 30, '2029/30', NULL, 40767130000.00),
(45, 31, '2029/30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_key_output_results`
--

CREATE TABLE `project_key_output_results` (
  `id` int(11) NOT NULL,
  `scope_type` enum('SAGA','Delivery Unit','Project') NOT NULL DEFAULT 'SAGA',
  `scope_name` varchar(150) NOT NULL,
  `output_code` varchar(20) NOT NULL,
  `output_title` varchar(255) NOT NULL,
  `kpi_code` varchar(40) NOT NULL,
  `kpi_name` varchar(255) NOT NULL,
  `unit` varchar(40) NOT NULL DEFAULT '',
  `direction` enum('higher','lower') NOT NULL DEFAULT 'higher',
  `fiscal_year` varchar(9) NOT NULL,
  `target_value` decimal(15,2) DEFAULT NULL,
  `actual_value` decimal(15,2) DEFAULT NULL,
  `target_type` enum('Target','Baseline') NOT NULL DEFAULT 'Target',
  `source_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_key_output_results`
--

INSERT INTO `project_key_output_results` (`id`, `scope_type`, `scope_name`, `output_code`, `output_title`, `kpi_code`, `kpi_name`, `unit`, `direction`, `fiscal_year`, `target_value`, `actual_value`, `target_type`, `source_note`, `created_at`, `updated_at`) VALUES
(1, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-01', 'Number of Heart surgeries done', 'surgeries', 'higher', '2025/26', 784.00, 630.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(2, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-01', 'Number of Heart surgeries done', 'surgeries', 'higher', '2026/27', 784.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(3, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-01', 'Number of Heart surgeries done', 'surgeries', 'higher', '2027/28', 815.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(4, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-01', 'Number of Heart surgeries done', 'surgeries', 'higher', '2028/29', 863.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(5, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-01', 'Number of Heart surgeries done', 'surgeries', 'higher', '2029/30', 889.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(6, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-02', 'Number of other cardiothoracic surgeries conducted', 'surgeries', 'higher', '2025/26', 1519.00, 1023.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(7, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-02', 'Number of other cardiothoracic surgeries conducted', 'surgeries', 'higher', '2026/27', 1519.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(8, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-02', 'Number of other cardiothoracic surgeries conducted', 'surgeries', 'higher', '2027/28', 1261.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(9, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-02', 'Number of other cardiothoracic surgeries conducted', 'surgeries', 'higher', '2028/29', 1266.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(10, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-02', 'Number of other cardiothoracic surgeries conducted', 'surgeries', 'higher', '2029/30', 1281.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(11, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-03', 'Number of Kidney Transplants conducted.', 'transplants', 'higher', '2025/26', 24.00, 22.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(12, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-03', 'Number of Kidney Transplants conducted.', 'transplants', 'higher', '2026/27', 24.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(13, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-03', 'Number of Kidney Transplants conducted.', 'transplants', 'higher', '2027/28', 29.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(14, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-03', 'Number of Kidney Transplants conducted.', 'transplants', 'higher', '2028/29', 31.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(15, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-03', 'Number of Kidney Transplants conducted.', 'transplants', 'higher', '2029/30', 32.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(16, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-04', 'Number of minimally invasive surgeries done', 'surgeries', 'higher', '2025/26', 10716.00, 9852.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(17, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-04', 'Number of minimally invasive surgeries done', 'surgeries', 'higher', '2026/27', 10784.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(18, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-04', 'Number of minimally invasive surgeries done', 'surgeries', 'higher', '2027/28', 11539.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(19, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-04', 'Number of minimally invasive surgeries done', 'surgeries', 'higher', '2028/29', 11986.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(20, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-04', 'Number of minimally invasive surgeries done', 'surgeries', 'higher', '2029/30', 12346.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(21, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-05', 'Number of patients undergoing specialized Burns treatment (OBD)', 'patients', 'higher', '2025/26', 649.00, 439.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(22, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-05', 'Number of patients undergoing specialized Burns treatment (OBD)', 'patients', 'higher', '2026/27', 649.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(23, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-05', 'Number of patients undergoing specialized Burns treatment (OBD)', 'patients', 'higher', '2027/28', 595.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(24, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-05', 'Number of patients undergoing specialized Burns treatment (OBD)', 'patients', 'higher', '2028/29', 598.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(25, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-05', 'Number of patients undergoing specialized Burns treatment (OBD)', 'patients', 'higher', '2029/30', 604.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(26, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-06', 'Number of oncology sessions on (Chemotherapy and radiotherapy)', 'sessions', 'higher', '2025/26', 47646.00, 11545.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(27, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-06', 'Number of oncology sessions on (Chemotherapy and radiotherapy)', 'sessions', 'higher', '2026/27', 47646.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(28, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-06', 'Number of oncology sessions on (Chemotherapy and radiotherapy)', 'sessions', 'higher', '2027/28', 40600.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(29, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-06', 'Number of oncology sessions on (Chemotherapy and radiotherapy)', 'sessions', 'higher', '2028/29', 40637.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(30, 'SAGA', 'Kenyatta National Hospital', 'KNH-01', 'Specialized Healthcare Services', 'KNH-01-06', 'Number of oncology sessions on (Chemotherapy and radiotherapy)', 'sessions', 'higher', '2029/30', 41450.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(31, 'SAGA', 'Kenyatta National Hospital', 'KNH-02', 'Health Research disseminated', 'KNH-02-01', 'Number of new research projects disseminated', 'projects', 'higher', '2025/26', 5.00, 3.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(32, 'SAGA', 'Kenyatta National Hospital', 'KNH-02', 'Health Research disseminated', 'KNH-02-01', 'Number of new research projects disseminated', 'projects', 'higher', '2026/27', 5.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(33, 'SAGA', 'Kenyatta National Hospital', 'KNH-02', 'Health Research disseminated', 'KNH-02-01', 'Number of new research projects disseminated', 'projects', 'higher', '2027/28', 8.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(34, 'SAGA', 'Kenyatta National Hospital', 'KNH-02', 'Health Research disseminated', 'KNH-02-01', 'Number of new research projects disseminated', 'projects', 'higher', '2028/29', 9.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(35, 'SAGA', 'Kenyatta National Hospital', 'KNH-02', 'Health Research disseminated', 'KNH-02-01', 'Number of new research projects disseminated', 'projects', 'higher', '2029/30', 9.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(36, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-01', 'Average Length of Stay (ALOS) for trauma patients (days)', 'days', 'lower', '2025/26', 35.00, 40.90, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(37, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-01', 'Average Length of Stay (ALOS) for trauma patients (days)', 'days', 'lower', '2026/27', 34.20, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(38, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-01', 'Average Length of Stay (ALOS) for trauma patients (days)', 'days', 'lower', '2027/28', 33.50, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(39, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-01', 'Average Length of Stay (ALOS) for trauma patients (days)', 'days', 'lower', '2028/29', 33.20, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(40, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-01', 'Average Length of Stay (ALOS) for trauma patients (days)', 'days', 'lower', '2029/30', 32.80, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(41, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-02', 'Average waiting time (days) for radiotherapy', 'days', 'lower', '2025/26', 17.00, 57.00, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(42, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-02', 'Average waiting time (days) for radiotherapy', 'days', 'lower', '2026/27', 16.50, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(43, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-02', 'Average waiting time (days) for radiotherapy', 'days', 'lower', '2027/28', 16.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(44, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-02', 'Average waiting time (days) for radiotherapy', 'days', 'lower', '2028/29', 15.50, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(45, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-02', 'Average waiting time (days) for radiotherapy', 'days', 'lower', '2029/30', 15.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(46, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-03', 'Average waiting time (days) for chemotherapy', 'days', 'lower', '2025/26', 3.00, 2.10, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(47, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-03', 'Average waiting time (days) for chemotherapy', 'days', 'lower', '2026/27', 2.00, NULL, 'Baseline', 'Target (Baseline) 2026/27', '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(48, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-03', 'Average waiting time (days) for chemotherapy', 'days', 'lower', '2027/28', 2.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(49, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-03', 'Average waiting time (days) for chemotherapy', 'days', 'lower', '2028/29', 1.50, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41'),
(50, 'SAGA', 'Kenyatta National Hospital', 'KNH-03', 'Average waiting time for specialized diagnostic and treatment services reduced', 'KNH-03-03', 'Average waiting time (days) for chemotherapy', 'days', 'lower', '2029/30', 2.00, NULL, 'Target', NULL, '2026-10-15 15:40:41', '2026-10-15 15:40:41');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `project` varchar(255) DEFAULT NULL,
  `report_date` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `visibility` varchar(50) DEFAULT 'Restricted',
  `status` varchar(50) DEFAULT 'Pending',
  `uploaded_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sagas`
--

CREATE TABLE `sagas` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sagas`
--

INSERT INTO `sagas` (`id`, `name`, `code`, `description`, `logo`, `created_at`) VALUES
(1, 'Kenya Medical Research Institute (KEMRI)', 'KEMRI', 'Medical research and vaccine innovation hub', 'assets/uploads/kemri.png', '2026-10-15 16:03:04'),
(2, 'Kenya Medical Supplies Authority (KEMSA)', 'KEMSA', 'Strategic medical logistics and supply chain', 'assets/uploads/kemsa-logo.png', '2026-10-15 16:03:04'),
(3, 'Kenyatta National Hospital (KNH)', 'KNH', 'National referral and specialized teaching hospital', 'assets/uploads/Kenya National Hospital.png', '2026-10-15 16:03:04'),
(4, 'Moi Teaching and Referral Hospital (MTRH)', 'MTRH', 'Western Kenya multi-specialty referral facility', 'assets/uploads/Moi Teaching and Referral Hospital.png', '2026-10-15 16:03:04'),
(5, 'Mathari National Teaching and Referral Hospital (MNTRH)', 'MNTRH', 'National specialized psychiatric and mental health hospital', 'assets/uploads/Mathari National Teaching.jpg', '2026-10-15 16:03:04'),
(6, 'Kenyatta University Teaching, Referral & Research Hospital (KUTRRH)', 'KUTRRH', 'National oncology, research and specialized care hospital', 'assets/uploads/KUTRRH.png', '2026-10-15 16:03:04'),
(7, 'Mwai Kibaki Referral Hospital (MKRH)', 'MKRH', 'Mt. Kenya regional annex hospital for specialized surgery', 'assets/uploads/MKRH.png', '2026-10-15 16:03:04'),
(8, 'Jaramogi Oginga Odinga Teaching and Referral Hospital (JOOTRH)', 'JOOTRH', 'Lake Basin regional referral and research hub', 'assets/uploads/JOOTRH logo.png', '2026-10-15 16:03:04'),
(9, 'National Cancer Institute Kenya (NCIK)', 'NCIK', 'Cancer prevention, registration and early detection authority', 'assets/uploads/NCI-KENYA-LOGO.png', '2026-10-15 16:03:04'),
(10, 'National Syndemic Diseases Control Council (NSDCC)', 'NSDCC', 'Syndemic disease response, HIV, TB and viral hepatitis', 'assets/uploads/NSDCC.jpg', '2026-10-15 16:03:04'),
(11, 'Kenya National Blood Transfusion Service (KNBTS)', 'KNBTS', 'Safe blood and component services across all counties', 'assets/uploads/KNBTS.png', '2026-10-15 16:03:04'),
(12, 'National Spinal Injury Referral Hospital (NSIRH)', 'NSIRH', 'Specialized spinal rehabilitation and surgical care', 'assets/uploads/NSIRH.png', '2026-10-15 16:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `sub_programmes`
--

CREATE TABLE `sub_programmes` (
  `id` varchar(50) NOT NULL,
  `programme_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Viewer',
  `directorate` varchar(150) NOT NULL DEFAULT 'Health Systems',
  `can_upload` tinyint(1) DEFAULT 1,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_approve` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `directorate`, `can_upload`, `can_edit`, `can_approve`, `is_admin`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@health.go.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'Headquarters', 1, 1, 1, 1, '2026-10-14 08:52:53', '2026-10-14 08:52:53'),
(2, 'Dr. Harry Kimtai, CBS', 'ps@medicalservices.go.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Principal Secretary', 'Executive Office', 1, 1, 1, 1, '2026-10-14 08:52:53', '2026-10-14 08:52:53'),
(3, 'Project Monitoring Officer', 'monitoring@health.go.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Planning Director', 'Policy & Planning', 1, 1, 0, 0, '2026-10-14 08:52:53', '2026-10-14 08:52:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action_points`
--
ALTER TABLE `action_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ref_no` (`ref_no`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_units`
--
ALTER TABLE `delivery_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_programme_id` (`sub_programme_id`);

--
-- Indexes for table `diary_events`
--
ALTER TABLE `diary_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_diary_project_id` (`project_id`);

--
-- Indexes for table `directorates`
--
ALTER TABLE `directorates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_directorates_code` (`code`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_division_directorate_name` (`directorate_id`,`name`);

--
-- Indexes for table `flagships`
--
ALTER TABLE `flagships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kpis_and_sub_kpis`
--
ALTER TABLE `kpis_and_sub_kpis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_progress`
--
ALTER TABLE `monthly_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `outputs`
--
ALTER TABLE `outputs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_unit_id` (`delivery_unit_id`);

--
-- Indexes for table `pc_report_uploads`
--
ALTER TABLE `pc_report_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `performance_kpis`
--
ALTER TABLE `performance_kpis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flagship_id` (`flagship_id`),
  ADD KEY `idx_projects_project_code` (`project_code`);

--
-- Indexes for table `project_financial_projections`
--
ALTER TABLE `project_financial_projections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_financial_year` (`project_id`,`fiscal_year`);

--
-- Indexes for table `project_key_output_results`
--
ALTER TABLE `project_key_output_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_key_output_year` (`scope_type`,`scope_name`,`kpi_code`,`fiscal_year`),
  ADD KEY `idx_project_key_output_fy` (`scope_type`,`scope_name`,`fiscal_year`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `sagas`
--
ALTER TABLE `sagas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sagas_code` (`code`);

--
-- Indexes for table `sub_programmes`
--
ALTER TABLE `sub_programmes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programme_id` (`programme_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action_points`
--
ALTER TABLE `action_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `diary_events`
--
ALTER TABLE `diary_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `directorates`
--
ALTER TABLE `directorates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `flagships`
--
ALTER TABLE `flagships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kpis_and_sub_kpis`
--
ALTER TABLE `kpis_and_sub_kpis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `monthly_progress`
--
ALTER TABLE `monthly_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=424;

--
-- AUTO_INCREMENT for table `pc_report_uploads`
--
ALTER TABLE `pc_report_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `performance_kpis`
--
ALTER TABLE `performance_kpis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `programmes`
--
ALTER TABLE `programmes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `project_financial_projections`
--
ALTER TABLE `project_financial_projections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `project_key_output_results`
--
ALTER TABLE `project_key_output_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sagas`
--
ALTER TABLE `sagas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery_units`
--
ALTER TABLE `delivery_units`
  ADD CONSTRAINT `delivery_units_ibfk_1` FOREIGN KEY (`sub_programme_id`) REFERENCES `sub_programmes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `fk_divisions_directorate` FOREIGN KEY (`directorate_id`) REFERENCES `directorates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `monthly_progress`
--
ALTER TABLE `monthly_progress`
  ADD CONSTRAINT `monthly_progress_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `outputs`
--
ALTER TABLE `outputs`
  ADD CONSTRAINT `outputs_ibfk_1` FOREIGN KEY (`delivery_unit_id`) REFERENCES `delivery_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`flagship_id`) REFERENCES `flagships` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_financial_projections`
--
ALTER TABLE `project_financial_projections`
  ADD CONSTRAINT `fk_project_financial_projection_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_programmes`
--
ALTER TABLE `sub_programmes`
  ADD CONSTRAINT `sub_programmes_ibfk_1` FOREIGN KEY (`programme_id`) REFERENCES `programmes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
