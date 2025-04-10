-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2025 at 04:39 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `AdminEmail` varchar(120) DEFAULT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `updationDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `FullName`, `AdminEmail`, `UserName`, `Password`, `updationDate`) VALUES
(1, 'Anuj Kumar', 'admin@gmail.com', 'admin', 'f925916e2754e5e03f75dd58a5733251', '2024-12-31 19:03:56');

-- --------------------------------------------------------

--
-- Table structure for table `tblbooks`
--

CREATE TABLE `tblbooks` (
  `id` int(11) NOT NULL,
  `BookName` varchar(255) DEFAULT NULL,
  `CatId` int(11) DEFAULT NULL,
  `PublisherID` int(11) DEFAULT NULL,
  `ISBNNumber` varchar(25) DEFAULT NULL,
  `bookImage` varchar(250) NOT NULL,
  `isIssued` int(1) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `bookQty` int(11) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `copyrightDate` year(4) DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `coverType` varchar(50) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL COMMENT 'Height in cm',
  `shelfLocation` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `callNumber` varchar(100) DEFAULT NULL,
  `LRN` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblbooks`
--

INSERT INTO `tblbooks` (`id`, `BookName`, `CatId`, `PublisherID`, `ISBNNumber`, `bookImage`, `isIssued`, `RegDate`, `UpdationDate`, `bookQty`, `publisher`, `copyrightDate`, `edition`, `coverType`, `pages`, `height`, `shelfLocation`, `notes`, `callNumber`, `LRN`) VALUES
(1, 'PHP And MySql programming', 5, 1, '222333', '1efecc0ca822e40b7b673c0d79ae943f.jpg', 0, '2024-01-02 01:23:03', '2025-01-14 07:08:11', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'physics', 6, 4, '1111', 'dd8267b57e0e4feee5911cb1e1a03a79.jpg', 0, '2024-01-02 01:23:03', '2025-02-26 02:00:08', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Murach\'s MySQL', 5, 1, '9350237695', '5939d64655b4d2ae443830d73abc35b6.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:11:01', 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'WordPress for Beginners 2022: A Visual Step-by-Step Guide to Mastering WordPress', 5, 10, 'B019MO3WCM', '144ab706ba1cb9f6c23fd6ae9c0502b3.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:05:35', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'WordPress Mastery Guide:', 5, 11, 'B09NKWH7NP', '90083a56014186e88ffca10286172e64.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:05:39', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Rich Dad Poor Dad: What the Rich Teach Their Kids About Money That the Poor and Middle Class Do Not', 8, 12, 'B07C7M8SX9', '52411b2bd2a6b2e0df3eb10943a5b640.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:05:41', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'The Girl Who Drank the Moon', 8, 13, '1848126476', 'f05cd198ac9335245e1fdffa793207a7.jpg', 1, '2024-01-02 01:23:03', '2025-03-28 07:21:13', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'C++: The Complete Reference, 4th Edition', 5, 14, '007053246X', '36af5de9012bf8c804e499dc3c3b33a5.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:11:01', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'ASP.NET Core 5 for Beginners', 9, 11, 'GBSJ36344563', 'b1b6788016bbfab12cfd2722604badc9.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:11:01', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Python Packages', 9, 16, '0367687771', 'ba719639def504c64ebac89cdd0d0a85.jpg', 0, '2025-01-07 06:56:50', '2025-02-26 01:59:22', 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Python All-in-One for Dummies', 9, 18, '9388991214', 'f4ba4705a075527dd6ff5bd83a7d7562.jpg', 0, '2025-01-17 14:23:48', '2025-01-17 14:25:52', 30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Test', 4, 1, '1232131231231231231231231', 'aaa6596f190451b0cbc7e1b3ff642ee8.png', NULL, '2025-03-09 03:53:47', NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '123123123', 4, 1, '123123123', 'df1328ba92b7565c3813be56213facd5.png', NULL, '2025-03-09 11:06:49', NULL, 2147483647, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL,
  `CategoryName` varchar(150) DEFAULT NULL,
  `Status` int(1) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`id`, `CategoryName`, `Status`, `CreationDate`, `UpdationDate`) VALUES
