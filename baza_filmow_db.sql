-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Lis 16, 2025 at 08:16 PM
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
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `description`, `poster_url`, `release_year`, `director`, `genre`, `popularity`, `created_at`) VALUES
(1, 'Incepcja', 'Złodziej, który kradnie informacje, wchodząc do snów swoich ofiar, otrzymuje ostatnie zadanie: zaszczepić myśl w umyśle celu.', 'uploads/posters/inception.jpg', 2010, 'Christopher Nolan', 'Sci-Fi', 14.00, '2025-10-28 15:39:34'),
(2, 'Diuna: Część druga', 'Paul Atryda jednoczy się z Chani i Fremenami, szukając zemsty na spiskowcach, którzy zniszczyli jego rodzinę.', 'uploads/posters/dune-part-two.jpg', 2024, 'Denis Villeneuve', 'Sci-Fi', 1.00, '2025-10-28 15:39:34'),
(3, 'Parasite', 'Czteroosobowa rodzina bezrobotnych postanawia odmienić swój los, wplątując się w życie zamożnej rodziny Parków.', 'uploads/posters/parasite.jpg', 2019, 'Bong Joon Ho', 'Thriller', 5.00, '2025-10-28 15:39:34'),
(4, 'Joker', 'W Gotham City chory psychicznie komik Arthur Fleck zostaje zepchnięty na margines. Jego życie to ciąg niefortunnych zdarzeń, które prowadzą go na ścieżkę zbrodni.', 'uploads/posters/joker.jpg', 2019, 'Todd Phillips', 'Thriller', 5.00, '2025-10-28 15:39:34'),
(5, 'Forrest Gump', 'Historia życia prostolinijnego Forresta Gumpa, który mimo niskiego ilorazu inteligencji bierze udział w najważniejszych wydarzeniach w historii USA.', 'uploads/posters/forrest-gump.jpg', 1994, 'Robert Zemeckis', 'Dramat', 3.00, '2025-10-28 15:39:34'),
(6, 'Skazani na Shawshank', 'Adaptacja opowiadania Stephena Kinga. Niesłusznie skazany na dożywocie bankier, Andy Dufresne, stara się przetrwać w więzieniu, zachowując nadzieję.', 'uploads/posters/shawshank-redemption.jpg', 1994, 'Frank Darabont', 'Dramat', 0.00, '2025-10-28 15:39:34'),
(7, 'Ojciec Chrzestny', 'Starzejący się patriarcha potężnej, nowojorskiej rodziny mafijnej, Vito Corleone, postanawia przekazać kontrolę nad swoim imperium. Kiedy jego najmłodszy syn, Michael, początkowo niechętny rodzinie, zostaje wciągnięty w brutalny świat przestępczości i zdrady, musi przejąć rolę ojca i stać się nowym, bezwzględnym Donem.', 'uploads/posters/godfather.jpg', 1972, 'Francis Ford Coppola', 'Dramat', 3.00, '2025-11-05 14:56:22'),
(8, 'Pulp Fiction', 'Losy dwóch płatnych morderców, żony ich szefa, boksera, który miał przegrać walkę, oraz pary drobnych rabusiów splatają się w serii nieprzewidywalnych, pełnych czarnego humoru i przemocy zdarzeń. Film opowiada trzy pozornie oddzielne historie, które łączą się w nieliniowej narracji.', 'uploads/posters/pulpfiction.jpg', 1994, 'Quentin Tarantino', 'Dramat', 5.00, '2025-11-05 14:56:22'),
(9, 'Mroczny Rycerz', 'Batman, z pomocą porucznika Gordona i prokuratora Harveya Denta, kontynuuje swoją misję oczyszczenia Gotham z przestępczości. Na ich drodze staje jednak nowy, genialny i anarchiczny złoczyńca znany jako Joker, który pogrąża miasto w chaosie i zmusza Mrocznego Rycerza do przekroczenia cienkiej granicy między bohaterem a mścicielem.', 'uploads/posters/darkknight.jpg', 2008, 'Christopher Nolan', 'Dramat', 2.00, '2025-11-05 14:56:22'),
(10, 'Spirited Away: W krainie bogów', 'Dziesięcioletnia Chihiro, podczas przeprowadzki do nowego domu, trafia do magicznej krainy zamieszkanej przez duchy, bogów i potwory. Gdy jej rodzice zostają zamienieni w świnie przez potężną czarownicę Yubabę, dziewczynka musi podjąć pracę w niezwykłej łaźni dla bóstw, aby znaleźć sposób na uratowanie rodziny i powrót do świata ludzi.', 'uploads/posters/spiritedaway.jpg', 2001, 'Hayao Miyazaki', 'Animacja', 3.00, '2025-11-05 14:56:22'),
(11, 'Dwunastu gniewnych ludzi', 'Dwunastu przysięgłych zbiera się w dusznym pokoju, aby zadecydować o winie lub niewinności młodego chłopaka oskarżonego o morderstwo ojca. Kiedy jedenastu z nich jest gotowych na szybki werdykt skazujący, jeden przysięgły (Juror nr 8) postanawia samotnie przeciwstawić się reszcie, argumentując, że sprawa wymaga głębszej analizy dowodów i usunięcia wszelkich \'uzasadnionych wątpliwości\'.', 'uploads/posters/12angrymen.jpg', 1957, 'Sidney Lumet', 'Dramat', 0.00, '2025-11-05 14:56:22'),
(12, 'Lista Schindlera', 'Oparta na faktach historia Oskara Schindlera, niemieckiego przemysłowca i członka NSDAP, który podczas II wojny światowej ratuje ponad tysiąc Żydów przed śmiercią w obozie koncentracyjnym. Zatrudniając ich w swojej fabryce emaliowanych naczyń w Krakowie, Schindler ryzykuje własnym życiem i majątkiem, aby ocalić jak najwięcej osób.', 'uploads/posters/schindlerslist.jpg', 1993, 'Steven Spielberg', 'Dramat', 3.00, '2025-11-05 14:56:22'),
(13, 'Władca Pierścieni: Powrót Króla', 'Ostatnia część trylogii. Podczas gdy Aragorn jednoczy siły dobra, aby stoczyć ostateczną bitwę z armiami Saurona, hobbici Frodo i Sam kontynuują swoją desperacką misję do wnętrza Mordoru. Ich celem jest Góra Przeznaczenia – jedyne miejsce, w którym można zniszczyć Pierścień Władzy i pokonać Władcę Ciemności.', 'uploads/posters/rotk.jpg', 2003, 'Peter Jackson', 'Fantasy', 8.00, '2025-11-05 14:56:22'),
(14, 'The Matrix', 'Haker komputerowy Neo odkrywa, że świat, który zna, jest jedynie zaawansowaną symulacją komputerową stworzoną przez maszyny. Dołącza do Morfeusza i grupy rebeliantów, aby walczyć o wolność ludzkości.', 'uploads/posters/matrix.jpg', 1999, 'Lana Wachowski, Lilly Wachowski', 'Sci-Fi', 1.00, '2025-11-05 15:17:08'),
(15, 'Interstellar', 'W niedalekiej przyszłości Ziemia staje się niezdatna do życia. Były pilot NASA, Cooper, wyrusza w desperacką misję przez tunel czasoprzestrzenny, aby znaleźć nowy dom dla ludzkości, pozostawiając za sobą swoje dzieci.', 'uploads/posters/interstellar.jpg', 2014, 'Christopher Nolan', 'Sci-Fi', 1.00, '2025-11-05 15:17:08'),
(16, 'Szeregowiec Ryan', 'Po brutalnym lądowaniu w Normandii podczas II wojny światowej, kapitan John Miller otrzymuje rozkaz poprowadzenia swojego oddziału za linię wroga. Ich misją jest odnalezienie i bezpieczne sprowadzenie do domu szeregowca Jamesa Ryana, którego trzej bracia zginęli już na froncie.', 'uploads/posters/savingprivateryan.jpg', 1998, 'Steven Spielberg', 'Wojenny', 0.00, '2025-11-05 15:17:08'),
(17, 'Chłopcy z ferajny', 'Oparta na faktach historia Henry\'ego Hilla, który od najmłodszych lat wspina się po szczeblach mafijnej kariery w Nowym Jorku. Film ukazuje brutalną rzeczywistość, bogactwo i ostateczny upadek życia w zorganizowanej przestępczości.', 'uploads/posters/goodfellas.jpg', 1990, 'Martin Scorsese', 'Dramat', 3.00, '2025-11-05 15:17:08'),
(18, 'Król Lew', 'Młody lew, Simba, następca tronu Lwiej Ziemi, zostaje oszukany przez swojego podstępnego wuja, Skazę, i ucieka na wygnanie. Z pomocą przyjaciół, Timona i Pumby, Simba musi dorosnąć i wrócić, by odzyskać swoje prawowite miejsce w \'kręgu życia\'.', 'uploads/posters/lionking.jpg', 1994, 'Roger Allers, Rob Minkoff', 'Animacja', 1.00, '2025-11-05 15:17:08'),
(19, 'Lśnienie', 'Pisarz Jack Torrance przyjmuje posadę zimowego stróża w odcięto od świata hotelu Overlook. Zabiera ze sobą żonę i syna, ale złowroga siła obecna w hotelu oraz przerażająca izolacja powoli doprowadzają go do obłędu.', 'uploads/posters/shining.jpg', 1980, 'Stanley Kubrick', 'Horror', 1.00, '2025-11-05 15:17:08'),
(20, 'Łowca androidów', 'W dystopijnym Los Angeles 2019 roku, detektyw Rick Deckard, znany jako \'łowca androidów\', zostaje zmuszony do powrotu ze emerytury. Jego zadaniem jest wytropienie i \'eliminowanie\' grupy zbiegłych, zaawansowanych replikantów, którzy przybyli na Ziemię.', 'uploads/posters/bladerunner.jpg', 1982, 'Ridley Scott', 'Sci-Fi', 1.00, '2025-11-05 15:17:08'),
(21, 'Amelia', 'Młoda, ekscentryczna kelnerka z paryskiej dzielnicy Montmartre, Amelia Poulain, postanawia w sekrecie pomagać ludziom wokół siebie i naprawiać ich życia. W trakcie tej misji odkrywa miłość w najmniej oczekiwanym momencie.', 'uploads/posters/amelie.jpg', 2001, 'Jean-Pierre Jeunet', 'Komedia', 1.00, '2025-11-05 15:17:08'),
(22, 'Casablanca', 'Cyniczny amerykański emigrant, Rick Blaine, prowadzi popularny klub w kontrolowanej przez Francję Vichy Casablance podczas II wojny światowej. Niespodziewanie w jego życiu ponownie pojawia się dawna miłość, Ilsa Lund, która wraz z mężem, przywódcą ruchu oporu, desperacko potrzebuje jego pomocy w ucieczce do Ameryki.', 'uploads/posters/casablanca.jpg', 1942, 'Michael Curtiz', 'Dramat', 3.00, '2025-11-05 15:17:08'),
(23, 'Poszukiwacze zaginionej Arki', 'Nieustraszony archeolog i poszukiwacz przygód, Indiana Jones, zostaje wynajęty przez rząd USA, aby odnaleźć legendarną Arkę Przymierza. Musi zdążyć, zanim potężny artefakt wpadnie w ręce nazistów, którzy chcą wykorzystać jego moc do zdobycia władzy nad światem.', 'uploads/posters/raiders.jpg', 1981, 'Steven Spielberg', 'Przygodowy', 0.00, '2025-11-05 15:17:08'),
(24, 'Transformers: Początek', 'Animowany film opowiada o przyjaźni między Optimusem Prime a Megatronem, która przerodziła się we wrogość, oraz o początkach wojny między Autobotami a Deceptikonami.', 'uploads/posters/6918cabbe7ac6_transformers-one.jpg', 2024, 'Josh Cooley', 'Animacja', 3.00, '2025-11-15 18:47:23');

