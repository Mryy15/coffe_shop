-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 06:40 AM
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
-- Database: `coffee_shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `image_url`, `created_at`, `category`) VALUES
(11, 'Caramel Macchiato', 'Kopi dengan rasa karamel yang manis dan creamy', 32000.00, 'https://images.unsplash.com/photo-1561047029-3000c68339ca?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:03:22', 'kopi'),
(12, 'Mocha Delight', 'Perpaduan coklat dan kopi yang sempurna', 28000.00, 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:03:22', 'kopi'),
(13, 'Americano Classic', 'Kopi hitam klasik yang kuat dan menyegarkan', 22000.00, 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:03:22', 'kopi'),
(14, 'Vanilla Cold Brew', 'Cold brew dengan esens vanilla yang lembut', 27000.00, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:03:22', 'kopi'),
(15, 'Hazelnut Latte', 'Latte dengan rasa hazelnut yang khas', 30000.00, 'https://images.unsplash.com/photo-1511537190424-bbbab87ac5eb?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:03:22', 'kopi'),
(16, 'Iced Matcha Coffee', 'Perpaduan matcha dan kopi yang unik', 33000.00, 'https://images.unsplash.com/photo-1567241566621-17c3c5c97b8c?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:03:22', 'kopi'),
(17, 'Matcha Latte', 'Creamy', 20000.00, 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:06:30', 'non-kopi'),
(18, 'cookies matcha', 'soft cookies', 15000.00, 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=400&q=80', '2025-12-24 02:30:44', 'snack');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `order_type` enum('dine_in','takeaway') DEFAULT 'dine_in',
  `payment_method` enum('cash','qris') DEFAULT 'cash',
  `total_amount` int(11) NOT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `order_type`, `payment_method`, `total_amount`, `status`, `notes`, `order_date`) VALUES
(1, 'ORD-20251224040230-2', 1, 'viola dwi', 'dine_in', 'cash', 15000, 'pending', '', '2025-12-24 03:02:30'),
(2, 'ORD-20251224110957-3', 1, 'Pengguna', 'dine_in', 'cash', 37000, 'preparing', '', '2025-12-24 10:09:57'),
(3, 'ORD-20260107113045-3', 14, 'rinal', 'takeaway', 'cash', 57000, 'pending', '', '2026-01-07 10:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_name`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 'cookies matcha', 1, 15000, 15000),
(2, 2, 'cookies matcha', 1, 15000, 15000),
(3, 2, 'Americano Classic', 1, 22000, 22000),
(4, 3, 'Americano Classic', 1, 22000, 22000),
(5, 3, 'cookies matcha', 1, 15000, 15000),
(6, 3, 'Matcha Latte', 1, 20000, 20000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `phone`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '08123456789', 'admin@coffee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-12-23 18:42:35'),
(3, 'testuser', '08111111111', 'test@coffee.com', '$2y$10$G/qaOhDEMHwRt..4Mn5H5.oTAZyTwsvOUujESBXr4JgjZb0e5KoeK', 'user', '2025-12-23 19:10:57'),
(4, 'test1', '081236893245', 'coba@example.com', '$2y$10$KlUxcego/vjtWB7t4H8UIOkG0D.yOu9zoI5WLO4.zQLswj6IU3Kly', 'user', '2025-12-23 19:32:52'),
(5, 'diah anggraini', '081977482146', 'ddxxrr160705@gmail.com', 'Diah121416', 'user', '2025-12-23 19:53:08'),
(6, 'Jeje Dodo', '12345678910', 'test@extest.com', '$2y$10$m8MKKp8NYRU4pp6rZYqLR.i6yni3waPP5Q9A1KjikX7s7iLvdv5yK', 'user', '2025-12-23 21:06:21'),
(7, 'Jeje Dofio', '12345678919', 'tust@extest.com', '$2y$10$mKXgQnmdAqsjA4OOlX6R4eIvDv2aJGEV7orrVeQiVfJ5C39NV.N2u', 'user', '2025-12-23 21:10:14'),
(8, 'almut cantik', '44663377881', 'almut@gmail.com', '$2y$10$oyXZve2u3PYfRW8VfeTGY..XUev5Mw.stKKiOAg4i/3PNmi2TH6eS', 'user', '2025-12-23 23:09:24'),
(9, 'alya nazuwa', '081933456721', 'alyana@gmail.com', '$2y$10$saDmxiI98f4aPjtOQzckyOK1apc04G.ZIk3A01j.c5uaRnr6LV/Iy', 'user', '2025-12-23 23:24:24'),
(10, 'amel putri', '089765456732', 'amelputri@gmail.com', '$2y$10$Qmqi.G4Dj4TTzESB.KQx.ujBuqlpvSRxcnLJTJWuRU7PCj01HlVGS', 'user', '2025-12-23 23:40:27'),
(11, 'viola dwi', '756482916574', 'viocomel@gmail.com', '$2y$10$fM7KycX/ne7mWHH.AmgfNeTVoxU7O.WAOikfm7MQqjJ8BS1rWjMQm', 'user', '2025-12-23 23:43:45'),
(12, 'adiba', '243536776853', 'dibacute@gmail.com', '$2y$10$cjHDc2fn6RfgnfOWMWSXi.CxjcfJfpVjc/hRc5EtTtg/.AfUygZpC', 'user', '2025-12-24 03:10:55'),
(13, 'diah', '081977482146', 'diahmakan@gmail.com', '$2y$10$WfCt4nMEO/QJxrOt1U2DBOReveFW88EdHE.GxmPPk86YWJATTvtGG', 'user', '2026-01-07 09:51:02'),
(14, 'rinal', '08123456121', 'rinal@gmail.com', '$2y$10$yMzRtuJPWBxXHeaKcTWPN.4NQmi1LFWlxuTRy/BF3dKXPLsa.9JNy', 'user', '2026-01-07 10:02:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
