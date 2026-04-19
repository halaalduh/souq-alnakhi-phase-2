-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 19, 2026 at 07:29 PM
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
-- Database: `souq_alnakhil_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `farms`
--

CREATE TABLE `farms` (
  `id` int(11) NOT NULL,
  `farmer_user_id` int(11) NOT NULL,
  `farm_name` varchar(100) NOT NULL,
  `region` enum('Najd','Qassim','Al-Ahsa') NOT NULL,
  `farm_description` text NOT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farms`
--

INSERT INTO `farms` (`id`, `farmer_user_id`, `farm_name`, `region`, `farm_description`, `contact_phone`, `contact_email`, `is_verified`) VALUES
(7, 2, 'Palm Valley', 'Qassim', 'Trusted farm producing premium Sukkari dates.', '0551111111', 'palmvalley@souq.com', 1),
(8, 3, 'Golden Palm Farm', 'Najd', 'Local farm offering fresh Khalas dates.', '0552222222', 'goldenpalm@souq.com', 0),
(9, 10, 'Haifa Fram', 'Qassim', 'hello', '0600011891', 'haifathefirst@gmail.com', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `farm_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `date_type` enum('Ajwa','Sukkari','Khalas') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `farm_id`, `product_name`, `date_type`, `price`, `quantity`, `description`, `image`) VALUES
(10, 7, 'Sukkari Dates', 'Sukkari', 35.00, 15, 'Sweet and soft Sukkari dates, perfect for daily use.', '\r\n'),
(11, 7, 'Ajwa Dates', 'Ajwa', 45.00, 20, 'Fresh premium Ajwa dates from a trusted local farm.', ''),
(12, 8, 'Khalas Dates', 'Khalas', 40.00, 12, 'High-quality Khalas dates with rich texture and flavor.', ''),
(15, 9, 's', 'Sukkari', 12.00, 12, '12', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','farmer','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@souq.com', '$2y$12$3yzH06Br5fE73qsDlZqaVeErloMIP1Qy.77laHuM5MvF6xKJQztrC', 'admin', '2026-04-10 17:18:31'),
(2, 'Farmer One', 'farmer1@souq.com', '123456', 'farmer', '2026-04-11 15:36:22'),
(3, 'Farmer Two', 'farmer2@souq.com', '123456', 'farmer', '2026-04-11 15:36:22'),
(6, 'Haifa', 'haifathefirst@gmail.com', '$2y$10$T5mVPjC1TIZm00eMGnMp..Jq4lBskNcLGVnROIB6kFRULkiQC1Mre', 'customer', '2026-04-11 16:53:09'),
(7, 'haifa', 'haifatfirst@gmail.com', '$2y$10$6mabbYi6./rnQmQ363YVKOpCPQJuRx/ap7Hg5HzZGFkMtZhWBBRua', 'farmer', '2026-04-11 16:54:03'),
(8, 'Haifa', 'hirst@gmail.com', '$2y$10$OIMLRLLtCqW6LCu0VaMCU.COWyWnj2FEu3vSrbiJCh/avXjtXFAiu', 'customer', '2026-04-14 06:55:11'),
(9, 'Haifa', 'hefirst@gmail.com', '$2y$10$5JznUDY5lI2ccyKAgd3EeuXQdlwWJCoeVYJWmDCEFeDCeQKRrf5gO', 'farmer', '2026-04-14 07:00:04'),
(10, 'Haifa', 'h1@gmail.com', '$2y$10$Y38wTWufVPDxGMP.tugbcu4iFcKLVr2AxHfO9HMLSj277X1Kpx5Ii', 'farmer', '2026-04-14 07:02:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `farms`
--
ALTER TABLE `farms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farmer_user_id` (`farmer_user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

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
-- AUTO_INCREMENT for table `farms`
--
ALTER TABLE `farms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `farms`
--
ALTER TABLE `farms`
  ADD CONSTRAINT `farms_ibfk_1` FOREIGN KEY (`farmer_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
