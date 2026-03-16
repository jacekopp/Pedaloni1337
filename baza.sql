-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 08:21 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

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
CREATE DATABASE IF NOT EXISTS `konzvalony` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci;
USE `konzvalony`;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `blog`
--

CREATE TABLE `blog` (
  `id` int(11) NOT NULL,
  `tytul` varchar(255) NOT NULL,
  `tresc` text NOT NULL,
  `zdjecie` varchar(255) DEFAULT 'default-blog.jpg',
  `autor` varchar(100) DEFAULT NULL,
  `data_dodania` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('szkic','opublikowany') DEFAULT 'opublikowany',
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `blog`
--

INSERT INTO `blog` (`id`, `tytul`, `tresc`, `zdjecie`, `autor`, `data_dodania`, `status`, `views`) VALUES
(1, 'Jak dbać o kopytka swojego konia?', 'Regularna pielęgnacja kopyt to podstawa zdrowia każdego konia. W tym artykule dowiesz się, jak często czyścić kopyta, na co zwracać uwagę i kiedy wezwać kowala...', 'blog1.jpg', 'Jan Kowalski', '2026-03-11 12:36:38', 'opublikowany', 1),
(2, '10 oznak, że Twój koń Cię kocha', 'Czy wiesz, że konie okazują przywiązanie na wiele sposobów? Od delikatnego szturchania nosem po spokojne rżenie na Twój widok - poznaj wszystkie znaki...', 'blog2.jpg', 'Anna Nowak', '2026-03-11 12:36:38', 'opublikowany', 0),
(3, 'Wiosenne porządki w stajni', 'Przed sezonem warto zrobić generalne porządki. Sprawdź naszą listę rzeczy do zrobienia, aby Twoja stajnia była gotowa na wiosenne przejażdżki!', 'blog3.jpg', 'Piotr Wiśniewski', '2026-03-11 12:36:38', 'opublikowany', 0);

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
(5, 'Mirek', 'Kon Pracujący na Etacie', 7, 'Mirek nie tylko zamuruje cegly ale rowniez w miedzy czasie obali krate browca', 130.00, 'default-horse.jpg', 1),
(9, 'Zenon', 'Kon na emeryturze', 22, 'Zenon całe życie harował na pańskim, a teraz ma emeryturę 1200 zł i musi dorabiać jako koń do wynajęcia. Politycy obiecywali złote góry, a on dalej wozi tych samych grubych januszy.', 90.00, 'default-horse.jpg', 1),
(10, 'Izabela', 'Konfidentka', 6, 'Izabela donosi na inne konie do zarządu stajni. Za marchewkę sprzeda każdego. Koledzy ją omijają szerokim łukiem, ale prezes ją uwielbia. Idealna dla osób które lubią wiedzieć co u innych.', 180.00, 'default-horse.jpg', 1),
(11, 'Czesław', 'Kon filozof', 14, 'Czesław czyta za dużo Nietzschego i teraz ma kryzys egzystencjalny. \"Bóg umarł, ale kto mu teraz da marchewkę?\" - zastanawia się codziennie. Nie polecam na pierwszą jazdę, bo będzie Ci truł dupę godzinami.', 120.00, 'default-horse.jpg', 1),
(12, 'Brygida', 'Narodowiec', 9, 'Brygida uważa że tylko polskie konie są prawdziwe. Na niemieckie owczarki prycha, na angielskie rże z pogardą. \"Jeszcze Polska nie zginęła póki my jebiemy\" - to jej ulubione hasło.', 200.00, 'default-horse.jpg', 1),
(13, 'Eustachy', 'Kon z patostreamu', 5, 'Eustachy prowadzi patostreamy z boksu. Za dwa jabłka zrobi wszystko przed kamerą. Ostatnio pokazywał jak je siano i wszyscy się zrzygali. Popularniejszy niż Gural.', 160.00, 'default-horse.jpg', 1),
(14, 'Marcelina', 'Konfederatka', 3, 'Marcelina wierzy że płaska ziemia jest płaska a słońce krąży wokół niej. Na pytanie \"czemu zachód słońca wygląda tak a nie inaczej\" odpowiada \"bo tak, nie wierz w naukę tylko w Korwin-Mikkego\".', 190.00, 'default-horse.jpg', 1),
(15, 'Bolesław', 'Kon który spadł z rowerka', 17, 'Bolesław jako małe źrebię spadł z rowerka i od tamtej pory jest lekko przygłuchy. Każde polecenie słyszy inaczej - \"w lewo\" interpretuje jako \"w prawo\", \"stój\" jako \"galop\". Ale ma dobre serce.', 140.00, 'default-horse.jpg', 1),
(16, 'Róża', 'Była posłanka', 4, 'Róża dorabia po sejmie. Za 200 złotych udaje że Cię słucha i kiwa głową, ale tak naprawdę myśli o swoich sprawach. Często przynosi do stajni swoich byłych kolegów i każą jej taniej.', 210.00, 'default-horse.jpg', 1),
(17, 'Władysław', 'Kon z długiem', 11, 'Władysław wziął kredyt we frankach i teraz haruje 24/7 żeby spłacić. Ma drugą pracę w nocy jako koń dorożkarski. Wygląda na zmęczonego ale zawsze mówi \"a co tam, jakoś to będzie\".', 170.00, 'default-horse.jpg', 1),
(18, 'Klementyna', 'Nadpobudliwa', 8, 'Klementyna ma ADHD i bierze leki. Jak zapomni rano tabletki to gania po całej stajni i straszy źrebaki. Jak weźmie to śpi cały dzień. Idealna dla osób które lubią hazard - nigdy nie wiesz co dostaniesz.', 150.00, 'default-horse.jpg', 1),
(19, 'Hieronim', 'Kon Tusk', 12, 'Hieronim zawsze mówi że zrobi wszystko co obieca, ale potem idzie na kawę i zapomina. Ma w stajni swoją \"strefę komfortu\" i nie pozwala nikomu wchodzić. Ktoś mu powiedział że jest prezydencki.', 130.00, 'default-horse.jpg', 1),
(20, 'Teodozja', 'Krytyczka kulinarna', 7, 'Teodozja na wszystko narzeka. Siano za twarde, owies za suchy, woda za mokra. Jak już znajdziesz coś co jej smakuje, to i tak powie że mogło być lepsze. Zajebisty koń dla osób które lubią hejt.', 185.00, 'default-horse.jpg', 1),
(21, 'Bazyli', 'Wannablowcy', 5, 'Bazyli udaje że jest koniem wyścigowym, chociaż nikt go nie sprawdzał. Założył firmę \"Bazyli Transport\" i wozi ludzi do Biedronki. Na drzwiach naklejone ma \"Firma zajebista, nie pitol sie\".', 155.00, 'default-horse.jpg', 1),
(22, 'Matylda', 'Aktywistka', 6, 'Matylda protestuje pod stajnią że konie mają za mało praw. Postuluje 4-dniowy tydzień pracy i 8 godzin snu. Jak ktoś jej zwróci uwagę to mówi że to mobbing. Ostatnio chciała zakładać związek zawodowy koni.', 195.00, 'default-horse.jpg', 1),
(23, 'Korneliusz', 'Cwaniak z osiedla', 19, 'Korneliusz handluje pod stajnią używane siodła i wiązanki siana. Podobno ma długi u źrebaków z osiedla i dlatego uciekł do wynajmu. Nie ufaj mu jak mówi \"spoko ziom, załatwię tanio\".', 100.00, 'default-horse.jpg', 1),
(24, 'Julian', 'Ambasador', 10, 'Julian był ambasadorem w Szwajcarii, ale go odwołali bo za dużo jadł czekolady. Teraz wozi turystów i opowiada jak to kiedyś było fajnie. Ciągle cytuje \"ja za czasów Tuska to miałem lepiej\".', 175.00, 'default-horse.jpg', 1),
(25, 'Weronika', 'Pan doktor', 8, 'Weronika skończyła studia i każdy musi jej mówić \"pani doktor\". W rzeczywistości zrobiła kurs online z psychologii koni i teraz każdemu mówi jak ma żyć. Lepiej nie pytaj o radę.', 165.00, 'default-horse.jpg', 1),
(26, 'Mirosław', 'Janusz biznesu', 13, 'Mirek ma firmę remontową i w międzyczasie dorabia jako koń. Zawsze mówi \"na cito\", \"szybka kaska\" i \"załatwione w weekend\". Kończy się na tym że robi wszystko byle jak i spierdala.', 145.00, 'default-horse.jpg', 1),
(27, 'Sławomir', 'Mentor', 9, 'Sławek prowadzi kursy \"jak zostać milionerem na koniu\". Za 1000 złotych zdradzi Ci sekret, że trzeba kupić taniej a sprzedać drożej. Jeździ na nim cała patologia która wierzy w szybki hajs.', 220.00, 'default-horse.jpg', 1),
(28, 'Grażyna', 'Sezonowa', 11, 'Grażyna pracuje tylko w wakacje bo stwierdziła że ma dość polskiej zimy. Resztę roku spędza w Hiszpanii i wysyła zdjęcia na Instagram. Zawsze mówi \"ale tu ciepło, wy tam zamarzacie\".', 135.00, 'default-horse.jpg', 1),
(29, 'Zdzisław', 'Kombinator', 16, 'Zdzisław kombinuje jak ominąć system. Chodzi na lewo, jeździ na gapę i zawsze ma jakiś \"pewny interes\". Ostatnio chciał sprzedać liny z dzwonka kościelnego.', 115.00, 'default-horse.jpg', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `opinie`
--

CREATE TABLE `opinie` (
  `id` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `id_konia` int(11) NOT NULL,
  `ocena` int(11) DEFAULT NULL CHECK (`ocena` between 1 and 5),
  `komentarz` text DEFAULT NULL,
  `data_dodania` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('oczekujaca','zatwierdzona','odrzucona') DEFAULT 'oczekujaca'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `polecenia`
--

CREATE TABLE `polecenia` (
  `id` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `kod_polecajacy` varchar(50) NOT NULL,
  `liczba_poleconych` int(11) DEFAULT 0,
  `znizka_procent` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `polecenia`
--

INSERT INTO `polecenia` (`id`, `id_uzytkownika`, `kod_polecajacy`, `liczba_poleconych`, `znizka_procent`) VALUES
(1, 5, '46731426', 0, 10),
(2, 1, '0A61C68B', 0, 10),
(3, 6, '2EF3AEB5', 0, 10),
(4, 7, 'C147206D', 0, 10);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `poleceni_znajomi`
--

CREATE TABLE `poleceni_znajomi` (
  `id` int(11) NOT NULL,
  `id_polecenia` int(11) NOT NULL,
  `email_znajomego` varchar(100) NOT NULL,
  `data_wyslania` timestamp NOT NULL DEFAULT current_timestamp(),
  `czy_zarejestrowany` tinyint(1) DEFAULT 0,
  `czy_wykorzystany` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `poleceni_znajomi`
--

INSERT INTO `poleceni_znajomi` (`id`, `id_polecenia`, `email_znajomego`, `data_wyslania`, `czy_zarejestrowany`, `czy_wykorzystany`) VALUES
(1, 1, 'jacekstolarz0@gmail.com', '2026-03-11 12:59:20', 0, 0),
(2, 1, 'kasti@gmail.com', '2026-03-11 13:17:06', 0, 0),
(3, 1, 'patrykgyat@gmial.com', '2026-03-11 13:43:58', 0, 0),
(4, 3, 'jacekstolarz0@gmail.com', '2026-03-11 14:07:25', 0, 0),
(5, 3, 'patrykrybak086@gmail.com', '2026-03-11 14:11:03', 0, 0),
(6, 3, 'rybakpatryk188@gmail.com', '2026-03-11 14:23:27', 1, 0),
(7, 4, 'jacekstolarz0@gmail.com', '2026-03-11 14:26:39', 0, 0),
(8, 1, 'drkapselek@gmail.com', '2026-03-11 14:38:11', 0, 0),
(9, 1, 'cieslaa07@gmail.com', '2026-03-11 14:39:01', 0, 0),
(10, 4, 'klaudiam338@gmail.com', '2026-03-11 14:43:07', 0, 0);

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
(5, 4, 4, '2026-03-18', '2026-03-26', 'potwierdzona', '2026-03-05 17:06:58'),
(6, 5, 2, '2026-03-12', '2026-03-13', 'oczekujaca', '2026-03-11 11:51:46'),
(7, 6, 2, '2026-03-28', '2026-03-31', 'oczekujaca', '2026-03-11 14:15:50');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ulubione`
--

CREATE TABLE `ulubione` (
  `id` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `id_konia` int(11) NOT NULL,
  `data_dodania` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `ulubione`
--

INSERT INTO `ulubione` (`id`, `id_uzytkownika`, `id_konia`, `data_dodania`) VALUES
(3, 5, 3, '2026-03-11 13:20:28'),
(4, 5, 5, '2026-03-11 13:20:29');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `kod_polecajacy` varchar(50) DEFAULT NULL,
  `haslo` varchar(255) NOT NULL,
  `rola` enum('user','admin') NOT NULL DEFAULT 'user',
  `data_rejestracji` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `username`, `email`, `kod_polecajacy`, `haslo`, `rola`, `data_rejestracji`) VALUES
(1, 'admin', 'admin@konzvalony.pl', '0A61C68B', '$2y$10$b8LUvXMu5wINYllqS3zn3uj5UzEZUkLItq30CHpnOq0Ia5J7VOG3q', 'admin', '2026-03-05 16:46:50'),
(2, 'janek', 'jan@example.com', NULL, '$2y$10$KjGMjmflmgAPlF8hR97/rOW/PbOgkOZZk1sbq/NcIHlLy2OTWmdgu', 'user', '2026-03-05 16:46:50'),
(4, 'kuca', 'asdadasdas@gmail.com', NULL, '$2y$10$KgU9/OiFJW4gTTtKwVksMehsxjIeXpRTHX3NiuWnI03jDDMFJvDWe', 'user', '2026-03-05 17:06:19'),
(5, 'skibidi', 'kasti@gmail.com', '46731426', '$2y$10$dfdHLFST2c/gxQ3Damgxte1L/Ed.W1VzuJEjNwB7b8lR2Hk9WOMPq', 'user', '2026-03-11 11:51:29'),
(6, 'konzvalony', 'konzvalonyy@gmail.com', '2EF3AEB5', '$2y$10$datSi968xDZ86buf7eAoXe9U7aJHcbq4y7ikGKFfXHc/BelZeHHq6', 'user', '2026-03-11 14:07:11'),
(7, 'patryk', 'rybakpatryk188@gmail.com', 'C147206D', '$2y$10$Im1T8JoOmkoyTnmuQBL2luj87yEvPlBlrU3IeRZNKCDrJb3YrLnb.', 'user', '2026-03-11 14:24:48');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `konie`
--
ALTER TABLE `konie`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `opinie`
--
ALTER TABLE `opinie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_opinion` (`id_uzytkownika`,`id_konia`),
  ADD KEY `id_konia` (`id_konia`);

--
-- Indeksy dla tabeli `polecenia`
--
ALTER TABLE `polecenia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kod_polecajacy` (`kod_polecajacy`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- Indeksy dla tabeli `poleceni_znajomi`
--
ALTER TABLE `poleceni_znajomi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_polecenia` (`id_polecenia`);

--
-- Indeksy dla tabeli `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`),
  ADD KEY `id_konia` (`id_konia`);

--
-- Indeksy dla tabeli `ulubione`
--
ALTER TABLE `ulubione`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`id_uzytkownika`,`id_konia`),
  ADD KEY `id_konia` (`id_konia`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `kod_polecajacy` (`kod_polecajacy`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `konie`
--
ALTER TABLE `konie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `opinie`
--
ALTER TABLE `opinie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `polecenia`
--
ALTER TABLE `polecenia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `poleceni_znajomi`
--
ALTER TABLE `poleceni_znajomi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ulubione`
--
ALTER TABLE `ulubione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `opinie`
--
ALTER TABLE `opinie`
  ADD CONSTRAINT `opinie_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opinie_ibfk_2` FOREIGN KEY (`id_konia`) REFERENCES `konie` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `polecenia`
--
ALTER TABLE `polecenia`
  ADD CONSTRAINT `polecenia_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poleceni_znajomi`
--
ALTER TABLE `poleceni_znajomi`
  ADD CONSTRAINT `poleceni_znajomi_ibfk_1` FOREIGN KEY (`id_polecenia`) REFERENCES `polecenia` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD CONSTRAINT `rezerwacje_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezerwacje_ibfk_2` FOREIGN KEY (`id_konia`) REFERENCES `konie` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ulubione`
--
ALTER TABLE `ulubione`
  ADD CONSTRAINT `ulubione_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ulubione_ibfk_2` FOREIGN KEY (`id_konia`) REFERENCES `konie` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
