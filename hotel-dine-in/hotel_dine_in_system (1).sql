-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 21, 2025 at 10:10 AM
-- Server version: 8.0.41-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_dine_in_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_master`
--

CREATE TABLE `admin_master` (
  `admin_id` int NOT NULL,
  `admin_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cuisines_master`
--

CREATE TABLE `cuisines_master` (
  `cuisine_id` int NOT NULL,
  `cuisine_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuisine_image` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuisine_status` int NOT NULL DEFAULT '1' COMMENT '1 for show 0 for hide',
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cuisines_master`
--

INSERT INTO `cuisines_master` (`cuisine_id`, `cuisine_name`, `cuisine_image`, `cuisine_status`, `is_delete`) VALUES
(1, 'Italian', 'http://example.com/images/italian.jpg', 1, 0),
(2, 'Chinese', 'http://example.com/images/chinese.jpg', 1, 0),
(3, 'Mexican', 'http://example.com/images/mexican.jpg', 1, 0),
(4, 'Gujarati', 'http://example.com/images/gujarati.jpg', 1, 0),
(5, 'Punjabi', 'http://example.com/images/punjabi.jpg', 1, 0),
(6, 'south indian', 'http://example.com/images/south indian.jpg', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `owner_address`
--

CREATE TABLE `owner_address` (
  `owner_address_id` int NOT NULL,
  `owner_address_house_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_address_society` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_address_street` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_address_area` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_address_landmark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` int NOT NULL,
  `owner_address_addedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner_address_updatedAt` datetime DEFAULT NULL,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active and 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner_master`
--

CREATE TABLE `owner_master` (
  `owner_id` int NOT NULL,
  `owner_full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_phone_number` bigint NOT NULL,
  `owner_gender` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0 for male, 1 for female, 2 for other',
  `owner_aadharcard` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_pancard` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner_updatedAt` datetime DEFAULT NULL,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active, 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owner_master`
--

