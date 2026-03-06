-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2026 at 05:55 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `konzvalony`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `konie`
--

CREATE TABLE `konie` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `rasa` varchar(100) NOT NULL,
  `wiek` int(11) NOT NULL,
  `opis` text NOT NULL,
  `cena_za_dobe` decimal(10,2) NOT NULL,
  `zdjecie` varchar(255) DEFAULT 'default-horse.jpg',
  `dostepny` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `konie`
--

INSERT INTO `konie` (`id`, `nazwa`, `rasa`, `wiek`, `opis`, `cena_za_dobe`, `zdjecie`, `dostepny`) VALUES
(1, 'Waldek', 'Podroznik', 12, 'Waldek zwiedzil nie jedną dziure a z tobą moze polepszyc swoje statystyki', 150.00, 'horse1.jpg', 1),
(2, 'Gejakles', 'Filozof', 8, 'Na krzesle siedzę i filozofuję Do wniosku dochodzę, że w życiu najpiękniejsze są chuje', 67.00, 'horse2.jpg', 1),
(3, 'Koniald Tusk', 'Niemiecki owczarek', 15, 'Koniald mimo ze jest z Polski ma bardzo durze umilowanie do Niemiec', 100.00, 'horse3.jpg', 1),
(4, 'Sianobajceps', 'Gejmer', 41, 'Gra w furry love caly dzien a to tylko jedna z jego zalet', 150.00, 'horse4.jpg', 1),
(5, 'Mirek', 'Kon Pracujący na Etacie', 7, 'Mirek nie tylko zamuruje cegly ale rowniez w miedzy czasie obali krate browca', 130.00, 'default-horse.jpg', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacje`
--

CREATE TABLE `rezerwacje` (
  `id` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `id_konia` int(11) NOT NULL,
  `data_od` date NOT NULL,
  `data_do` date NOT NULL,
  `status` enum('oczekujaca','potwierdzona','anulowana') DEFAULT 'oczekujaca',
  `data_rezerwacji` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `rezerwacje`
--

INSERT INTO `rezerwacje` (`id`, `id_uzytkownika`, `id_konia`, `data_od`, `data_do`, `status`, `data_rezerwacji`) VALUES
(1, 2, 1, '2024-06-15', '2024-06-17', 'potwierdzona', '2026-03-05 16:46:50'),
(2, 2, 3, '2024-06-20', '2024-06-22', 'potwierdzona', '2026-03-05 16:46:50'),
(4, 1, 4, '2026-03-06', '2026-03-07', 'potwierdzona', '2026-03-05 17:01:06'),
(5, 4, 4, '2026-03-18', '2026-03-26', 'potwierdzona', '2026-03-05 17:06:58');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `rola` enum('user','admin') NOT NULL DEFAULT 'user',
  `data_rejestracji` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `username`, `email`, `haslo`, `rola`, `data_rejestracji`) VALUES
(1, 'admin', 'admin@konzvalony.pl', '$2y$10$b8LUvXMu5wINYllqS3zn3uj5UzEZUkLItq30CHpnOq0Ia5J7VOG3q', 'admin', '2026-03-05 16:46:50'),
(2, 'janek', 'jan@example.com', '$2y$10$KjGMjmflmgAPlF8hR97/rOW/PbOgkOZZk1sbq/NcIHlLy2OTWmdgu', 'user', '2026-03-05 16:46:50'),
(4, 'kuca', 'asdadasdas@gmail.com', '$2y$10$KgU9/OiFJW4gTTtKwVksMehsxjIeXpRTHX3NiuWnI03jDDMFJvDWe', 'user', '2026-03-05 17:06:19');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `konie`
--
ALTER TABLE `konie`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`),
  ADD KEY `id_konia` (`id_konia`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `konie`
--
ALTER TABLE `konie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD CONSTRAINT `rezerwacje_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezerwacje_ibfk_2` FOREIGN KEY (`id_konia`) REFERENCES `konie` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
