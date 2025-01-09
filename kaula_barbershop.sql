-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Jan 2025 pada 18.01
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kaula_barbershop`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `nama_kapster` varchar(255) DEFAULT NULL,
  `status` enum('hadir','tidak hadir') DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `attendance`
--

INSERT INTO `attendance` (`id`, `nama_kapster`, `status`, `date`, `created_at`) VALUES
(1, 'asan', 'hadir', '2025-01-05', '2025-01-05 16:41:54'),
(2, '', 'hadir', '2025-01-05', '2025-01-05 16:54:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL,
  `nama_kapster` varchar(100) NOT NULL,
  `jenis_treatment` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `produk` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL DEFAULT 1,
  `product_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karyawan`
--

INSERT INTO `karyawan` (`id`, `nama_kapster`, `jenis_treatment`, `harga`, `produk`, `created_at`, `quantity`, `product_price`, `total_price`) VALUES
(1, 'asep', 'Special Service', 50000.00, 'Oil Based', '2025-01-04 13:43:52', 1, 65000.00, 65000.00),
(2, 'asep', 'Shaving', 10000.00, 'Hair Tonic', '2025-01-04 13:44:02', 1, 25000.00, 25000.00),
(3, 'asep', 'Shaving', 10000.00, 'Hair Tonic', '2025-01-04 13:48:25', 1, 25000.00, 25000.00),
(4, 'tatang', 'Special Service', 50000.00, 'Matte Clay', '2025-01-04 13:55:25', 8, 60000.00, 480000.00),
(5, 'tatang', 'Colouring Basic', 50000.00, 'Matte Clay', '2025-01-04 13:56:08', 2, 60000.00, 120000.00),
(6, 'tatang', 'Colouring Basic', 60000.00, 'Matte Clay', '2025-01-04 13:56:48', 1, 60000.00, 60000.00),
(7, 'hahehe', 'Colouring Basic', 50000.00, 'Water Based', '2025-01-04 14:22:28', 13, 60000.00, 780000.00),
(8, 'tatang', 'Shaving', 10000.00, 'Oil Based', '2025-01-05 00:15:19', 2, 65000.00, 130000.00),
(9, 'tatang', 'Shaving', 10000.00, 'Water Based', '2025-01-05 01:05:18', 1, 60000.00, 60000.00),
(10, '', '', 0.00, '', '2025-01-05 15:01:12', 1, 0.00, 0.00),
(11, 'asan', 'Shaving', 10000.00, 'Matte Clay', '2025-01-05 16:31:17', 1, 60000.00, 60000.00),
(12, '', 'Shaving', 10000.00, 'Water Based', '2025-01-05 16:54:12', 1, 60000.00, 60000.00),
(13, '', 'Shaving', 10000.00, 'Water Based', '2025-01-05 16:54:17', 1, 60000.00, 60000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `price`) VALUES
(1, 'Hair Tonic', 25000.00),
(2, 'Oil Based', 65000.00),
(3, 'Matte Clay', 60000.00),
(4, 'Water Based', 60000.00),
(5, 'Powder', 55000.00),
(6, 'Hair Vitamin', 5000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_starting_price` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `services`
--

INSERT INTO `services` (`service_id`, `category_id`, `service_name`, `description`, `price`, `is_starting_price`) VALUES
(1, 1, 'Haircuts', 'Hair wash + styling', 35000.00, 0),
(2, 1, 'Special Service', 'Hair wash + massage + styling', 50000.00, 0),
(3, 1, 'Shaving', NULL, 10000.00, 0),
(4, 1, 'Hair Wash', NULL, 15000.00, 0),
(5, 2, 'Colouring Basic', 'black, brown, dark brown', 50000.00, 1),
(6, 2, 'Colouring Fashion', 'silver, biru, hijau, dll', 250000.00, 1),
(7, 2, 'Highlight', NULL, 150000.00, 1),
(8, 3, 'Hair Mask', NULL, 50000.00, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `service_categories`
--

CREATE TABLE `service_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `service_categories`
--

INSERT INTO `service_categories` (`category_id`, `category_name`) VALUES
(1, 'Haircut Services'),
(2, 'Coloring Services'),
(3, 'Treatment Services');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shop_info`
--

CREATE TABLE `shop_info` (
  `shop_id` int(11) NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shop_info`
--

INSERT INTO `shop_info` (`shop_id`, `shop_name`, `phone_number`, `instagram`) VALUES
(1, 'KAULA BARBERSHOP', '087731704708', 'KAULABARBERSHOP');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama_kapster` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `nama_kapster`, `password`, `role`) VALUES
(1, 'user', 'user', '$2y$10$FcB0aIdTwAMsQjGH4dG0t.zBRa2e5U91AuZ/jp5rlsi/Hy/sKE1IC', 'kapster'),
(2, 'user2', 'user2', '$2y$10$n1qdsVSnmoqg6fKDw4Go8uW0Z8WCj6vb17fuq4cveM4QMYy94vEey', 'admin'),
(3, 'user3', 'asan', '$2y$10$R6YQcTsGIki1g/Gqb3xBZuI3O6Y.a0ko8Z8S8igx7n1B2VdnOO1re', 'kapster');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indeks untuk tabel `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indeks untuk tabel `shop_info`
--
ALTER TABLE `shop_info`
  ADD PRIMARY KEY (`shop_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `shop_info`
--
ALTER TABLE `shop_info`
  MODIFY `shop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`);

DELIMITER $$
--
-- Event
--
CREATE DEFINER=`root`@`localhost` EVENT `clear_attendance` ON SCHEDULE EVERY 1 DAY STARTS '2025-01-06 23:56:57' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM attendance WHERE DATE(date) < CURDATE();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
