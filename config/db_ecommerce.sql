-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 26, 2025 at 07:54 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `alamat_pengiriman` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `tanggal`, `status`, `total`, `alamat_pengiriman`) VALUES
(33, 8, '2025-10-23 14:01:10', 'Selesai', '25000.00', 'jalan jalan'),
(35, 8, '2025-10-23 14:50:46', 'Menunggu Pembayaran', '9000.00', NULL),
(36, 12, '2025-10-30 14:14:27', 'Dibatalkan', '21000.00', NULL),
(37, 8, '2025-11-11 14:12:58', 'Sedang Diproses', '15000.00', 'nolhjo'),
(38, 8, '2025-12-01 03:17:09', 'Sedang Diproses', '37000.00', 'njdoafh;'),
(39, 8, '2025-12-01 03:51:42', 'Belum Bayar', '25000.00', NULL),
(40, 8, '2025-12-01 03:52:58', 'Selesai', '25000.00', 'loonjib'),
(41, 8, '2025-12-01 16:03:30', 'Selesai', '18000.00', 'pojojsdnij'),
(43, 15, '2025-12-03 08:06:04', 'Selesai', '9000.00', NULL),
(44, 16, '2025-12-03 09:26:02', 'Selesai', '12000.00', 'jl. melati no 10'),
(45, 8, '2025-12-11 12:49:04', 'Sedang Diproses', '15000.00', 'jl abu abu'),
(46, 8, '2025-12-11 14:10:51', 'Menunggu Pembayaran', '5000.00', NULL),
(47, 8, '2025-12-11 14:14:07', 'Menunggu Pembayaran', '5000.00', NULL),
(48, 8, '2025-12-11 14:16:40', 'Sedang Diproses', '15000.00', 'sfjngb'),
(49, 19, '2025-12-24 20:20:52', 'Selesai', '4000.00', 'Jalan Proklamasi'),
(50, 19, '2025-12-25 19:27:46', 'Menunggu Konfirmasi Admin', '15000.00', 'Test');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `harga`) VALUES
(33, 33, 7, 1, '25000.00'),
(35, 35, 5, 1, '9000.00'),
(36, 36, 5, 1, '9000.00'),
(37, 36, 3, 1, '12000.00'),
(38, 37, 2, 1, '15000.00'),
(39, 38, 7, 1, '25000.00'),
(40, 38, 3, 1, '12000.00'),
(41, 39, 7, 1, '25000.00'),
(42, 40, 7, 1, '25000.00'),
(43, 41, 5, 2, '9000.00'),
(45, 43, 5, 1, '9000.00'),
(46, 44, 3, 1, '12000.00'),
(47, 45, 8, 1, '15000.00'),
(48, 46, 9, 1, '5000.00'),
(49, 47, 9, 1, '5000.00'),
(50, 48, 8, 1, '15000.00'),
(51, 49, 15, 1, '2000.00'),
(52, 49, 16, 1, '2000.00'),
(53, 50, 8, 1, '15000.00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `jumlah` decimal(15,2) DEFAULT NULL,
  `vendor_pengirim` varchar(50) DEFAULT NULL,
  `resi` varchar(100) DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `metode`, `status`, `tanggal`, `jumlah`, `vendor_pengirim`, `resi`, `bukti`) VALUES
