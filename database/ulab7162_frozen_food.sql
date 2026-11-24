-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 24 Nov 2026 pada 10.03
-- Versi server: 10.11.14-MariaDB-cll-lve
-- Versi PHP: 8.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `ulab7162_frozen_food`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `membeli`
--

CREATE TABLE `membeli` (
  `id_membeli` int(11) NOT NULL,
  `kode_pesanan` varchar(20) NOT NULL,
  `id_pembeli` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `tgl_pesanan` datetime NOT NULL,
  `metode_bayar` enum('qris','ewallet') NOT NULL,
  `status` enum('menunggu_bayar','menunggu_konfirmasi','diproses','selesai','batal','kedaluwarsa') NOT NULL DEFAULT 'menunggu_bayar',
  `nama_penerima` varchar(100) NOT NULL,
  `email_penerima` varchar(100) NOT NULL,
  `no_wa_penerima` varchar(20) NOT NULL,
  `catatan` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `membeli`
--

INSERT INTO `membeli` (`id_membeli`, `kode_pesanan`, `id_pembeli`, `id_produk`, `jumlah`, `harga_satuan`, `tgl_pesanan`, `metode_bayar`, `status`, `nama_penerima`, `email_penerima`, `no_wa_penerima`, `catatan`) VALUES
(1, 'ORD-261120-0001', 2, 2, 1, 13000.00, '2026-11-20 09:58:08', 'qris', 'selesai', 'RIDHO KURNIAWAN', 'ridhokrnwn80@gmail.com', '087840889115', NULL),
(2, 'ORD-261120-0002', 3, 2, 1, 13000.00, '2026-11-20 10:20:44', 'ewallet', 'selesai', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(3, 'ORD-261120-0002', 3, 3, 1, 35000.00, '2026-11-20 10:20:44', 'ewallet', 'selesai', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(4, 'ORD-261120-0003', 3, 3, 1, 35000.00, '2026-11-20 10:22:15', 'ewallet', 'menunggu_konfirmasi', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(5, 'ORD-261120-0004', 4, 3, 1, 35000.00, '2026-11-20 10:23:52', 'qris', 'menunggu_konfirmasi', 'aliza', 'aliza@gmail.com', '08549312785', NULL),
(6, 'ORD-261120-0005', 5, 2, 1, 13000.00, '2026-11-20 10:23:57', 'qris', 'kedaluwarsa', 'Ridho Kurniawan', 'kodokdhodho@gmail.com', '087840889115', NULL),
(7, 'ORD-261120-0005', 5, 3, 1, 35000.00, '2026-11-20 10:23:57', 'qris', 'kedaluwarsa', 'Ridho Kurniawan', 'kodokdhodho@gmail.com', '087840889115', NULL),
(8, 'ORD-261120-0006', 5, 3, 1, 35000.00, '2026-11-20 10:24:42', 'qris', 'kedaluwarsa', 'Ridho Kurniawan', 'kodokdhodho@gmail.com', '087840889115', NULL),
(9, 'ORD-261120-0006', 5, 2, 1, 13000.00, '2026-11-20 10:24:42', 'qris', 'kedaluwarsa', 'Ridho Kurniawan', 'kodokdhodho@gmail.com', '087840889115', NULL),
(10, 'ORD-261120-0007', 6, 1, 1, 48000.00, '2026-11-20 11:23:59', 'qris', 'menunggu_konfirmasi', 'Romi Hitam', 'romi@gmail.com', '0812345678900', NULL),
(11, 'ORD-261120-0007', 6, 3, 4, 35000.00, '2026-11-20 11:23:59', 'qris', 'menunggu_konfirmasi', 'Romi Hitam', 'romi@gmail.com', '0812345678900', NULL),
(12, 'ORD-261120-0008', 7, 1, 1, 48000.00, '2026-11-20 11:44:37', 'qris', 'selesai', 'Satria Hitam LEGAM', 'satria@gmail.com', '0812354756756', 'cod ya mas'),
(13, 'ORD-261120-0008', 7, 3, 1, 35000.00, '2026-11-20 11:44:37', 'qris', 'selesai', 'Satria Hitam LEGAM', 'satria@gmail.com', '0812354756756', 'cod ya mas'),
(14, 'ORD-261120-0008', 7, 2, 1, 13000.00, '2026-11-20 11:44:37', 'qris', 'selesai', 'Satria Hitam LEGAM', 'satria@gmail.com', '0812354756756', 'cod ya mas'),
(15, 'ORD-261120-0009', 1, 1, 1, 48000.00, '2026-11-20 21:32:45', 'qris', 'kedaluwarsa', 'Admin Mlandang', 'admin@mlandang.test', '081200000000', NULL),
(16, 'ORD-261123-0010', 3, 1, 3, 48000.00, '2026-11-23 11:49:09', 'ewallet', 'menunggu_konfirmasi', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(17, 'ORD-261123-0011', 3, 2, 1, 13000.00, '2026-11-23 15:53:45', 'qris', 'batal', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(18, 'ORD-261123-0011', 3, 3, 1, 35000.00, '2026-11-23 15:53:45', 'qris', 'batal', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(19, 'ORD-261123-0012', 3, 1, 1, 48000.00, '2026-11-23 15:54:02', 'ewallet', 'batal', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL),
(20, 'ORD-261123-0012', 3, 2, 2, 13000.00, '2026-11-23 15:54:02', 'ewallet', 'batal', 'Ahmad Ulaya', 'ulaya@gmail.com', '081256204220', NULL);

--
-- Trigger `membeli`
--
DELIMITER $$
CREATE TRIGGER `trg_cek_stok_membeli` BEFORE INSERT ON `membeli` FOR EACH ROW BEGIN
  DECLARE stok_sekarang INT;
  SELECT stok INTO stok_sekarang FROM produk WHERE id_produk = NEW.id_produk;
  IF stok_sekarang IS NULL OR NEW.jumlah > stok_sekarang THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok tidak cukup';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `role` enum('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama`, `email`, `password`, `no_wa`, `role`, `created_at`) VALUES
(1, 'Admin Mlandang', 'admin@mlandang.test', '$2y$10$36sq/newexAHvgZUWj5OWeVe8QQDMWIlhJwE/n1nSFq8l8ffvkiSS', '081200000000', 'admin', '2026-11-20 08:54:20'),
(2, 'RIDHO KURNIAWAN', 'ridhokrnwn80@gmail.com', '$2y$12$UtBjmlt65qGaF75b8nVZ2OrZgKeInfQrF9pBgMsrZErvMAnGDIli.', '087840889115', 'pelanggan', '2026-11-20 09:57:13'),
(3, 'Ahmad Ulaya', 'ulaya@gmail.com', '$2y$12$pQua.Tu9WaRnQObXSH0G.eMtvQHBQEpA0e6jY4kNSKKNofSrR/RD6', '081256204220', 'pelanggan', '2026-11-20 10:20:29'),
(4, 'aliza', 'aliza@gmail.com', '$2y$12$vYwHvE0Hz5.yKCSFLtJ6peLWjbQfcoFuPZNh8EyCTG34wY.kbUVAS', '08549312785', 'pelanggan', '2026-11-20 10:23:23'),
(5, 'Ridho Kurniawan', 'kodokdhodho@gmail.com', '$2y$12$yuH15ihwHQFdbVl65qL7/OoEY3zTCP0aMeH0k0PxD2bqHn/Zy0xRC', '087840889115', 'pelanggan', '2026-11-20 10:23:30'),
(6, 'Romi Hitam', 'romi@gmail.com', '$2y$12$jWdhgwXfXX0FsGySrn5dU./3AO3Z77XFAU.tLR0VynoRIvu2bXj3O', '0812345678900', 'pelanggan', '2026-11-20 11:23:38'),
(7, 'Satria Hitam LEGAM', 'satria@gmail.com', '$2y$12$4CpHif68oqQ1L1KMYKA9K.4heNIZyt2FPG5ylzqAhpqCbP7DM3g3y', '0812354756756', 'pelanggan', '2026-11-20 11:44:01'),
(8, 'sdfghj', 'fdrtdrdrt@gmail.com', '$2y$12$LB/Rx1.dC8.ka/aFuBX6BeMBXAwb7X4irHbtktDqfLgdp2b31VHWK', '087840889115', 'pelanggan', '2026-11-21 16:28:18'),
(9, 'abiyu nur', 'abiyu@gmail.com', '$2y$12$jL9vFT0X27dYeYnhOM9OZuWiK3.31SnNU9eI1DKM7aT7tCf.jG4Wa', '0854721685', 'pelanggan', '2026-11-23 10:21:28'),
(10, 'liza', 'liza@gmail.com', '$2y$12$2Xrmjm.6TDfYRztylOSgpuElkbmRHQSgWxKb2H0y528ZNaGlZKUWW', '0123346780654', 'pelanggan', '2026-11-23 11:00:17'),
(11, 'anjayy', 'anjayy@gmail.com', '$2y$12$XXjAY71b00PySqJXfbG5CuSUOHmngiG2JRgq/U2.Gy2l7VZ8YyLFu', '082112345678', 'pelanggan', '2026-11-23 14:35:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(120) NOT NULL,
  `kategori` varchar(60) NOT NULL,
  `merk` varchar(60) NOT NULL,
  `berat_gram` int(11) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `tgl_kadaluarsa` date DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(120) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `kategori`, `merk`, `berat_gram`, `harga`, `stok`, `tgl_kadaluarsa`, `deskripsi`, `gambar`, `status`) VALUES
(1, 'Nugget Ayam', 'Nugget Ayam & Olahan', 'Fiesta', 500, 48000.00, 10, '2028-11-04', 'Nugget olahan yang siap menemani hari hari kamu.', 'produk-729019370eae.png', 'aktif'),
(2, 'Del Monte Saus Barbeque', 'Aneka Saus & Bumbu', 'Del Monte', 500, 13000.00, 12, '2028-11-20', 'Saus barbeque pilihan keluarga, andalan anda', 'produk-8df49b70203a.png', 'aktif'),
(3, 'Kanzler Sosis Cheese Bratwurst', 'Sosis Daging & Olahan', 'Kanzler', 500, 35000.00, 15, '2028-01-20', 'Sosis keju dengan daging ayam dan sapi pilihan', 'produk-3e5851c5f7af.png', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `alamat` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `no_telp`, `alamat`) VALUES
(3, 'doni', '0986543271', 'nganjuk');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_beli` decimal(10,2) NOT NULL,
  `tgl_transaksi` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_supplier`, `id_produk`, `jumlah`, `harga_beli`, `tgl_transaksi`) VALUES
(1, 3, 3, 3, 13000.00, '2026-11-21 18:48:37'),
(2, 3, 2, 2, 23444.00, '2026-11-22 05:53:36'),
(3, 3, 3, 4, 12000.00, '2026-11-22 05:59:57');

--
-- Trigger `transaksi`
--
DELIMITER $$
CREATE TRIGGER `trg_stok_masuk` AFTER INSERT ON `transaksi` FOR EACH ROW BEGIN
  UPDATE produk SET stok = stok + NEW.jumlah WHERE id_produk = NEW.id_produk;
END
$$
DELIMITER ;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `membeli`
--
ALTER TABLE `membeli`
  ADD PRIMARY KEY (`id_membeli`),
  ADD KEY `idx_kode` (`kode_pesanan`),
  ADD KEY `id_pembeli` (`id_pembeli`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `id_produk` (`id_produk`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `membeli`
--
ALTER TABLE `membeli`
  MODIFY `id_membeli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id_pembeli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `membeli`
--
ALTER TABLE `membeli`
  ADD CONSTRAINT `membeli_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`),
  ADD CONSTRAINT `membeli_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