-- --------------------------------------------------------
--
-- Struktura tabeli dla tabeli `directors`
--

CREATE TABLE `directors` (
  `director_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `followers`
--

CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL COMMENT 'ID użytkownika, który obserwuje',
  `followed_id` int(11) NOT NULL COMMENT 'ID użytkownika, który jest obserwowany',
  `followed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `popularity` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `movie_directors`
--

CREATE TABLE `movie_directors` (
  `movie_id` int(11) NOT NULL,
  `director_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `movie_genres`
--

CREATE TABLE `movie_genres` (
  `movie_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktura tabeli dla tabeli `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `comment` text DEFAULT NULL,
  `rating_type` enum('user','critic') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `movie_id`, `rating`, `comment`, `rating_type`, `created_at`) VALUES
(55, 4, 1, 10.0, 'Ten film to absolutne arcydzieło! Od pierwszej do ostatniej minuty trzymał mnie w napięciu, a zakończenie po prostu zwaliło mnie z nóg. Aktorstwo na najwyższym poziomie, reżyseria genialna, a ścieżka dźwiękowa idealnie dopełnia całość. Polecam każdemu, kto szuka niezapomnianych wrażeń kinowych!', 'critic', '2025-11-09 22:14:59'),
(126, 2, 1, 5.0, 'Film ma swoje mocne i słabe strony. Początek był obiecujący, ale w połowie akcja trochę zwolniła. Koncepcja jest ciekawa, ale chyba nie została w pełni wykorzystana. Nie żałuję czasu spędzonego na oglądaniu, ale też nie jest to pozycja, do której będę wracać.', 'critic', '2024-11-06 11:14:59'),
(127, 1, 1, 3.0, 'Rewelacja! 10/10!', 'critic', '2020-07-21 06:25:22'),
(182, 4, 5, 10.0, '0', 'user', '2025-11-10 18:37:12'),
(183, 3, 1, 4.0, 'Bardzo dobry film! Fabuła jest intrygująca i dobrze poprowadzona, choć momentami tempo mogłoby być nieco szybsze. Aktorzy świetnie poradzili sobie ze swoimi rolami, zwłaszcza główny bohater. Wizualnie prezentuje się znakomicie. Zdecydowanie warto obejrzeć.', 'critic', '2020-07-21 06:25:22'),
(225, 4, 13, 10.0, 'test', 'user', '2025-11-15 17:26:00'),
(227, 4, 9, 6.0, 'dskajadjhajbha', 'user', '2025-11-15 17:26:30'),
(241, 4, 3, 7.0, 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.', 'user', '2025-11-15 18:23:44'),
(242, 4, 8, 10.0, 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.', 'user', '2025-11-15 18:23:55'),
(245, 5, 4, 8.0, 'dsadsdaa', 'critic', '2025-11-15 21:57:16'),
(246, 5, 1, 6.0, 'test', 'critic', '2025-11-15 22:01:30');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `review_likes`
--

CREATE TABLE `review_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_banner_url` varchar(255) DEFAULT NULL,
  `critic_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `phone_number`, `created_at`, `profile_banner_url`, `critic_description`) VALUES
(1, 'wik304', 'wiktorzawadzki007@gmail.com', '$2y$10$Nwx9Bxpd.5iT3pi1cBUc..SxbEpZq3SF9fmQzXjSnpccanGlqEOn6', 'critic', NULL, '2025-10-13 15:00:50', NULL, NULL),
(2, '21321', 'h@d', '$2y$10$zQBeV3/L.elHQRuUuaBlXuYVo5XbFeeDeBj0Rhl96xFCr/zmb7jSO', 'user', NULL, '2025-10-13 15:05:17', NULL, NULL),
(3, 'Wiktor Zawadzki', 'wiktorzawadzki@gmail.com', '$2y$10$ICUDLAA64J2gxf6xD1hpMOTFjtiAb3SR2SIsRMRxFlauivbtqFNRy', 'user', NULL, '2025-10-13 15:31:49', NULL, NULL),
(4, 'test', 'test@gmail.com', '$2y$10$xJK3MsM6KGD9T41BpfUx7uZgACl21lKtmhBS/V9yRxf1WcWTNr/ie', 'admin', NULL, '2025-11-09 21:08:29', 'uploads/posters/pulpfiction.jpg', NULL),
(5, 'krytyk', 'krytyk@gmail.com', '$2y$10$Wt3p2pXDc3dXfclktWINFOIN6WedupYmF.U7i5cqGMurgjMJ6tPbu', 'critic', NULL, '2025-11-15 19:47:08', 'uploads/posters/casablanca.jpg', 'Naczelny fan polskich produkcji filmowych.');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_movie_lists`
--

CREATE TABLE `user_movie_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `list_type` enum('favorite','watchlist') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_movie_lists`
--

INSERT INTO `user_movie_lists` (`id`, `user_id`, `movie_id`, `list_type`, `created_at`) VALUES
(2, 4, 1, 'watchlist', '2025-11-09 23:44:02'),
(4, 4, 1, 'favorite', '2025-11-09 23:47:30'),
(7, 4, 7, 'favorite', '2025-11-15 17:58:42'),
(8, 4, 13, 'favorite', '2025-11-15 17:58:45'),
(9, 4, 4, 'favorite', '2025-11-15 17:58:49'),
(10, 4, 3, 'favorite', '2025-11-15 17:58:52'),
(11, 4, 8, 'favorite', '2025-11-15 17:58:55'),
(12, 4, 10, 'favorite', '2025-11-15 17:58:59'),
(13, 4, 22, 'favorite', '2025-11-15 17:59:01'),
(14, 4, 12, 'favorite', '2025-11-15 17:59:05'),
(15, 4, 17, 'favorite', '2025-11-15 17:59:25'),
(16, 4, 2, 'watchlist', '2025-11-15 18:00:07'),
(17, 4, 21, 'watchlist', '2025-11-15 18:00:10'),
(18, 4, 19, 'watchlist', '2025-11-15 18:00:12'),
(19, 4, 20, 'watchlist', '2025-11-15 18:00:17'),
(20, 4, 15, 'watchlist', '2025-11-15 18:00:20'),
(21, 4, 14, 'watchlist', '2025-11-15 18:00:22'),
(22, 4, 18, 'watchlist', '2025-11-15 18:00:32'),
(23, 4, 5, 'watchlist', '2025-11-15 18:00:55'),
(25, 4, 24, 'favorite', '2025-11-15 18:54:19'),
(26, 5, 13, 'favorite', '2025-11-15 20:06:00');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `directors`
--
ALTER TABLE `directors`
  ADD PRIMARY KEY (`director_id`),
  ADD UNIQUE KEY `idx_full_name` (`full_name`);

--
-- Indeksy dla tabeli `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Indeksy dla tabeli `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `idx_name` (`name`);

--
-- Indeksy dla tabeli `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `movie_directors`
--
ALTER TABLE `movie_directors`
  ADD PRIMARY KEY (`movie_id`,`director_id`),
  ADD KEY `fk_md_director` (`director_id`);

--
-- Indeksy dla tabeli `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_movie_rating` (`user_id`,`movie_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indeksy dla tabeli `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD PRIMARY KEY (`movie_id`,`genre_id`),
  ADD KEY `fk_mg_genre` (`genre_id`);

--
-- Indeksy dla tabeli `review_likes`
--
ALTER TABLE `review_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`rating_id`),
  ADD KEY `rating_id` (`rating_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `user_movie_lists`
--
ALTER TABLE `user_movie_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`movie_id`,`list_type`),
  ADD KEY `movie_id` (`movie_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `directors`
--
ALTER TABLE `directors`
  MODIFY `director_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `review_likes`
--
ALTER TABLE `review_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_movie_lists`
--
ALTER TABLE `user_movie_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_directors`
--
ALTER TABLE `movie_directors`
  ADD CONSTRAINT `fk_md_director` FOREIGN KEY (`director_id`) REFERENCES `directors` (`director_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_md_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD CONSTRAINT `fk_mg_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mg_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD CONSTRAINT `review_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_likes_ibfk_2` FOREIGN KEY (`rating_id`) REFERENCES `ratings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_movie_lists`
--
ALTER TABLE `user_movie_lists`
  ADD CONSTRAINT `user_movie_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_movie_lists_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