INSERT INTO `owner_master` (`owner_id`, `owner_full_name`, `owner_email`, `owner_phone_number`, `owner_gender`, `owner_aadharcard`, `owner_pancard`, `owner_createdAt`, `owner_updatedAt`, `is_delete`) VALUES
(2, 'Sagar', 'sagar1@gmail.com', 1234567891, 'male', 'sagar_aadhar', 'sagar_pancard', '2025-03-16 21:15:26', NULL, 0),
(3, 'Ram Bhai', 'rambhai1@gmail.com', 8475915789, 'male', 'rambhai_adhar', 'rambhai_pancard', '2025-03-19 23:41:31', NULL, 0),
(4, 'Kashyap Patel', 'kash11@gmail.com', 9854755781, 'male', 'kash_adhar', 'kash_pancard', '2025-03-19 23:42:32', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `review` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_hidden` int NOT NULL DEFAULT '0' COMMENT '0 for show and 1 for hide',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active and 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `restaurant_id`, `user_id`, `rating`, `review`, `image`, `is_hidden`, `createdAt`, `is_delete`) VALUES
(1, 2, 2, 5, 'Nice place', NULL, 0, '2025-03-17 23:09:35', 0),
(2, 4, 2, 2, 'ok ok', '', 0, '2025-03-18 14:36:50', 0),
(4, 2, 1, 3, 'ok ok', '', 0, '2025-03-18 14:42:55', 0),
(5, 5, 2, 5, 'amazing place', '', 0, '2025-03-20 01:48:50', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL,
  `user_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `restaurant_slot_id` int NOT NULL,
  `reservation_date` date NOT NULL,
  `number_of_guests` int NOT NULL,
  `reservation_mode` int NOT NULL COMMENT '0 for online and 1 for offline',
  `reservation_status` int NOT NULL DEFAULT '1' COMMENT '0 for cancelled and 1 for confirmed',
  `cancel_reason` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancel_by` int DEFAULT NULL COMMENT '0 for user, 1 for restaurant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `restaurant_id`, `restaurant_slot_id`, `reservation_date`, `number_of_guests`, `reservation_mode`, `reservation_status`, `cancel_reason`, `cancel_by`) VALUES
(2, 2, 3, 446, '2025-03-20', 20, 0, 1, 'other plan', NULL),
(6, 2, 3, 446, '2025-03-20', 5, 0, 1, NULL, NULL),
(8, 2, 3, 443, '2025-03-20', 5, 0, 0, 'other plan', NULL),
(9, 2, 3, 443, '2025-03-20', 5, 0, 0, 'other plan', NULL),
(10, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(11, 2, 3, 443, '2025-03-20', 1, 0, 1, 'other plan', NULL),
(12, 2, 3, 443, '2025-03-20', 1, 0, 1, 'other plan', NULL),
(13, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(14, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(15, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(16, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(17, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(18, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(19, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(20, 2, 3, 443, '2025-03-20', 1, 0, 1, NULL, NULL),
(21, 2, 3, 443, '2025-03-20', 1, 0, 0, 'other plan', NULL),
(22, 2, 3, 443, '2025-03-20', 1, 0, 1, NULL, NULL),
(23, 2, 3, 443, '2025-03-20', 1, 0, 1, NULL, NULL),
(27, 3, 4, 476, '2025-03-21', 20, 0, 0, 'other plan', NULL),
(29, 3, 4, 476, '2025-03-21', 20, 0, 1, NULL, NULL),
(30, 3, 4, 476, '2025-03-21', 10, 0, 1, NULL, NULL),
(31, 3, 4, 476, '2025-03-22', 10, 0, 1, NULL, NULL),
(38, 0, 4, 475, '2025-03-22', 25, 1, 0, 'server problem', 1),
(41, 1, 2, 439, '2025-03-22', 5, 0, 0, 'Other time', 0),
(42, 0, 4, 475, '2025-03-22', 25, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_address`
--

CREATE TABLE `restaurant_address` (
  `restaurant_address_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `restaurant_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_complex` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_street` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_area` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_landmark` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restaurant_city` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_state` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_country` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_latitude` double(9,6) NOT NULL,
  `restaurant_longitude` double(9,6) NOT NULL,
  `restaurant_address_addedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `restaurant_address_addedBy` int NOT NULL,
  `restaurant_address_updatedAt` datetime DEFAULT NULL,
  `restaurant_address_updatedBy` int NOT NULL,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active,1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_address`
--

INSERT INTO `restaurant_address` (`restaurant_address_id`, `restaurant_id`, `restaurant_number`, `restaurant_complex`, `restaurant_street`, `restaurant_area`, `restaurant_landmark`, `restaurant_city`, `restaurant_state`, `restaurant_country`, `restaurant_latitude`, `restaurant_longitude`, `restaurant_address_addedAt`, `restaurant_address_addedBy`, `restaurant_address_updatedAt`, `restaurant_address_updatedBy`, `is_delete`) VALUES
(1, 2, 'Top Floor', 'Vittal Complex', 'Chandkheda', 'New CG Rd', 'opp. Sakar School', 'Ahmedabad', 'Gujarat', 'India', 23.109468, 72.590449, '2025-03-17 15:32:02', 2, NULL, 0, 0),
(4, 3, 'AMC', ' OPPOSITE PAM GREENS APPARTMENT MAKRBA GARDEN', 'Makarba', 'POWER ROAD', 'opposite TORENT', 'Ahmedabad', 'Gujarat', 'India', 22.999473, 72.505001, '2025-03-18 09:38:47', 2, NULL, 0, 0),
(5, 4, '1st floor', 'Dev Aurum Commercial Complex', 'Prahladnagar', '100 Feet Anand Nagar Rd', '', 'Ahmedabad', 'Gujarat', 'India', 23.012439, 72.514516, '2025-03-18 13:07:05', 2, NULL, 0, 0),
(6, 5, 'Plot 14 &15 ', 'Al Asbab Park ', 'Makarba', '100 Feet Road, Makarba', 'near Al Burooj', 'Ahmedabad', 'Gujarat', 'India', 22.991966, 72.513515, '2025-03-19 23:58:17', 3, NULL, 0, 0),
(7, 5, '2G32+WHP,', 'YMCA International Centre', 'Makarba', 'Sarkhej - Gandhinagar Hwy', '', 'Ahmedabad', 'Gujarat', 'India', 23.004904, 72.501417, '2025-03-20 14:05:31', 3, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_cuisines`
--

CREATE TABLE `restaurant_cuisines` (
  `restaurant_cuisine_id` int NOT NULL,
  `cuisine_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `is_hidden` int NOT NULL DEFAULT '0' COMMENT '0 for show and 1 for hide'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_cuisines`
--

INSERT INTO `restaurant_cuisines` (`restaurant_cuisine_id`, `cuisine_id`, `restaurant_id`, `is_hidden`) VALUES
(1, 1, 2, 0),
(2, 2, 2, 0),
(10, 2, 3, 0),
(11, 4, 3, 0),
(12, 5, 3, 0),
(13, 1, 4, 0),
(14, 2, 4, 0),
(15, 4, 4, 0),
(16, 5, 4, 0),
(21, 2, 5, 0),
(22, 4, 5, 0),
(23, 5, 5, 0),
(24, 6, 5, 0),
(25, 1, 5, 1),
(26, 2, 6, 0);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_images`
--

CREATE TABLE `restaurant_images` (
  `restaurant_image_id` int NOT NULL,
  `restaurant_image_url` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_images`
--

INSERT INTO `restaurant_images` (`restaurant_image_id`, `restaurant_image_url`, `restaurant_id`) VALUES
(1, 'restaurant 2 image', 2),
(2, '9778_kake_di_haddi.jpg', 5),
(3, '4347_9 world.jpg', 5),
(4, '2217_9 world.jpg', 6);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_master`
--

CREATE TABLE `restaurant_master` (
  `restaurant_id` int NOT NULL,
  `restaurant_name` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_phone_number` bigint NOT NULL,
  `restaurant_licence_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_website_link` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restaurant_avg_price` int NOT NULL,
  `restaurant_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `restaurant_food_type` int NOT NULL COMMENT '0 for veg, 1 for non veg, 2 for both',
  `restauarnt_approved_status` int NOT NULL DEFAULT '0' COMMENT '0 for pending, 1 for approve,2 for cancel',
  `restaurant_addedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `restaurant_addedBy` int NOT NULL,
  `restaurant_updatedAt` datetime DEFAULT NULL,
  `restaurant_updatedBy` int NOT NULL,
  `restaurant_approvedAt` datetime DEFAULT NULL,
  `restaurant_approvedBy` int NOT NULL,
  `restaurant_status` int NOT NULL DEFAULT '0' COMMENT '0 for close, 1 for active, 2 for Temporary Stop',
  `restaurant_capacity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_master`
--

INSERT INTO `restaurant_master` (`restaurant_id`, `restaurant_name`, `restaurant_email`, `restaurant_phone_number`, `restaurant_licence_no`, `restaurant_website_link`, `restaurant_avg_price`, `restaurant_description`, `restaurant_food_type`, `restauarnt_approved_status`, `restaurant_addedAt`, `restaurant_addedBy`, `restaurant_updatedAt`, `restaurant_updatedBy`, `restaurant_approvedAt`, `restaurant_approvedBy`, `restaurant_status`, `restaurant_capacity`) VALUES
(2, '7 Star Rooftop Cafe\n', 'sagar1@gmail.com', 1234567891, 'ff04456', '', 250, 'chinese restaurant', 0, 1, '2025-03-16 22:34:38', 2, NULL, 0, NULL, 0, 1, 40),
(3, 'Shiv Fast Food', 'sagar1@gmail.com', 1234567891, 'ff044258', '', 200, 'mix restaurant food', 0, 1, '2025-03-18 09:24:05', 2, NULL, 0, NULL, 0, 1, 50),
(4, 'Kake di hatti', 'sagar1@gmail.com', 1234567891, 'ff0442458', '', 450, 'North Indian restaurant', 0, 1, '2025-03-18 09:41:27', 2, NULL, 0, NULL, 0, 1, 30),
(5, 'The Kebabish Restaurant', 'rambhai1@gmail.com', 8475915789, 'ff52698', 'http://thekebabish.com/', 300, 'mix restaurant', 2, 1, '2025-03-19 23:46:26', 3, NULL, 0, NULL, 0, 1, 55),
(6, 'The 9 World Cuisine Restaurant', 'kash11@gmail.com', 9854755781, 'ff52454', '', 650, 'Chinese restaurant', 0, 0, '2025-03-20 13:58:13', 4, NULL, 0, NULL, 0, 0, 60);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_owners`
--

CREATE TABLE `restaurant_owners` (
  `restaurant_owner_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `owner_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_owners`
--

INSERT INTO `restaurant_owners` (`restaurant_owner_id`, `restaurant_id`, `owner_id`) VALUES
(1, 2, 2),
(2, 3, 2),
(3, 4, 2),
(4, 5, 3),
(5, 6, 4);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_slots`
--

CREATE TABLE `restaurant_slots` (
  `restaurant_slot_id` int NOT NULL,
  `restaurant_time_id` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active, 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_slots`
--

INSERT INTO `restaurant_slots` (`restaurant_slot_id`, `restaurant_time_id`, `start_time`, `end_time`, `is_delete`) VALUES
(428, 170, '09:00:00', '09:15:00', 0),
(429, 170, '09:15:00', '09:30:00', 0),
(430, 170, '09:30:00', '09:45:00', 0),
(431, 170, '09:45:00', '10:00:00', 0),
(432, 170, '10:00:00', '10:15:00', 0),
(433, 170, '10:15:00', '10:30:00', 0),
(434, 171, '11:00:00', '11:45:00', 0),
(435, 171, '11:45:00', '12:30:00', 0),
(436, 171, '12:30:00', '01:15:00', 0),
(437, 171, '01:15:00', '02:00:00', 0),
(438, 172, '06:00:00', '07:00:00', 0),
(439, 172, '07:00:00', '08:00:00', 0),
(440, 172, '08:00:00', '09:00:00', 0),
(441, 172, '09:00:00', '10:00:00', 0),
(442, 172, '10:00:00', '11:00:00', 0),
(443, 173, '08:00:00', '08:15:00', 0),
(444, 173, '08:15:00', '08:30:00', 0),
(445, 173, '08:30:00', '08:45:00', 0),
(446, 173, '08:45:00', '09:00:00', 0),
(447, 173, '09:00:00', '09:15:00', 0),
(448, 173, '09:15:00', '09:30:00', 0),
(449, 173, '09:30:00', '09:45:00', 0),
(450, 173, '09:45:00', '10:00:00', 0),
(451, 173, '10:00:00', '10:15:00', 0),
(452, 173, '10:15:00', '10:30:00', 0),
(453, 174, '11:00:00', '11:45:00', 0),
(454, 174, '11:45:00', '12:30:00', 0),
(455, 174, '12:30:00', '01:15:00', 0),
(456, 174, '01:15:00', '02:00:00', 0),
(457, 175, '08:00:00', '08:15:00', 0),
(458, 175, '08:15:00', '08:30:00', 0),
(459, 175, '08:30:00', '08:45:00', 0),
(460, 175, '08:45:00', '09:00:00', 0),
(461, 175, '09:00:00', '09:15:00', 0),
(462, 175, '09:15:00', '09:30:00', 0),
(463, 175, '09:30:00', '09:45:00', 0),
(464, 175, '09:45:00', '10:00:00', 0),
(465, 175, '10:00:00', '10:15:00', 0),
(466, 175, '10:15:00', '10:30:00', 0),
(467, 176, '11:00:00', '11:45:00', 0),
(468, 176, '11:45:00', '12:30:00', 0),
(469, 176, '12:30:00', '01:15:00', 0),
(470, 176, '01:15:00', '02:00:00', 0),
(471, 177, '05:30:00', '06:30:00', 0),
(472, 177, '06:30:00', '07:30:00', 0),
(473, 177, '07:30:00', '08:30:00', 0),
(474, 177, '08:30:00', '09:30:00', 0),
(475, 177, '09:30:00', '10:30:00', 0),
(476, 177, '10:30:00', '11:30:00', 0),
(506, 186, '10:30:00', '11:15:00', 1),
(507, 186, '11:15:00', '12:00:00', 1),
(508, 186, '12:00:00', '12:45:00', 1),
(509, 186, '12:45:00', '01:30:00', 1),
(510, 186, '01:30:00', '02:15:00', 1),
(511, 186, '02:15:00', '03:00:00', 1),
(518, 190, '09:30:00', '10:15:00', 1),
(519, 190, '10:15:00', '11:00:00', 1),
(520, 190, '11:00:00', '11:45:00', 1),
(521, 190, '11:45:00', '12:30:00', 1),
(522, 190, '12:30:00', '01:15:00', 1),
(523, 190, '01:15:00', '02:00:00', 1),
(524, 190, '02:00:00', '02:45:00', 1),
(525, 191, '09:30:00', '10:30:00', 0),
(526, 191, '10:30:00', '11:30:00', 0),
(527, 191, '11:30:00', '12:30:00', 0),
(528, 191, '12:30:00', '01:30:00', 0),
(529, 191, '01:30:00', '02:30:00', 0),
(530, 192, '08:30:00', '09:30:00', 0),
(531, 192, '09:30:00', '10:30:00', 0),
(532, 192, '10:30:00', '11:30:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_time`
--

CREATE TABLE `restaurant_time` (
  `restaurant_time_id` int NOT NULL,
  `restaurant_id` int NOT NULL,
  `meal_type` int NOT NULL COMMENT '0 for breakfast,1 for lunch,2 for dinner',
  `meal_start_time` time NOT NULL,
  `meal_end_time` time NOT NULL,
  `interval_time` int NOT NULL,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active, 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_time`
--

INSERT INTO `restaurant_time` (`restaurant_time_id`, `restaurant_id`, `meal_type`, `meal_start_time`, `meal_end_time`, `interval_time`, `is_delete`) VALUES
(170, 2, 0, '09:00:00', '10:30:00', 15, 0),
(171, 2, 1, '11:00:00', '14:00:00', 45, 0),
(172, 2, 2, '18:00:00', '23:00:00', 60, 0),
(173, 3, 0, '08:00:00', '10:30:00', 15, 0),
(174, 3, 1, '11:00:00', '14:00:00', 45, 0),
(175, 4, 0, '08:00:00', '10:30:00', 15, 0),
(176, 4, 1, '11:00:00', '14:00:00', 45, 0),
(177, 4, 2, '17:30:00', '23:00:00', 60, 0),
(186, 5, 1, '10:30:00', '14:30:00', 45, 1),
(190, 5, 1, '09:30:00', '14:30:00', 45, 1),
(191, 6, 1, '09:30:00', '14:30:00', 60, 0),
(192, 6, 2, '20:30:00', '23:30:00', 60, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `user_address_id` int NOT NULL,
  `user_address_house_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_address_society` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_address_street` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_address_area` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_address_landmark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `user_address_addedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_address_updatedAt` datetime DEFAULT NULL,
  `user_address_latitude` double(9,6) NOT NULL,
  `user_address_longitude` double(9,6) NOT NULL,
  `is_delete` int NOT NULL DEFAULT '0' COMMENT '0 for active and 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`user_address_id`, `user_address_house_number`, `user_address_society`, `user_address_street`, `user_address_area`, `user_address_landmark`, `city_name`, `state_name`, `country_name`, `user_id`, `user_address_addedAt`, `user_address_updatedAt`, `user_address_latitude`, `user_address_longitude`, `is_delete`) VALUES
(3, 'B-10', 'Ram apartment', 'Karmachari Nagar', 'ghatlodia', '', 'Ahmedabad', 'Gujarat', 'India', 2, '2025-03-15 19:41:39', '2025-03-20 01:06:26', 23.054133, 72.539249, 0),
(8, 'B-03', 'Hari om society', 'Ghatlodiya', 'prabhat chok', '', 'Ahmedabad', 'Gujarat', 'India', 3, '2025-03-20 00:33:16', NULL, 23.070404, 72.544338, 0),
(14, 'B-03', 'Hari om society', 'Ghatlodiya', 'prabhat chok', '', 'Ahmedabad', 'Gujarat', 'India', 3, '2025-03-20 00:49:10', NULL, 23.070404, 72.544338, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_master`
--

CREATE TABLE `user_master` (
  `user_id` int NOT NULL,
  `user_full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_phone_number` bigint NOT NULL,
  `user_password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_gender` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0=male, 1=female,2=other',
  `user_image` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_updatedAt` datetime DEFAULT NULL,
  `is_block` smallint NOT NULL DEFAULT '0' COMMENT '0 for unblock and 1 for block',
  `is_blockedAt` datetime DEFAULT NULL,
  `block_until` datetime DEFAULT NULL,
  `is_delete` smallint NOT NULL DEFAULT '0' COMMENT '0 for active and 1 for delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_master`
--

INSERT INTO `user_master` (`user_id`, `user_full_name`, `user_email`, `user_phone_number`, `user_password`, `user_gender`, `user_image`, `user_createdAt`, `user_updatedAt`, `is_block`, `is_blockedAt`, `block_until`, `is_delete`) VALUES
(1, 'ram ji', 'rajubha11@gmail.com', 8320162754, '$2y$10$JEuIIn3z1Ebz3q1YN96IzOlMlw32tK7hKwvemCWJxvXzE.WpoRWqu', 'male', '1593_0101240106169779.png', '2025-03-15 18:51:40', '2025-03-20 02:00:26', 0, NULL, NULL, 0),
(2, 'henyy', 'henny@123.com3', 5241256789, '$2y$10$5Bjgubx2ZykhfQcmEcW9Fu691h8m2WfLKX2C2/Ted9VoHKFs68QC.', '0', NULL, '2025-03-15 18:52:45', NULL, 1, '2025-03-21 01:56:16', '2025-03-31 01:56:16', 0),
(3, 'Dev Patel', 'dev12@123.com', 8574258963, '$2y$10$Neb2oZ76Gg/URCgaUd3Er.c183RjoX2MOJiudXqMQc2xRnEc3E/Ga', 'male', NULL, '2025-03-20 00:25:31', NULL, 0, NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_master`
--
ALTER TABLE `admin_master`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `cuisines_master`
--
ALTER TABLE `cuisines_master`
  ADD PRIMARY KEY (`cuisine_id`);

--
-- Indexes for table `owner_address`
--
ALTER TABLE `owner_address`
  ADD PRIMARY KEY (`owner_address_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `owner_master`
--
ALTER TABLE `owner_master`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `restaurant_slot_id` (`restaurant_slot_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `restaurant_address`
--
ALTER TABLE `restaurant_address`
  ADD PRIMARY KEY (`restaurant_address_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurant_cuisines`
--
ALTER TABLE `restaurant_cuisines`
  ADD PRIMARY KEY (`restaurant_cuisine_id`),
  ADD KEY `cuisine_id` (`cuisine_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurant_images`
--
ALTER TABLE `restaurant_images`
  ADD PRIMARY KEY (`restaurant_image_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurant_master`
--
ALTER TABLE `restaurant_master`
  ADD PRIMARY KEY (`restaurant_id`),
  ADD UNIQUE KEY `restaurant_licence_no` (`restaurant_licence_no`);

--
-- Indexes for table `restaurant_owners`
--
ALTER TABLE `restaurant_owners`
  ADD PRIMARY KEY (`restaurant_owner_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurant_slots`
--
ALTER TABLE `restaurant_slots`
  ADD PRIMARY KEY (`restaurant_slot_id`),
  ADD KEY `restaurant_time_id` (`restaurant_time_id`);

--
-- Indexes for table `restaurant_time`
--
ALTER TABLE `restaurant_time`
  ADD PRIMARY KEY (`restaurant_time_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`user_address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_master`
--
ALTER TABLE `user_master`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD UNIQUE KEY `user_phone_number` (`user_phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_master`
--
ALTER TABLE `admin_master`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cuisines_master`
--
ALTER TABLE `cuisines_master`
  MODIFY `cuisine_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `owner_address`
--
ALTER TABLE `owner_address`
  MODIFY `owner_address_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owner_master`
--
ALTER TABLE `owner_master`
  MODIFY `owner_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `restaurant_address`
--
ALTER TABLE `restaurant_address`
  MODIFY `restaurant_address_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `restaurant_cuisines`
--
ALTER TABLE `restaurant_cuisines`
  MODIFY `restaurant_cuisine_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `restaurant_images`
--
ALTER TABLE `restaurant_images`
  MODIFY `restaurant_image_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurant_master`
--
ALTER TABLE `restaurant_master`
  MODIFY `restaurant_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `restaurant_owners`
--
ALTER TABLE `restaurant_owners`
  MODIFY `restaurant_owner_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `restaurant_slots`
--
ALTER TABLE `restaurant_slots`
  MODIFY `restaurant_slot_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=533;

--
-- AUTO_INCREMENT for table `restaurant_time`
--
ALTER TABLE `restaurant_time`
  MODIFY `restaurant_time_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `user_address_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_master`
--
ALTER TABLE `user_master`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `owner_address`
--
ALTER TABLE `owner_address`
  ADD CONSTRAINT `owner_address_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owner_master` (`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`restaurant_slot_id`) REFERENCES `restaurant_slots` (`restaurant_slot_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurant_address`
--
ALTER TABLE `restaurant_address`
  ADD CONSTRAINT `restaurant_address_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurant_cuisines`
--
ALTER TABLE `restaurant_cuisines`
  ADD CONSTRAINT `restaurant_cuisines_ibfk_1` FOREIGN KEY (`cuisine_id`) REFERENCES `cuisines_master` (`cuisine_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `restaurant_cuisines_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurant_images`
--
ALTER TABLE `restaurant_images`
  ADD CONSTRAINT `restaurant_images_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurant_owners`
--
ALTER TABLE `restaurant_owners`
  ADD CONSTRAINT `restaurant_owners_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owner_master` (`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `restaurant_owners_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurant_slots`
--
ALTER TABLE `restaurant_slots`
  ADD CONSTRAINT `restaurant_slots_ibfk_1` FOREIGN KEY (`restaurant_time_id`) REFERENCES `restaurant_time` (`restaurant_time_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurant_time`
--
ALTER TABLE `restaurant_time`
  ADD CONSTRAINT `restaurant_time_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant_master` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
