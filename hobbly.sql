-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 08:51 AM
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
-- Database: `hobbly`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `paneladmin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('staff','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`paneladmin_id`, `username`, `password_hash`, `role`) VALUES
(2, 'taitaja@hobbly.app', '$2a$12$ho/xWrODW/ylJwB03AH6au2UrRgRiMymkRVpUrYIxosGiMa1NqvaO', 'staff'),
(9, 'test@test.com', '$2y$10$7jQmDiHY2wKhtCxpAmrXK.umGC9/p4I4.CIP32SXjX/r/ZfuUSdDO', 'user'),
(10, 'test2@test.com', '$2y$10$2CZk2kD0aARf9e6vjXKs.u635LkXKMx9UvMGdGs1loMQrtCPI04.e', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `org_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postcode` varchar(20) NOT NULL,
  `businessid` varchar(50) DEFAULT NULL,
  `organization_type` enum('Association','Private','Limited company') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`org_id`, `name`, `email`, `phone`, `photo_url`, `description`, `address`, `city`, `postcode`, `businessid`, `organization_type`) VALUES
(9, 'testorg', 'testorg@testcompany.com', '+3584012341512', 'https://i.imgur.com/oIWMxv6.jpeg', 'nothing ever happens', 'Testitie 5', 'Testikylä', '12345', NULL, 'Private'),
(10, 'test2', 'testorg@testcompany2.com', '12341333312', 'https://i.imgur.com/oIWMxv6.jpeg', 'asdasdsd', 'asdasdd 5', 'asdasd', '12312', NULL, 'Limited company');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `img_url` varchar(255) NOT NULL,
  `activity_type` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `event_start` datetime NOT NULL DEFAULT current_timestamp(),
  `event_end` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `creator_id`, `title`, `description`, `url`, `img_url`, `activity_type`, `category`, `tags`, `address`, `latitude`, `longitude`, `event_start`, `event_end`) VALUES
(2, 9, 'test title', 'this is a description', 'google.com', 'https://pbs.twimg.com/media/GghcC8iaUAEAOM9.jpg', 'Club', 'Games and eSports', '', 'Testitie 5', '65', '26', '2025-11-06 11:24:47', '2025-11-06 11:24:47'),
(3, 9, 'test title', 'this is a description', 'google.com', 'imgur.com', 'Club', 'Games and eSports', 'Open to everyone, Suitable for seniors, Suitable for special groups, Equipment available on-site, test2, test3', 'Testitie 5', '60', '23', '2025-11-06 11:24:47', '2025-11-06 11:24:47'),
(4, 9, 'test title', 'this is a description', 'google.com', 'imgur.com', 'Hobby opportunity', 'Children and families', 'Free of charge, Suitable for special groups, Requires pre-registration', 'Testitie 5', '62', '25', '2025-11-06 11:24:47', '2025-11-06 11:24:47'),
(5, 9, 'Sadonkorjuujuhla', 'Torilla paljon tuottajia myymässä omia tuotteitaan.', 'https://potwell.fi/viljelijat/', 'https://kohteet.haso.fi/sites/default/files/styles/mainimage/public/2020-08/pexels-ella-olsson-1458694.jpg', 'Event', 'Nature and outdoor activities', 'Open to everyone, Ongoing event, Suitable for families, Kurpitsa, peruna', 'Torikatu 5', '62.892417', '27.678422', '2025-11-06 11:24:47', '2026-11-19 11:24:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`paneladmin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`org_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `paneladmin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `organizations`
--
ALTER TABLE `organizations`
  ADD CONSTRAINT `organizations_ibfk_2` FOREIGN KEY (`org_id`) REFERENCES `admins` (`paneladmin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
