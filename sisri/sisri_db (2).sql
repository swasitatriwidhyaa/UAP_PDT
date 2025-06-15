-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2025 at 10:35 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sisri_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ApproveBookingAndSetRoomStatus` (IN `booking_id_param` INT)   BEGIN
    DECLARE v_room_id INT;
    DECLARE v_current_status VARCHAR(50);

    -- Dapatkan detail pemesanan dan status saat ini
    SELECT room_id, status INTO v_room_id, v_current_status
    FROM bookings
    WHERE id = booking_id_param;

    -- Lanjutkan hanya jika pemesanan ditemukan dan statusnya 'pending'
    IF v_room_id IS NOT NULL AND v_current_status = 'pending' THEN
        -- Mulai transaksi database secara implisit di dalam prosedur
        START TRANSACTION;

        -- Perbarui status pemesanan
        UPDATE bookings
        SET status = 'confirmed'
        WHERE id = booking_id_param;

        -- Perbarui status ketersediaan indekos
        UPDATE indekos
        SET status_ketersediaan = 'terisi'
        WHERE id = v_room_id;

        -- Commit transaksi
        COMMIT;
    ELSE
        -- Rollback atau tangani kasus jika tidak memenuhi syarat
        -- Untuk prosedur, biasanya Anda akan menggunakan kondisi dan keluar
        -- atau mengembalikan kode error. Di sini, kita tidak melakukan apa-apa jika tidak pending/ditemukan.
        -- Contoh: SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pemesanan tidak valid atau sudah diproses.';
        ROLLBACK; -- Jika ada START TRANSACTION, selalu ada ROLLBACK jika IF gagal.
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `bookRoom` (IN `room_id` INT, IN `user_id` INT, IN `start_date` DATE, IN `end_date` DATE, OUT `total_price` DECIMAL(10,2))   BEGIN
    DECLARE price DECIMAL(10,2);

    -- Memulai transaksi
    START TRANSACTION;

    -- Mengambil harga per jam untuk ruangan
    SELECT harga INTO price FROM indekos WHERE id = room_id;

    -- Jika harga tidak ditemukan, set total_price ke NULL dan batalkan transaksi
    IF price IS NULL THEN
        SET total_price = NULL;
        ROLLBACK;  -- Batalkan transaksi jika harga tidak ditemukan
    ELSE
        -- Menghitung total harga berdasarkan durasi pemesanan
        SET total_price = price * DATEDIFF(end_date, start_date);
        
        -- Jika total_price dihitung dengan benar, lanjutkan transaksi dan sisipkan data pemesanan
        IF total_price IS NOT NULL THEN
            -- Memasukkan data pemesanan ke dalam tabel bookings
            INSERT INTO bookings (user_id, room_id, start_date, end_date, total_price)
            VALUES (user_id, room_id, start_date, end_date, total_price);
            COMMIT;  -- Commit transaksi setelah pemesanan berhasil dimasukkan
        ELSE
            -- Jika total_price NULL, batalkan transaksi
            ROLLBACK;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RejectBooking` (IN `booking_id_param` INT)   BEGIN
    DECLARE v_current_status VARCHAR(50);

    -- Dapatkan status saat ini
    SELECT status INTO v_current_status
    FROM bookings
    WHERE id = booking_id_param;

    -- Lanjutkan hanya jika pemesanan ditemukan dan statusnya 'pending'
    IF v_current_status = 'pending' THEN
        -- Mulai transaksi
        START TRANSACTION;

        -- Perbarui status pemesanan
        UPDATE bookings
        SET status = 'rejected'
        WHERE id = booking_id_param;

        -- Opsional: Jika Anda mengubah status kamar jadi 'terisi' saat Approve,
        -- maka di sini Anda mungkin ingin mengubahnya kembali jadi 'tersedia'
        -- UPDATE indekos SET status_ketersediaan = 'tersedia' WHERE id = (SELECT room_id FROM bookings WHERE id = booking_id_param);

        -- Commit transaksi
        COMMIT;
    ELSE
        ROLLBACK;
    END IF;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateBookingDuration` (`start_date` DATE, `end_date` DATE) RETURNS INT DETERMINISTIC BEGIN
    DECLARE duration_in_days INT;
    SET duration_in_days = DATEDIFF(end_date, start_date);
    IF duration_in_days = 0 THEN
        SET duration_in_days = 1; -- Asumsi minimal 1 hari
    END IF;
    RETURN duration_in_days;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateBookingTotalPrice` (`room_id_param` INT, `start_date_param` DATE, `end_date_param` DATE) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE room_price DECIMAL(10, 2);
    DECLARE total_price DECIMAL(10, 2);

    SELECT harga INTO room_price FROM indekos WHERE id = room_id_param;

    IF room_price IS NULL THEN
        RETURN 0;
    END IF;

    SET total_price = room_price * CalculateBookingDuration(start_date_param, end_date_param);
    RETURN total_price;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `calculateTotalPrice` (`room_id` INT, `start_date` DATE, `end_date` DATE) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE price DECIMAL(10,2);
    DECLARE total_price DECIMAL(10,2);

    -- Mengambil harga per jam untuk ruangan
    SELECT harga INTO price FROM indekos WHERE id = room_id;

    -- Menghitung total harga jika harga ditemukan
    IF price IS NOT NULL THEN
        SET total_price = price * DATEDIFF(end_date, start_date);
    ELSE
        SET total_price = 0;  -- Jika harga tidak ditemukan, set harga total ke 0
    END IF;

    RETURN total_price;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','rejected') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `start_date`, `end_date`, `total_price`, `status`, `created_at`) VALUES
