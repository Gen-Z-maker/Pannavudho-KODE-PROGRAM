-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2025 at 01:19 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_pupuk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int NOT NULL,
  `transaksi_id` int NOT NULL,
  `produk_id` int DEFAULT NULL,
  `kuantitas` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `produk_id`, `kuantitas`, `harga_satuan`, `subtotal`) VALUES
(6, 30, 1, 50.00, 50000.00, 2500000.00),
(7, 31, 1, 50.00, 45000.00, 2250000.00),
(8, 32, 1, 50.00, 50000.00, 2500000.00),
(9, 33, 1, 50.00, 45000.00, 2250000.00),
(10, 34, 1, 50.00, 50000.00, 2500000.00),
(11, 35, 1, 50.00, 45000.00, 2250000.00),
(12, 36, 1, 50.00, 50000.00, 2500000.00),
(13, 37, 1, 50.00, 50000.00, 2500000.00),
(14, 38, 1, 1000.00, 50000.00, 50000000.00),
(15, 39, 1, 50.00, 45000.00, 2250000.00),
(16, 40, 1, 50.00, 45000.00, 2250000.00),
(17, 41, 7, 1.00, 16000.00, 16000.00),
(18, 42, 1, 200.00, 50000.00, 10000000.00),
(19, 42, 7, 7.00, 16000.00, 112000.00),
(20, 43, 7, 1000.00, 16000.00, 16000000.00),
(21, 44, 7, 900.00, 16000.00, 14400000.00),
(22, 45, 7, 99.00, 16000.00, 1584000.00),
(23, 46, 7, 1000.00, 16000.00, 16000000.00),
(24, 47, 6, 200.00, 23000.00, 4600000.00),
(25, 48, 6, 250.00, 23000.00, 5750000.00),
(26, 49, 1, 10.00, 50000.00, 500000.00),
(27, 50, 1, 10.00, 45000.00, 450000.00),
(28, 51, 6, 1.00, 23000.00, 23000.00);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_pengambilan`
--

CREATE TABLE `jadwal_pengambilan` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `produk_id` int NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'KG',
  `deskripsi` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jadwal_pengambilan`
--

