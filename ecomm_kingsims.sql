-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 30, 2023 at 09:44 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecomm_kingsims`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `account_no` int(11) NOT NULL,
  `account_fname` varchar(100) NOT NULL,
  `account_mname` varchar(100) NOT NULL,
  `account_lname` varchar(100) NOT NULL,
  `account_username` varchar(100) NOT NULL,
  `account_password` longtext NOT NULL,
  `account_status` int(10) NOT NULL DEFAULT 1,
  `account_datetime_added` datetime NOT NULL,
  `account_avatar_base64` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`account_no`, `account_fname`, `account_mname`, `account_lname`, `account_username`, `account_password`, `account_status`, `account_datetime_added`, `account_avatar_base64`) VALUES
(1, 'kingsims', '', 'admin', 'kingsims_admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 1, '2023-01-16 17:50:12', ''),
(2, 'EC', '', 'admin', 'ec2', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 1, '2023-01-16 17:50:16', '');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_title` longtext NOT NULL,
  `plan_id` int(10) NOT NULL,
  `wc_id` varchar(100) NOT NULL,
  `product_status` int(10) NOT NULL DEFAULT 1,
  `product_datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transatel_esims`
--

CREATE TABLE `transatel_esims` (
  `esim_id` int(11) NOT NULL,
  `esim_number` varchar(100) NOT NULL,
  `esim_status` int(10) NOT NULL DEFAULT 1,
  `esim_datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`account_no`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `transatel_esims`
--
ALTER TABLE `transatel_esims`
  ADD PRIMARY KEY (`esim_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `account_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transatel_esims`
--
ALTER TABLE `transatel_esims`
  MODIFY `esim_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