(16, 1, 1, '2025-06-15', '2025-08-15', '20000000.00', 'confirmed', '2025-06-15 09:22:32');

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `trg_update_ketersediaan_kamar` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    -- Jika status diubah menjadi 'confirmed'
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        UPDATE indekos
        SET status_ketersediaan = 'terisi'
        WHERE id = NEW.room_id;
    END IF;

    -- Jika status diubah menjadi 'rejected'
    IF NEW.status = 'rejected' AND OLD.status = 'confirmed' THEN
        UPDATE indekos
        SET status_ketersediaan = 'tersedia'
        WHERE id = NEW.room_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `indekos`
--

CREATE TABLE `indekos` (
  `id` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `deskripsi` text,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `indekos`
--

INSERT INTO `indekos` (`id`, `nama`, `harga`, `lokasi`, `deskripsi`, `gambar`, `created_at`, `updated_at`) VALUES
(1, 'indekos Sita', '10000000.00', 'Griya Permai', 'kost putri', '684d27df1c248.jpg', '2025-06-14 07:42:23', '2025-06-15 07:59:33'),
(5, 'kos neta', '150000.00', 'gedong meneng', 'bagus', '684e7e6ac7694.jpg', '2025-06-15 08:03:54', '2025-06-15 08:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `status` enum('pending','confirmed','canceled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 1, '2023-09-01', '2023-09-10', 'confirmed', '2025-06-14 06:36:42', '2025-06-14 06:36:42');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `harga` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `type`, `price`, `description`, `created_at`, `updated_at`, `harga`) VALUES
(1, 'Room A', 'Single', '100.00', 'A cozy single room', '2025-06-14 06:36:42', '2025-06-14 07:09:19', '200000.00'),
(2, 'Room B', 'Double', '150.00', 'A spacious double room', '2025-06-14 06:36:42', '2025-06-14 07:10:39', '1000000.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'sita', 'ab@gmail.com', '$2y$10$X8kbF7f9Qax.QSll7Zg5bOeRnhYzEoJUZLYpIzGZApISV4hGTDzP.', 'user', '2025-06-12 19:53:38'),
(2, 'neta', 'au@gmail.com', '$2y$10$NW5Y52/yW2Slyq28Z6rdce4vA2FHoSFqIg/2opck6UIav9dz0PFie', 'user', '2025-06-12 20:20:57'),
(3, 'memet', 'me@gmail.com', '$2y$10$9N0lJxlUba7b697L4zuZX.bY4gLuIAz3tDULX3CgzsqP7ZZaA1QMG', 'user', '2025-06-12 20:25:56'),
(4, 'metaa', 'metaa@gmail.com', '$2y$10$N07dXnCA5P0IOKu0tFjNUeUFu9LG6jz2xYDLh7SFkHsDMY0rK6ZFO', 'user', '2025-06-13 01:13:08'),
(5, 'admin', 'admin@example.com', '$2y$10$C.aZ6WmW6x9K0ri6DU4EiOq8G6UUQnXZYonvsq/9FyDotrZ4P/gQ.', 'admin', '2025-06-13 01:34:51'),
(6, 'netaa', 'net@gmail.com', '$2y$10$A594ikVsFbVPcArfB1VTf.wbGd81Qnttm4d3vUPW.599PFLXZyzrC', 'user', '2025-06-13 10:19:12'),
(7, 'sitaa', 'sitaa@unila.com', '$2y$10$hrWUz1SEn2..ljrCZF9eOe5zFtiK/P5EEeFLJ3iIKYHCaWR3ETLZK', 'user', '2025-06-14 05:51:46'),
(8, 'admin1', 'admin@unila.com', '$2y$10$hnlyuTUYGSDieZ6PFGZguuhrqElczO176QZ02xclpcTMq4/.hy2Yq', 'admin', '2025-06-14 14:10:55'),
(9, 'aku', 'a@gmail.com', '$2y$10$bFyoov1.OuSraG4gS3e3BOiNEpbrKdTeNmTuoXOat5tSOMdpk773a', 'user', '2025-06-15 09:18:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `indekos`
--
ALTER TABLE `indekos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `indekos`
--
ALTER TABLE `indekos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `indekos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `indekos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