INSERT INTO `jadwal_pengambilan` (`id`, `tanggal`, `produk_id`, `jumlah`, `satuan`, `deskripsi`, `alamat`, `created_at`) VALUES
(3, '2025-11-25', 5, 10.00, 'Sak', 'pengiriman pupuk', 'desa tabuan', '2025-11-17 02:26:05'),
(4, '2025-11-29', 6, 15.00, 'Tabung', 'pengambilan gas', 'desa paringin selatan', '2025-11-20 11:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Pupuk NPK', 'Pupuk NPK untuk berbagai jenis tanaman'),
(2, 'Pupuk Organik', 'Pupuk organik alami'),
(3, 'Pupuk Urea', 'Pupuk urea untuk pertumbuhan'),
(4, 'Pupuk TSP', 'Pupuk TSP untuk pembungaan'),
(5, 'Pupuk KCL', 'Pupuk KCL untuk ketahanan'),
(6, 'Gas', 'GAs Elpiji 3 KG');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int NOT NULL,
  `kode_produk` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `deskripsi` text,
  `kategori_id` int DEFAULT NULL,
  `harga_beli` decimal(10,2) NOT NULL,
  `harga_jual` decimal(10,2) NOT NULL,
  `stok` int DEFAULT '0',
  `satuan` varchar(20) DEFAULT 'kg',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kode_produk`, `nama_produk`, `deskripsi`, `kategori_id`, `harga_beli`, `harga_jual`, `stok`, `satuan`, `status`) VALUES
(1, 'NPK001', 'Pupuk NPK 16-16-16', 'Pupuk NPK untuk tanaman padi, jagung, dan sayuran', 1, 45000.00, 50000.00, 700, 'kg', 'aktif'),
(2, 'ORG001', 'Pupuk Organik Kompos', 'Pupuk organik dari kompos alami', 2, 25000.00, 35000.00, 80, 'kg', 'aktif'),
(3, 'URE001', 'Pupuk Urea 46%', 'Pupuk urea untuk pertumbuhan tanaman', 3, 40000.00, 45000.00, 150, 'kg', 'aktif'),
(4, 'TSP001', 'Pupuk TSP 46%', 'Pupuk TSP untuk pembungaan dan pembuahan', 4, 42000.00, 47000.00, 75, 'kg', 'aktif'),
(5, 'KCL001', 'Pupuk KCL 60%', 'Pupuk KCL untuk ketahanan tanaman', 5, 38000.00, 43000.00, 90, 'kg', 'aktif'),
(6, 'LPG3KG', 'Gas elpiji 3 kg', 'Tabung gas elpiji 3 kg', 6, 0.00, 23000.00, 49, 'tabung', 'aktif'),
(7, '001', 'Pupuk Kapur', 'pupuk kapur untuk pembasmi hama', 1, 12000.00, 16000.00, 1000, 'sak', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('pemasukan','pengeluaran') NOT NULL,
  `kategori_transaksi` varchar(50) DEFAULT NULL,
  `deskripsi` text,
  `total` decimal(10,2) NOT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `tanggal`, `jenis`, `kategori_transaksi`, `deskripsi`, `total`, `user_id`) VALUES
(30, 'TRX20250710075124', '2025-07-10', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 50kg', 2500000.00, 9),
(31, 'TRX20250710075159', '2025-07-10', 'pengeluaran', 'Pembelian', 'pembelian', 2250000.00, 9),
(32, 'TRX20250710080349', '2025-07-10', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 50kg', 2500000.00, 9),
(33, 'TRX20250710080413', '2025-07-10', 'pengeluaran', 'Pembelian', 'pembelian', 2250000.00, 9),
(34, 'TRX20250710080450', '2025-08-01', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 50kg', 2500000.00, 9),
(35, 'TRX20250710083827', '2025-07-10', 'pengeluaran', 'Pembelian', 'pembelian', 2250000.00, 9),
(36, 'TRX20250710083937', '2025-07-10', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 50kg', 2500000.00, 9),
(37, 'TRX20250710093001', '2025-07-10', 'pemasukan', 'Penjualan', 'pembelian', 2500000.00, 9),
(38, 'TRX20250813095157', '2025-08-13', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 1000 kg', 50000000.00, 9),
(39, 'TRX20250813095241', '2025-08-13', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 50kg', 2250000.00, 9),
(40, 'TRX20250813095355', '2025-08-13', 'pengeluaran', 'Pembelian', 'penjualan pupuk npk 50kg', 2250000.00, 9),
(41, 'TRX20251020070954', '2025-10-20', 'pemasukan', 'Penjualan', 'pembelian', 16000.00, 9),
(42, 'TRX20251024031025', '2025-10-24', 'pemasukan', 'Penjualan', 'penjualan pupuk npk 50kg', 10112000.00, 10),
(43, 'TRX20251024060428', '2025-10-24', 'pengeluaran', 'Pembelian', 'pembelian', 16000000.00, 9),
(44, 'TRX20251024060552', '2025-10-24', 'pemasukan', 'Penjualan', 'penjualan', 14400000.00, 9),
(45, 'TRX20251024060740', '2025-10-24', 'pemasukan', 'Penjualan', 'penjualan', 1584000.00, 10),
(46, 'TRX20251107092204', '2025-11-07', 'pengeluaran', 'Pembelian', 'pembelian', 16000000.00, 9),
(47, 'TRX20251107092305', '2025-11-07', 'pemasukan', 'Penjualan', 'pembelian', 4600000.00, 9),
(48, 'TRX20251107092337', '2025-11-07', 'pengeluaran', 'Pembelian', 'pembelian', 5750000.00, 9),
(49, 'TRX20251120111014', '2025-11-20', 'pemasukan', 'Penjualan', 'penjuial pupuk npk', 500000.00, 9),
(50, 'TRX20251120111103', '2025-11-20', 'pengeluaran', 'Pembelian', 'pembelian pupuk npk', 450000.00, 9),
(51, 'TRX20251120111755', '2025-11-20', 'pemasukan', 'Penjualan', 'penjualan gas', 23000.00, 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
(9, 'pengelola', 'Pengelola@1', 'Administrator', 'pengelola@tokopupuk.com', 'admin'),
(10, 'karyawan', 'karyawan1', 'karyawan', 'karyawan@tokopupuk.com', 'kasir');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `jadwal_pengambilan`
--
ALTER TABLE `jadwal_pengambilan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `jadwal_pengambilan`
--
ALTER TABLE `jadwal_pengambilan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `jadwal_pengambilan`
--
ALTER TABLE `jadwal_pengambilan`
  ADD CONSTRAINT `jadwal_pengambilan_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
