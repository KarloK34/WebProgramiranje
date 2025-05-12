-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 01:09 PM
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
-- Database: `user`
--

-- --------------------------------------------------------

--
-- Table structure for table `ocjene`
--

CREATE TABLE `ocjene` (
  `id` int(11) NOT NULL,
  `id_korisnik` int(11) NOT NULL,
  `id_slika` int(11) NOT NULL,
  `ocjena` tinyint(4) NOT NULL,
  `vrijeme_ocjene` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ocjene`
--

INSERT INTO `ocjene` (`id`, `id_korisnik`, `id_slika`, `ocjena`, `vrijeme_ocjene`) VALUES
(1, 1, 1, 4, '2025-05-12 10:15:10'),
(2, 1, 5, 5, '2025-05-12 10:23:18'),
(3, 2, 5, 2, '2025-05-12 10:23:51'),
(4, 2, 1, 5, '2025-05-12 10:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `planned_trip`
--

CREATE TABLE `planned_trip` (
  `ID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `weather_data_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `planned_trip`
--

INSERT INTO `planned_trip` (`ID`, `user_id`, `weather_data_id`) VALUES
(4, 2, 3),
(5, 2, 1),
(8, 2, 6);

-- --------------------------------------------------------

--
-- Table structure for table `slike`
--

CREATE TABLE `slike` (
  `id` int(11) NOT NULL,
  `naziv_datoteke` varchar(255) NOT NULL,
  `putanja` varchar(255) NOT NULL,
  `opis` text DEFAULT NULL,
  `izvor` varchar(50) NOT NULL DEFAULT 'lokalno',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `api_image_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slike`
--

INSERT INTO `slike` (`id`, `naziv_datoteke`, `putanja`, `opis`, `izvor`, `uploaded_at`, `api_image_id`) VALUES
(1, 'undefined.png', 'slike/undefined.png', '', 'lokalno', '2025-05-12 10:13:40', NULL),
(5, 'lion.jpg', 'slike/lion.jpg', 'Slika lava u prirodi', 'lokalno', '2025-05-12 10:23:01', NULL),
(6, 'api_G4gWu7HJ5vI.jpg', 'https://images.unsplash.com/photo-1729280277171-71a6acd762e6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1NzV8&ixlib=rb-4.1.0&q=80&w=1080', 'A bridge with a city skyline in the background', 'api', '2025-05-12 10:59:25', 'G4gWu7HJ5vI'),
(7, 'api_kgzcovItVt8.jpg', 'https://images.unsplash.com/photo-1745508823793-e19654f8085a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1NzV8&ixlib=rb-4.1.0&q=80&w=1080', 'Nighttime view of a vibrant city street.', 'api', '2025-05-12 10:59:25', 'kgzcovItVt8'),
(8, 'api_4EvreRzmq44.jpg', 'https://images.unsplash.com/photo-1745750747228-d7ae37cba3a5?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1NzV8&ixlib=rb-4.1.0&q=80&w=1080', 'Foggy trees covered in a misty haze.', 'api', '2025-05-12 10:59:25', '4EvreRzmq44'),
(9, 'api_oip8AnEhf4U.jpg', 'https://images.unsplash.com/photo-1746058332635-71814ec87ac0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1NzV8&ixlib=rb-4.1.0&q=80&w=1080', 'A street vendor prepares food on a sunny day.', 'api', '2025-05-12 10:59:25', 'oip8AnEhf4U'),
(10, 'api_pC3zn62r0Q4.jpg', 'https://images.unsplash.com/photo-1746457421535-60ef4cb6d8d4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1NzV8&ixlib=rb-4.1.0&q=80&w=1080', 'A person crosses a wet street in the city.', 'api', '2025-05-12 10:59:25', 'pC3zn62r0Q4'),
(11, 'api_Jau-d_pZW6Y.jpg', 'https://images.unsplash.com/photo-1746624731088-164183e5beb8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1NzV8&ixlib=rb-4.1.0&q=80&w=1080', 'A mountain peaks proudly under a clear blue sky.', 'api', '2025-05-12 10:59:25', 'Jau-d_pZW6Y'),
(12, 'api_1ulM0x54tZ0.jpg', 'https://images.unsplash.com/photo-1669411162387-0415e1d0dc7d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1ODh8&ixlib=rb-4.1.0&q=80&w=1080', 'A man with glasses gazes toward the sunlight.', 'api', '2025-05-12 10:59:38', '1ulM0x54tZ0'),
(13, 'api_PIJel1qTrOY.jpg', 'https://images.unsplash.com/photo-1741851373441-88b6f673d655?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1ODh8&ixlib=rb-4.1.0&q=80&w=1080', 'Train tracks lead to a view of mount fuji.', 'api', '2025-05-12 10:59:38', 'PIJel1qTrOY'),
(14, 'api_tckwigHxUSI.jpg', 'https://images.unsplash.com/photo-1743013193065-e19d8112b15e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1ODh8&ixlib=rb-4.1.0&q=80&w=1080', 'Vibrant waters meet striking land features.', 'api', '2025-05-12 10:59:38', 'tckwigHxUSI'),
(15, 'api_AYtEEyMotgY.jpg', 'https://images.unsplash.com/photo-1744360817433-0d9386ddb9e4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1ODh8&ixlib=rb-4.1.0&q=80&w=1080', 'A black car is parked in front of a building.', 'api', '2025-05-12 10:59:38', 'AYtEEyMotgY'),
(16, 'api_HzLgETDi1aU.jpg', 'https://images.unsplash.com/photo-1746058387788-e58453a07498?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1ODh8&ixlib=rb-4.1.0&q=80&w=1080', 'People play basketball in a colorful, urban setting.', 'api', '2025-05-12 10:59:38', 'HzLgETDi1aU'),
(17, 'api_Nbq0OdEtDp8.jpg', 'https://images.unsplash.com/photo-1746230605205-89159f6530c2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1ODh8&ixlib=rb-4.1.0&q=80&w=1080', 'Sunset glistens over the ocean and the beach.', 'api', '2025-05-12 10:59:38', 'Nbq0OdEtDp8'),
(18, 'api_FjWmN6IoNVg.jpg', 'https://images.unsplash.com/photo-1741705817231-5fadb295cc9d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1OTZ8&ixlib=rb-4.1.0&q=80&w=1080', 'Sunset colors the beach and the sea.', 'api', '2025-05-12 10:59:47', 'FjWmN6IoNVg'),
(19, 'api_hcBVdd2leJs.jpg', 'https://images.unsplash.com/photo-1741850820849-1b63a5911606?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1OTZ8&ixlib=rb-4.1.0&q=80&w=1080', 'An elephant stands in the african savanna.', 'api', '2025-05-12 10:59:47', 'hcBVdd2leJs'),
(20, 'api_edEXTbShEL0.jpg', 'https://images.unsplash.com/photo-1744690098560-87001ce7c217?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1OTZ8&ixlib=rb-4.1.0&q=80&w=1080', 'A couple shares a loving moment in the grass.', 'api', '2025-05-12 10:59:47', 'edEXTbShEL0'),
(21, 'api_lIXRSlHUv0s.jpg', 'https://images.unsplash.com/photo-1745512751454-710500481a82?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1OTZ8&ixlib=rb-4.1.0&q=80&w=1080', 'Icebergs loom in a dark, cold ocean.', 'api', '2025-05-12 10:59:47', 'lIXRSlHUv0s'),
(22, 'api_0VvQjNVDTtw.jpg', 'https://images.unsplash.com/photo-1745555926235-faa237ea89a0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1OTZ8&ixlib=rb-4.1.0&q=80&w=1080', 'Woman reads a map in a forest.', 'api', '2025-05-12 10:59:47', '0VvQjNVDTtw'),
(23, 'api_xvBAKfjcPvA.jpg', 'https://images.unsplash.com/photo-1746058359873-7e4707604a83?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3NTAxMzl8MHwxfHJhbmRvbXx8fHx8fHx8fDE3NDcwNDc1OTZ8&ixlib=rb-4.1.0&q=80&w=1080', 'High-rise buildings tower over a public space.', 'api', '2025-05-12 10:59:47', 'xvBAKfjcPvA');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Username`, `Password`, `role`) VALUES
(1, 'karlo', '$2y$10$L40cZmu4tNq/mLxLS6bituGrl/mmABM1P6d4sHykedebOeURX1rI6', 'admin'),
(2, 'fran', '$2y$10$RzqhMMn5u5QcudmwcEwLeueTcN.Q0HaY7USM1lwyd8oJB/LRhnPGm', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `weather_data`
--

CREATE TABLE `weather_data` (
  `ID` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `temperature` decimal(10,0) NOT NULL,
  `precipitation` decimal(10,0) NOT NULL,
  `weather_type` enum('Sunny','Rainy','Snowy','Cloudy') NOT NULL,
  `season` enum('Spring','Summer','Autumn','Winter') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weather_data`
--

INSERT INTO `weather_data` (`ID`, `location`, `date`, `temperature`, `precipitation`, `weather_type`, `season`) VALUES
(1, 'Papuk', '2025-05-15', 16, 45, 'Cloudy', 'Spring'),
(2, 'Zagreb', '2025-05-15', 21, 0, 'Sunny', 'Summer'),
(3, 'Mali Lošinj', '2025-05-23', 32, 0, 'Sunny', 'Summer'),
(5, 'Sljeme', '2025-12-30', -4, 100, 'Snowy', 'Winter'),
(6, 'Dubrovnik', '2025-06-28', 39, 0, 'Sunny', 'Summer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ocjene`
--
ALTER TABLE `ocjene`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `planned_trip`
--
ALTER TABLE `planned_trip`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `weather_data_id` (`weather_data_id`);

--
-- Indexes for table `slike`
--
ALTER TABLE `slike`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_image_id_unique` (`api_image_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `weather_data`
--
ALTER TABLE `weather_data`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ocjene`
--
ALTER TABLE `ocjene`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `planned_trip`
--
ALTER TABLE `planned_trip`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `slike`
--
ALTER TABLE `slike`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `weather_data`
--
ALTER TABLE `weather_data`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `planned_trip`
--
ALTER TABLE `planned_trip`
  ADD CONSTRAINT `planned_trip_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `planned_trip_ibfk_2` FOREIGN KEY (`weather_data_id`) REFERENCES `weather_data` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