(4, 'Romantic', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:11'),
(5, 'Technology', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(6, 'Science', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(7, 'Management', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(8, 'General', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(9, 'Programming', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21');

-- --------------------------------------------------------

--
-- Table structure for table `tblissuedbookdetails`
--

CREATE TABLE `tblissuedbookdetails` (
  `id` int(11) NOT NULL,
  `BookId` int(11) DEFAULT NULL,
  `LRN` varchar(12) DEFAULT NULL,
  `IssuesDate` timestamp NULL DEFAULT current_timestamp(),
  `ReturnDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `ReturnStatus` tinyint(1) DEFAULT NULL,
  `fine` int(11) DEFAULT NULL,
  `remark` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblissuedbookdetails`
--

INSERT INTO `tblissuedbookdetails` (`id`, `BookId`, `LRN`, `IssuesDate`, `ReturnDate`, `ReturnStatus`, `fine`, `remark`) VALUES
(1, 1, 'SID002', '2025-01-13 11:12:40', '2025-01-14 06:00:56', 1, 0, 'NA'),
(2, 7, 'SID010', '2025-01-14 05:55:25', NULL, NULL, NULL, 'NA'),
(3, 1, 'SID010', '2025-01-14 05:55:39', NULL, NULL, NULL, 'NA'),
(5, 1, 'SID002', '2025-01-14 06:02:14', '2025-01-14 06:03:36', 1, 0, 'ds'),
(6, 7, 'SID012', '2025-01-17 14:16:31', NULL, NULL, NULL, 'NA'),
(7, 13, 'SID013', '2025-01-17 14:24:47', '2025-01-17 14:25:52', 1, 0, 'NA'),
(8, 13, 'SID012', '2025-01-17 14:25:34', NULL, NULL, NULL, 'NA'),
(9, 3, '123456789011', '2025-02-24 05:54:24', '2025-02-26 02:00:08', 1, 0, '5 days return'),
(10, 9, '123456789011', '2025-02-25 01:04:25', '2025-02-26 01:59:12', 1, 0, '5 Days'),
(11, 12, '123456789011', '2025-02-26 01:25:54', '2025-02-26 01:59:22', 1, 0, 'N/A'),
(12, 12, '070707070707', '2025-03-09 11:54:56', '2025-03-14 02:52:49', 1, 0, 'N/A'),
(13, 13, '098765432112', '2025-03-28 03:19:39', NULL, NULL, NULL, '5 days return'),
(14, 13, '098765432112', '2025-03-28 03:20:20', NULL, NULL, NULL, '5 days return'),
(15, 13, '098765432112', '2025-03-28 03:52:41', NULL, NULL, NULL, '5 Days Return\r\n'),
(18, 13, '098765432112', '2025-03-28 04:05:18', NULL, NULL, NULL, 'N/A'),
(19, 9, '098765432112', '2025-03-28 07:21:13', NULL, 0, NULL, '5 Days');

-- --------------------------------------------------------

--
-- Table structure for table `tblpublishers`
--

CREATE TABLE `tblpublishers` (
  `id` int(11) NOT NULL,
  `PublisherName` varchar(255) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblpublishers`
--

INSERT INTO `tblpublishers` (`id`, `PublisherName`, `creationDate`, `UpdationDate`) VALUES
(1, 'Anuj kumar', '2023-12-31 21:23:03', '2025-01-07 06:18:43'),
(2, 'Chetan Bhagatt', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(3, 'Anita Desai', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(4, 'HC Verma', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(5, 'R.D. Sharma ', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(9, 'fwdfrwer', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(10, 'Dr. Andy Williams', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(11, 'Kyle Hill', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(12, 'Robert T. Kiyosak', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(13, 'Kelly Barnhill', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(14, 'Herbert Schildt', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(16, ' Tiffany Timbers', '2025-01-07 06:55:54', NULL),
(18, 'John Shovic', '2025-01-17 14:23:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblstudents`
--

CREATE TABLE `tblstudents` (
  `id` int(11) NOT NULL,
  `LRN` varchar(100) NOT NULL,
  `Name` varchar(120) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Grade_Level` varchar(50) DEFAULT NULL,
  `Section` varchar(50) DEFAULT NULL,
  `Strand` varchar(100) DEFAULT NULL,
  `Password` varchar(120) DEFAULT NULL,
  `Status` int(1) DEFAULT NULL,
  `RegDate` datetime DEFAULT current_timestamp(),
  `UpdationDate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblstudents`
--

INSERT INTO `tblstudents` (`id`, `LRN`, `Name`, `Address`, `Department`, `Grade_Level`, `Section`, `Strand`, `Password`, `Status`, `RegDate`, `UpdationDate`) VALUES
(1, 'SID002', 'Anuj kumar', NULL, NULL, NULL, NULL, NULL, 'f925916e2754e5e03f75dd58a5733251', 1, '2025-03-28 09:51:35', NULL),
(4, 'SID005', 'sdfsd', NULL, NULL, NULL, NULL, NULL, '92228410fc8b872914e023160cf4ae8f', 1, '2025-03-28 09:51:35', NULL),
(8, 'SID009', 'test', NULL, NULL, NULL, NULL, NULL, 'f925916e2754e5e03f75dd58a5733251', 1, '2025-03-28 09:51:35', NULL),
(9, 'SID010', 'Amit', NULL, NULL, NULL, NULL, NULL, 'f925916e2754e5e03f75dd58a5733251', 1, '2025-03-28 09:51:35', NULL),
(10, 'SID011', 'Sarita Pandey', NULL, NULL, NULL, NULL, NULL, 'f925916e2754e5e03f75dd58a5733251', 1, '2025-03-28 09:51:35', NULL),
(11, 'SID012', 'John Doe', NULL, NULL, NULL, NULL, NULL, 'f925916e2754e5e03f75dd58a5733251', 1, '2025-03-28 09:51:35', NULL),
(12, 'SID013', 'Ajay Kumar Singh', NULL, NULL, NULL, NULL, NULL, 'f925916e2754e5e03f75dd58a5733251', 1, '2025-03-28 09:51:35', NULL),
(13, 'SID014', 'Okay', NULL, NULL, NULL, NULL, NULL, 'a2bb6832cd29a91597aad8907bfb8b7f', 1, '2025-03-28 09:51:35', NULL),
(14, '12312312312312312312', 'Tester', NULL, NULL, NULL, NULL, NULL, '68eacb97d86f0c4621fa2b0e17cabd8c', 1, '2025-03-28 09:51:35', NULL),
(16, '98123912312312312', 'Testerr', 'Sampaloc, Talavera Nueva Ecija', 'Junior high', '10', 'ST PAUL', 'N/A', '4ef8e68130fe64150d9c81fa479684e1', 1, '2025-03-28 09:51:35', NULL),
(17, '09812341212', 'Ranilei A. Sarmiento', 'San Alejandro, Quezon, Nueva Ecija', 'Senior High', 'Grade 12', '', 'ABM', '3fc0a7acf087f549ac2b266baf94b8b1', 1, '2025-03-28 09:51:35', NULL),
(18, '123456789011', 'Tester', 'Testerr', 'Senior High', 'Grade 11', '', 'HUMSS', 'cdc469444b76536cea721d7397a0c17f', 1, '2025-03-28 09:51:35', NULL),
(63, '098765413213', 'Aski', 'Talavera, Nueva Ecija', 'Senior High', 'Grade 11', '', 'STEM', 'ac92cc1bbec03a22b3c8e2b0c1501268', 1, '2025-03-28 09:51:35', NULL),
(64, '040404040404', 'Testerrr', 'Santo Domingo, Nueva Ecija', 'Senior High', 'Grade 11', '', 'ABM', 'a3876fafbc8b9b9d3820b6e3a610e3d2', 1, '2025-03-28 09:51:35', NULL),
(66, '0202020202020', 'Raniwow ', 'Quezon, Nueva Ecija ', 'Senior High School', '12', 'St.GinawakoNamanLahat', 'ABM', NULL, 1, '2025-03-28 09:51:35', NULL),
(67, '070707070707', 'Joseph Pascual', 'Santo Domingo, Nueva Ecija', 'Senior High', 'Grade 12', '', 'STEM', '64e36fbed01c5951c8c3a55d875f4c81', 1, '2025-03-28 09:51:35', NULL),
(72, '123456789000', 'Testing', 'Testing', 'Junior High', 'Grade 7', '', '', 'ac1c8d64fd23ae5a7eac5b7f7ffee1fa', 1, '2025-03-28 09:51:35', NULL),
(73, '999999999999', 'ProPro', 'ProPro', 'Junior High', 'Grade 9', '', '', '7c38cfd72649a212d0467bb0296e408d', 1, '2025-03-28 09:51:35', NULL),
(74, '888888888888', 'ProProo', 'ProProo', 'Junior High', 'Grade 9', '', '', '553e83ca69693b33ef73958c04b7a315', 1, '2025-03-28 09:51:35', NULL),
(75, '098765432112', 'ASKI Man', 'Sampaloc, Talavera, Nueva Ecija', 'Junior High', 'Grade 7', '', '', 'bde63b27cf8647a3887e4992bcc4587e', 1, '2025-03-28 09:51:35', '2025-03-28 09:59:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblbooks`
--
ALTER TABLE `tblbooks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblissuedbookdetails`
--
ALTER TABLE `tblissuedbookdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblpublishers`
--
ALTER TABLE `tblpublishers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblstudents`
--
ALTER TABLE `tblstudents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `StudentId` (`LRN`),
  ADD UNIQUE KEY `LRN` (`LRN`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblbooks`
--
ALTER TABLE `tblbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblissuedbookdetails`
--
ALTER TABLE `tblissuedbookdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tblpublishers`
--
ALTER TABLE `tblpublishers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tblstudents`
--
ALTER TABLE `tblstudents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
