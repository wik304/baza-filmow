-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Lis 06, 2025 at 03:34 PM
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
-- Database: `baza_filmow_db`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `poster_url` varchar(255) DEFAULT NULL,
  `release_year` int(11) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `popularity` decimal(5,2) DEFAULT 0.00,
  `user_rating` decimal(3,1) DEFAULT NULL,
  `critic_rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `description`, `poster_url`, `release_year`, `director`, `genre`, `popularity`, `user_rating`, `critic_rating`, `created_at`) VALUES
(1, 'Incepcja', 'Złodziej, który kradnie informacje, wchodząc do snów swoich ofiar, otrzymuje ostatnie zadanie: zaszczepić myśl w umyśle celu.', 'uploads/posters/inception.jpg', 2010, 'Christopher Nolan', 'Sci-Fi', 9.50, 8.8, 8.1, '2025-10-28 15:39:34'),
(2, 'Diuna: Część druga', 'Paul Atryda jednoczy się z Chani i Fremenami, szukając zemsty na spiskowcach, którzy zniszczyli jego rodzinę.', 'uploads/posters/dune-part-two.jpg', 2024, 'Denis Villeneuve', 'Sci-Fi', 9.20, 7.9, 8.0, '2025-10-28 15:39:34'),
(3, 'Parasite', 'Czteroosobowa rodzina bezrobotnych postanawia odmienić swój los, wplątując się w życie zamożnej rodziny Parków.', 'uploads/posters/parasite.jpg', 2019, 'Bong Joon Ho', 'Thriller', 8.80, 7.9, 7.5, '2025-10-28 15:39:34'),
(4, 'Joker', 'W Gotham City chory psychicznie komik Arthur Fleck zostaje zepchnięty na margines. Jego życie to ciąg niefortunnych zdarzeń, które prowadzą go na ścieżkę zbrodni.', 'uploads/posters/joker.jpg', 2019, 'Todd Phillips', 'Thriller', 8.50, 8.9, 8.7, '2025-10-28 15:39:34'),
(5, 'Forrest Gump', 'Historia życia prostolinijnego Forresta Gumpa, który mimo niskiego ilorazu inteligencji bierze udział w najważniejszych wydarzeniach w historii USA.', 'uploads/posters/forrest-gump.jpg', 1994, 'Robert Zemeckis', 'Dramat', 9.80, 7.0, 9.0, '2025-10-28 15:39:34'),
(6, 'Skazani na Shawshank', 'Adaptacja opowiadania Stephena Kinga. Niesłusznie skazany na dożywocie bankier, Andy Dufresne, stara się przetrwać w więzieniu, zachowując nadzieję.', 'uploads/posters/shawshank-redemption.jpg', 1994, 'Frank Darabont', 'Dramat', 9.90, 8.4, 8.3, '2025-10-28 15:39:34'),
(7, 'Ojciec Chrzestny', 'Starzejący się patriarcha potężnej, nowojorskiej rodziny mafijnej, Vito Corleone, postanawia przekazać kontrolę nad swoim imperium. Kiedy jego najmłodszy syn, Michael, początkowo niechętny rodzinie, zostaje wciągnięty w brutalny świat przestępczości i zdrady, musi przejąć rolę ojca i stać się nowym, bezwzględnym Donem.', 'uploads/posters/godfather.jpg', 1972, 'Francis Ford Coppola', 'Dramat', 9.80, 10.0, 9.8, '2025-11-05 14:56:22'),
(8, 'Pulp Fiction', 'Losy dwóch płatnych morderców, żony ich szefa, boksera, który miał przegrać walkę, oraz pary drobnych rabusiów splatają się w serii nieprzewidywalnych, pełnych czarnego humoru i przemocy zdarzeń. Film opowiada trzy pozornie oddzielne historie, które łączą się w nieliniowej narracji.', 'uploads/posters/pulpfiction.jpg', 1994, 'Quentin Tarantino', 'Dramat', 9.50, 8.9, 9.2, '2025-11-05 14:56:22'),
(9, 'Mroczny Rycerz', 'Batman, z pomocą porucznika Gordona i prokuratora Harveya Denta, kontynuuje swoją misję oczyszczenia Gotham z przestępczości. Na ich drodze staje jednak nowy, genialny i anarchiczny złoczyńca znany jako Joker, który pogrąża miasto w chaosie i zmusza Mrocznego Rycerza do przekroczenia cienkiej granicy między bohaterem a mścicielem.', 'uploads/posters/darkknight.jpg', 2008, 'Christopher Nolan', 'Dramat', 9.70, 10.0, 9.4, '2025-11-05 14:56:22'),
(10, 'Spirited Away: W krainie bogów', 'Dziesięcioletnia Chihiro, podczas przeprowadzki do nowego domu, trafia do magicznej krainy zamieszkanej przez duchy, bogów i potwory. Gdy jej rodzice zostają zamienieni w świnie przez potężną czarownicę Yubabę, dziewczynka musi podjąć pracę w niezwykłej łaźni dla bóstw, aby znaleźć sposób na uratowanie rodziny i powrót do świata ludzi.', 'uploads/posters/spiritedaway.jpg', 2001, 'Hayao Miyazaki', 'Animacja', 9.20, 8.6, 9.7, '2025-11-05 14:56:22'),
(11, 'Dwunastu gniewnych ludzi', 'Dwunastu przysięgłych zbiera się w dusznym pokoju, aby zadecydować o winie lub niewinności młodego chłopaka oskarżonego o morderstwo ojca. Kiedy jedenastu z nich jest gotowych na szybki werdykt skazujący, jeden przysięgły (Juror nr 8) postanawia samotnie przeciwstawić się reszcie, argumentując, że sprawa wymaga głębszej analizy dowodów i usunięcia wszelkich \'uzasadnionych wątpliwości\'.', 'uploads/posters/12angrymen.jpg', 1957, 'Sidney Lumet', 'Dramat', 8.90, 8.0, 9.5, '2025-11-05 14:56:22'),
(12, 'Lista Schindlera', 'Oparta na faktach historia Oskara Schindlera, niemieckiego przemysłowca i członka NSDAP, który podczas II wojny światowej ratuje ponad tysiąc Żydów przed śmiercią w obozie koncentracyjnym. Zatrudniając ich w swojej fabryce emaliowanych naczyń w Krakowie, Schindler ryzykuje własnym życiem i majątkiem, aby ocalić jak najwięcej osób.', 'uploads/posters/schindlerslist.jpg', 1993, 'Steven Spielberg', 'Dramat', 9.10, 8.9, 9.6, '2025-11-05 14:56:22'),
(13, 'Władca Pierścieni: Powrót Króla', 'Ostatnia część trylogii. Podczas gdy Aragorn jednoczy siły dobra, aby stoczyć ostateczną bitwę z armiami Saurona, hobbici Frodo i Sam kontynuują swoją desperacką misję do wnętrza Mordoru. Ich celem jest Góra Przeznaczenia – jedyne miejsce, w którym można zniszczyć Pierścień Władzy i pokonać Władcę Ciemności.', 'uploads/posters/rotk.jpg', 2003, 'Peter Jackson', 'Fantasy', 9.60, 10.0, 9.4, '2025-11-05 14:56:22'),
(14, 'The Matrix', 'Haker komputerowy Neo odkrywa, że świat, który zna, jest jedynie zaawansowaną symulacją komputerową stworzoną przez maszyny. Dołącza do Morfeusza i grupy rebeliantów, aby walczyć o wolność ludzkości.', 'uploads/posters/matrix.jpg', 1999, 'Lana Wachowski, Lilly Wachowski', 'Sci-Fi', 9.40, 8.7, 9.0, '2025-11-05 15:17:08'),
(15, 'Interstellar', 'W niedalekiej przyszłości Ziemia staje się niezdatna do życia. Były pilot NASA, Cooper, wyrusza w desperacką misję przez tunel czasoprzestrzenny, aby znaleźć nowy dom dla ludzkości, pozostawiając za sobą swoje dzieci.', 'uploads/posters/interstellar.jpg', 2014, 'Christopher Nolan', 'Sci-Fi', 9.30, 8.6, 8.8, '2025-11-05 15:17:08'),
(16, 'Szeregowiec Ryan', 'Po brutalnym lądowaniu w Normandii podczas II wojny światowej, kapitan John Miller otrzymuje rozkaz poprowadzenia swojego oddziału za linię wroga. Ich misją jest odnalezienie i bezpieczne sprowadzenie do domu szeregowca Jamesa Ryana, którego trzej bracia zginęli już na froncie.', 'uploads/posters/savingprivateryan.jpg', 1998, 'Steven Spielberg', 'Wojenny', 9.00, 8.6, 9.2, '2025-11-05 15:17:08'),
(17, 'Chłopcy z ferajny', 'Oparta na faktach historia Henry\'ego Hilla, który od najmłodszych lat wspina się po szczeblach mafijnej kariery w Nowym Jorku. Film ukazuje brutalną rzeczywistość, bogactwo i ostateczny upadek życia w zorganizowanej przestępczości.', 'uploads/posters/goodfellas.jpg', 1990, 'Martin Scorsese', 'Dramat', 9.10, 8.7, 9.4, '2025-11-05 15:17:08'),
(18, 'Król Lew', 'Młody lew, Simba, następca tronu Lwiej Ziemi, zostaje oszukany przez swojego podstępnego wuja, Skazę, i ucieka na wygnanie. Z pomocą przyjaciół, Timona i Pumby, Simba musi dorosnąć i wrócić, by odzyskać swoje prawowite miejsce w \'kręgu życia\'.', 'uploads/posters/lionking.jpg', 1994, 'Roger Allers, Rob Minkoff', 'Animacja', 8.80, 8.5, 9.1, '2025-11-05 15:17:08'),
(19, 'Lśnienie', 'Pisarz Jack Torrance przyjmuje posadę zimowego stróża w odcięto od świata hotelu Overlook. Zabiera ze sobą żonę i syna, ale złowroga siła obecna w hotelu oraz przerażająca izolacja powoli doprowadzają go do obłędu.', 'uploads/posters/shining.jpg', 1980, 'Stanley Kubrick', 'Horror', 8.70, 8.4, 8.8, '2025-11-05 15:17:08'),
(20, 'Łowca androidów', 'W dystopijnym Los Angeles 2019 roku, detektyw Rick Deckard, znany jako \'łowca androidów\', zostaje zmuszony do powrotu ze emerytury. Jego zadaniem jest wytropienie i \'eliminowanie\' grupy zbiegłych, zaawansowanych replikantów, którzy przybyli na Ziemię.', 'uploads/posters/bladerunner.jpg', 1982, 'Ridley Scott', 'Sci-Fi', 8.90, 8.1, 9.0, '2025-11-05 15:17:08'),
(21, 'Amelia', 'Młoda, ekscentryczna kelnerka z paryskiej dzielnicy Montmartre, Amelia Poulain, postanawia w sekrecie pomagać ludziom wokół siebie i naprawiać ich życia. W trakcie tej misji odkrywa miłość w najmniej oczekiwanym momencie.', 'uploads/posters/amelie.jpg', 2001, 'Jean-Pierre Jeunet', 'Komedia', 8.50, 8.3, 8.9, '2025-11-05 15:17:08'),
(22, 'Casablanca', 'Cyniczny amerykański emigrant, Rick Blaine, prowadzi popularny klub w kontrolowanej przez Francję Vichy Casablance podczas II wojny światowej. Niespodziewanie w jego życiu ponownie pojawia się dawna miłość, Ilsa Lund, która wraz z mężem, przywódcą ruchu oporu, desperacko potrzebuje jego pomocy w ucieczce do Ameryki.', 'uploads/posters/casablanca.jpg', 1942, 'Michael Curtiz', 'Dramat', 8.60, 8.5, 9.7, '2025-11-05 15:17:08'),
(23, 'Poszukiwacze zaginionej Arki', 'Nieustraszony archeolog i poszukiwacz przygód, Indiana Jones, zostaje wynajęty przez rząd USA, aby odnaleźć legendarną Arkę Przymierza. Musi zdążyć, zanim potężny artefakt wpadnie w ręce nazistów, którzy chcą wykorzystać jego moc do zdobycia władzy nad światem.', 'uploads/posters/raiders.jpg', 1981, 'Steven Spielberg', 'Przygodowy', 9.00, 8.4, 9.3, '2025-11-05 15:17:08');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `movie_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 11, 8.0, NULL, '2025-11-06 13:37:03'),
(3, 3, 7, 10.0, NULL, '2025-11-06 13:37:26'),
(4, 3, 13, 10.0, 'fdg', '2025-11-06 13:41:17'),
(6, 3, 9, 10.0, 'test', '2025-11-06 13:41:40'),
(10, 2, 9, 7.0, 'test1', '2025-11-06 13:41:40'),
(11, 1, 9, 2.0, 'test2', '2025-11-06 13:41:40'),
(12, 3, 5, 7.0, '', '2025-11-06 14:25:35');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','critic','admin') NOT NULL DEFAULT 'user',
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `phone_number`, `created_at`) VALUES
(1, 'wik304', 'wiktorzawadzki007@gmail.com', '$2y$10$Nwx9Bxpd.5iT3pi1cBUc..SxbEpZq3SF9fmQzXjSnpccanGlqEOn6', 'user', NULL, '2025-10-13 15:00:50'),
(2, '21321', 'h@d', '$2y$10$zQBeV3/L.elHQRuUuaBlXuYVo5XbFeeDeBj0Rhl96xFCr/zmb7jSO', 'user', NULL, '2025-10-13 15:05:17'),
(3, 'Wiktor Zawadzki', 'wiktorzawadzki@gmail.com', '$2y$10$ICUDLAA64J2gxf6xD1hpMOTFjtiAb3SR2SIsRMRxFlauivbtqFNRy', 'user', NULL, '2025-10-13 15:31:49');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_movie_rating` (`user_id`,`movie_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