(1, NULL, 'transfer', 'completed', '2025-10-21 13:30:06', '15000.00', NULL, NULL, NULL),
(2, NULL, 'transfer', 'completed', '2025-10-21 17:51:44', '35000.00', NULL, NULL, NULL),
(44, 33, 'Transfer Bank', 'Diterima', '2025-10-23 19:01:39', '25000.00', 'Gojek', 'RESI-20251023140139-6628D4', 'uploads/bukti/bukti_1761220899.png'),
(45, 37, 'Transfer Bank', 'Diterima', '2025-11-11 20:13:15', '15000.00', 'Gojek', 'RESI-20251111141315-3396B2', 'uploads/bukti/bukti_1762866795.png'),
(46, 38, 'Transfer Bank', 'Diterima', '2025-12-01 09:17:43', '37000.00', 'Gojek', 'RESI-20251201031743-4D7E9A', 'uploads/bukti/bukti_1764555463.jpg'),
(47, 40, 'Transfer Bank', 'Diterima', '2025-12-01 09:53:49', '25000.00', 'JNE', 'RS202512013210AD', 'uploads/bukti/bukti_1764557629.jpg'),
(48, 41, 'QRIS', 'Diterima', '2025-12-01 22:04:09', '18000.00', 'JNE', 'RS20251201EAE128', 'uploads/bukti/bukti_1764601449.jpg'),
(49, 44, 'QRIS', 'Diterima', '2025-12-03 15:26:33', '12000.00', 'JNE', 'RS20251203E8F306', 'uploads/bukti/bukti_1764750393.png'),
(50, 45, 'QRIS', 'Diterima', '2025-12-11 18:50:02', '15000.00', 'JNE', 'RS202512118B792B', 'uploads/bukti/bukti_1765453802.png'),
(51, 48, 'QRIS', 'Diterima', '2025-12-11 20:18:37', '15000.00', 'JNE', 'RS20251211CE1D8C', 'uploads/bukti/bukti_1765459117.png'),
(52, 49, 'Cash', 'Diterima', '2025-12-25 02:21:09', '4000.00', 'JNE', 'RS202512244296AB', ''),
(53, 50, 'Cash', 'Menunggu Konfirmasi Admin', '2025-12-26 01:28:04', '15000.00', 'Gojek', 'RESI-20251225192804-E1DB47', ''),
(54, 50, 'Cash', 'Menunggu Konfirmasi Admin', '2025-12-26 01:30:02', '15000.00', 'Gojek', 'RESI-20251225193002-F996B4', '');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `modal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `nama`, `deskripsi`, `harga`, `stok`, `kategori_id`, `gambar`, `modal`) VALUES
(2, 'keripik singkong', NULL, '15000.00', 2, NULL, '1761036193_Foto produk_keripik pangsissst-116 - Copy.JPG', '12000.00'),
(3, 'makaroni goreng pedas', NULL, '12000.00', 8, NULL, '1761040446_Foto produk_keripik pangsissst-088.JPG', '10000.00'),
(5, 'keripik pangsit original', NULL, '9000.00', 2, NULL, '1761121259_Foto produk_keripik pangsissst-140 - Copy.JPG', '5000.00'),
(7, 'Mix (makaroni, keripik pangsit, & keripik singkong)', NULL, '25000.00', 7, NULL, '1761197851_Foto produk_keripik pangsissst-057.JPG', '18000.00'),
(8, 'Basreng bu neymar', NULL, '15000.00', 15, NULL, '1761829888_Foto produk_keripik pangsissst-140.JPG', '12000.00'),
(9, 'keripik pangsit OG', NULL, '5000.00', 8, NULL, '1765458527_Foto produk_keripik pangsissst-139 - Copy.JPG', '3000.00'),
(15, 'Kerupuk Putih', NULL, '2000.00', 99, NULL, '1766603460_kerupuk_putih.jpg', '1000.00'),
(16, 'Kerupuk Coklat', NULL, '2000.00', 99, NULL, '1766603484_kerupuk_coklat.jpeg', '1000.00');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `biaya` decimal(15,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(7, 'Admin', 'admin@gmail.com', '$2y$10$7woj11VOaHZERPmgNKk.be9spvNT68GBpJVDXcetYrk3fsib2b3Ia', 'admin', '2025-10-20 13:41:48'),
(8, 'nehan', 'nehan@gmail.com', '$2y$10$Gq0K0FDn08LXIE1GrYcXZelYN85JivhCDYNhOWAU6nZ3.pUqBtFnC', 'customer', '2025-10-20 13:42:18'),
(10, 'fathur', 'fathur@gmail.com', '$2y$10$WCabmUe5a2k5/RTcZ5ShUOoOEbmmjEQ/eiy30S/8q//WHalqOTjjO', 'customer', '2025-10-21 11:35:20'),
(12, 'neymar', 'neymar@gmail.com', '$2y$10$3fXIFxOhKuJWYLx86EVZU./Nfu3z4jR0yQWNQqgKk5zFOyaDyI0g6', 'customer', '2025-10-30 13:06:37'),
(15, 'Rianu Pakih', 'pakih@gmail.com', '$2y$10$Ej6Qae4T19VeTtwmhwyHaejvC6JTA.eykqfFe1mUyJooWw1WPQ7TS', 'customer', '2025-12-03 04:54:56'),
(16, 'Neyna', 'neyna@gmail.com', '$2y$10$C3iKNfsqVIssU2TQle2gG.BmElM17HU5sagTcvFSYU/Kc7rZxhNJm', 'customer', '2025-12-03 08:25:37'),
(17, 'admin2', 'admin2@gmail.com', '$2y$10$ghaRWCllScPehS6BHAOV2e8QGoVSsSDYbrZtrEP1RKbWyxA.VFTTS', 'admin', '2025-12-11 12:17:34'),
(18, 'MuhFikriHadian', 'fikri@gmail.com', '$2y$10$7rYbNyKf1OYFaOS8kDta7.wv9qUfrjthTm/Zm4PWS5OHh4KEGYda.', 'admin', '2025-12-24 17:50:34'),
(19, 'Hadian', 'hadian@gmail.com', '$2y$10$Gh2doLMmZzsZw2Mr.0mpbelXg/Lau2wcd1XHFt0UhfzVbTkwqQlW2', 'customer', '2025-12-24 19:13:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_users` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
