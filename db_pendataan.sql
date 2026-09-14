-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2026 at 07:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pendataan`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_pendataan`
--

CREATE TABLE `data_pendataan` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `sekolah` varchar(100) NOT NULL,
  `nomor_whatsapp` varchar(20) NOT NULL,
  `kota` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_pendataan`
--

INSERT INTO `data_pendataan` (`id`, `id_user`, `tanggal`, `nama`, `kelas`, `sekolah`, `nomor_whatsapp`, `kota`) VALUES
(2, 2, '2026-09-14', 'Rifqi', 'X 2', 'Sekolah 4', '08991227766', 'Banjarbaru'),
(3, 1, '2026-09-14', 'Tester', 'X ', 'Sekolah', '0843214321', 'Jakarta'),
(4, 3, '2026-09-14', 'Halim', 'XII IPS 3', 'SMAN 2 Jakarta', '082112341234', 'Jakarta'),
(5, 3, '2026-09-21', 'Siti Aisyah', 'XII IPS 5', 'SMAN 6 Bekasi Timur', '082112341234', 'Bekasi');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai') NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `nomor_whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kode_referral` varchar(20) DEFAULT NULL,
  `referred_by` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `nomor_whatsapp`, `email`, `alamat`, `kota`, `provinsi`, `kode_referral`, `referred_by`) VALUES
(1, 'admin', '$2y$10$gWN4mvIq8ytDVKj8iFHMDeAK8V0QmZhluRSvGSSS2txpojdm80tZ6', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 'admin', NULL),
(2, 'rifqi', '$2y$10$8P1kYqIyRHRs5EnsoZ.Jpe1eS/8YvDgyk7vvJ0YJ45tX5qofJ/ZUW', 'pegawai', 'Rifqi Anshari', '082112345678', 'rifqianshari@email.com', 'Banjarbaru anjay', 'Banjarbaru', 'Kalimantan Selatan', 'rifqi', NULL),
(3, 'dior', '$2y$10$oCGemwbjs64.eqSZdQdDu.uQVzZIeC4cphVo.vGv6rZXmiDsnCa2m', 'pegawai', NULL, NULL, NULL, NULL, NULL, NULL, 'dior', NULL),
(4, 'jerry', '$2y$10$oCtBLMvWd1SNx363qQIN9uI/HO0NwVFmO2eGQdwu9tiATDwD7/jXi', 'pegawai', 'Jerry', NULL, NULL, NULL, NULL, NULL, 'jerry', 'rifqi');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_pendataan`
--
ALTER TABLE `data_pendataan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_referral` (`kode_referral`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data_pendataan`
--
ALTER TABLE `data_pendataan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
