-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 07:20 PM
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
-- Struktura tabeli dla tabeli `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `icon_url` varchar(255) NOT NULL,
  `trigger_action` varchar(50) NOT NULL,
  `trigger_threshold` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `name`, `description`, `icon_url`, `trigger_action`, `trigger_threshold`) VALUES
(1, 'Pierwsza Ocena', 'Oceniono pierwszy film.', 'uploads/achievements/badge.png', 'rate_movie', 1),
(2, 'Recenzent', 'Napisano pierwszą recenzję.', 'uploads/achievements/success.png', 'write_review', 1),
(3, 'Krytyk', 'Napisano 10 recenzji.', 'uploads/achievements/victory.png', 'write_review', 10),
(4, 'Kolekcjoner', 'Dodano 5 filmów do listy \"Chcę obejrzeć\".', 'uploads/achievements/laurel-wreath.png', 'add_to_watchlist', 5),
(5, 'Fanatyk', 'Dodano 5 filmów do ulubionych.', 'uploads/achievements/thumbs-up.png', 'add_to_favorites', 5);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `background_image_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `movie_id`, `background_image_url`, `is_active`, `display_order`) VALUES
(1, 26, 'uploads/announcements/hero-image-1.jpg', 1, 1),
(2, 27, 'uploads/announcements/hero-image-2.jpg', 1, 2),
(3, 28, 'uploads/announcements/hero-image-3.jpg', 1, 3),
(4, 24, 'uploads/announcements/1764514813_1235176.13.png', 1, 4);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `directors`
--

CREATE TABLE `directors` (
  `director_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `directors`
--

INSERT INTO `directors` (`director_id`, `full_name`) VALUES
(3, 'Bong Joon Ho'),
(1, 'Christopher Nolan'),
(2, 'Denis Villeneuve'),
(7, 'Francis Ford Coppola'),
(6, 'Frank Darabont'),
(9, 'Hayao Miyazaki'),
(25, 'James Vanderbilt'),
(20, 'Jean-Pierre Jeunet'),
(24, 'Jon M. Chu'),
(22, 'Josh Cooley'),
(13, 'Lana Wachowski'),
(14, 'Lilly Wachowski'),
(15, 'Martin Scorsese'),
(21, 'Michael Curtiz'),
(12, 'Peter Jackson'),
(8, 'Quentin Tarantino'),
(19, 'Ridley Scott'),
(17, 'Rob Minkoff'),
(5, 'Robert Zemeckis'),
(16, 'Roger Allers'),
(23, 'Ruben Fleischer'),
(10, 'Sidney Lumet'),
(18, 'Stanley Kubrick'),
(11, 'Steven Spielberg'),
(4, 'Todd Phillips');

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

--
-- Dumping data for table `followers`
--

INSERT INTO `followers` (`id`, `follower_id`, `followed_id`, `followed_at`) VALUES
(1, 4, 5, '2025-11-18 19:47:54'),
(2, 4, 2, '2025-11-18 19:47:56');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`) VALUES
(4, 'Animacja'),
(3, 'Dramat'),
(5, 'Fantasy'),
(7, 'Horror'),
(8, 'Komedia'),
(11, 'Musical'),
(9, 'Przygodowy'),
(1, 'Sci-Fi'),
(2, 'Thriller'),
(6, 'Wojenny');

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
  `status` enum('available','upcoming') NOT NULL DEFAULT 'available',
  `popularity` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `description`, `poster_url`, `release_year`, `status`, `popularity`, `created_at`) VALUES
(1, 'Incepcja', 'Złodziej, który kradnie informacje, wchodząc do snów swoich ofiar, otrzymuje ostatnie zadanie: zaszczepić myśl w umyśle celu.', 'uploads/posters/inception.jpg', 2010, 'available', 14.00, '2025-10-28 15:39:34'),
(2, 'Diuna: Część druga', 'Paul Atryda jednoczy się z Chani i Fremenami, szukając zemsty na spiskowcach, którzy zniszczyli jego rodzinę.', 'uploads/posters/dune-part-two.jpg', 2024, 'available', 1.00, '2025-10-28 15:39:34'),
(3, 'Parasite', 'Czteroosobowa rodzina bezrobotnych postanawia odmienić swój los, wplątując się w życie zamożnej rodziny Parków.', 'uploads/posters/parasite.jpg', 2019, 'available', 5.00, '2025-10-28 15:39:34'),
(4, 'Joker', 'W Gotham City chory psychicznie komik Arthur Fleck zostaje zepchnięty na margines. Jego życie to ciąg niefortunnych zdarzeń, które prowadzą go na ścieżkę zbrodni.', 'uploads/posters/joker.jpg', 2019, 'available', 6.00, '2025-10-28 15:39:34'),
(5, 'Forrest Gump', 'Historia życia prostolinijnego Forresta Gumpa, który mimo niskiego ilorazu inteligencji bierze udział w najważniejszych wydarzeniach w historii USA.', 'uploads/posters/forrest-gump.jpg', 1994, 'available', 3.00, '2025-10-28 15:39:34'),
(6, 'Skazani na Shawshank', 'Adaptacja opowiadania Stephena Kinga. Niesłusznie skazany na dożywocie bankier, Andy Dufresne, stara się przetrwać w więzieniu, zachowując nadzieję.', 'uploads/posters/shawshank-redemption.jpg', 1994, 'available', 0.00, '2025-10-28 15:39:34'),
(7, 'Ojciec Chrzestny', 'Starzejący się patriarcha potężnej, nowojorskiej rodziny mafijnej, Vito Corleone, postanawia przekazać kontrolę nad swoim imperium. Kiedy jego najmłodszy syn, Michael, początkowo niechętny rodzinie, zostaje wciągnięty w brutalny świat przestępczości i zdrady, musi przejąć rolę ojca i stać się nowym, bezwzględnym Donem.', 'uploads/posters/godfather.jpg', 1972, 'available', 3.00, '2025-11-05 14:56:22'),
(8, 'Pulp Fiction', 'Losy dwóch płatnych morderców, żony ich szefa, boksera, który miał przegrać walkę, oraz pary drobnych rabusiów splatają się w serii nieprzewidywalnych, pełnych czarnego humoru i przemocy zdarzeń. Film opowiada trzy pozornie oddzielne historie, które łączą się w nieliniowej narracji.', 'uploads/posters/pulpfiction.jpg', 1994, 'available', 6.00, '2025-11-05 14:56:22'),
(9, 'Mroczny Rycerz', 'Batman, z pomocą porucznika Gordona i prokuratora Harveya Denta, kontynuuje swoją misję oczyszczenia Gotham z przestępczości. Na ich drodze staje jednak nowy, genialny i anarchiczny złoczyńca znany jako Joker, który pogrąża miasto w chaosie i zmusza Mrocznego Rycerza do przekroczenia cienkiej granicy między bohaterem a mścicielem.', 'uploads/posters/darkknight.jpg', 2008, 'available', 3.00, '2025-11-05 14:56:22'),
(10, 'Spirited Away: W krainie bogów', 'Dziesięcioletnia Chihiro, podczas przeprowadzki do nowego domu, trafia do magicznej krainy zamieszkanej przez duchy, bogów i potwory. Gdy jej rodzice zostają zamienieni w świnie przez potężną czarownicę Yubabę, dziewczynka musi podjąć pracę w niezwykłej łaźni dla bóstw, aby znaleźć sposób na uratowanie rodziny i powrót do świata ludzi.', 'uploads/posters/spiritedaway.jpg', 2001, 'available', 3.00, '2025-11-05 14:56:22'),
(11, 'Dwunastu gniewnych ludzi', 'Dwunastu przysięgłych zbiera się w dusznym pokoju, aby zadecydować o winie lub niewinności młodego chłopaka oskarżonego o morderstwo ojca. Kiedy jedenastu z nich jest gotowych na szybki werdykt skazujący, jeden przysięgły (Juror nr 8) postanawia samotnie przeciwstawić się reszcie, argumentując, że sprawa wymaga głębszej analizy dowodów i usunięcia wszelkich \'uzasadnionych wątpliwości\'.', 'uploads/posters/12angrymen.jpg', 1957, 'available', 0.00, '2025-11-05 14:56:22'),
(12, 'Lista Schindlera', 'Oparta na faktach historia Oskara Schindlera, niemieckiego przemysłowca i członka NSDAP, który podczas II wojny światowej ratuje ponad tysiąc Żydów przed śmiercią w obozie koncentracyjnym. Zatrudniając ich w swojej fabryce emaliowanych naczyń w Krakowie, Schindler ryzykuje własnym życiem i majątkiem, aby ocalić jak najwięcej osób.', 'uploads/posters/schindlerslist.jpg', 1993, 'available', 4.00, '2025-11-05 14:56:22'),
(13, 'Władca Pierścieni: Powrót Króla', 'Ostatnia część trylogii. Podczas gdy Aragorn jednoczy siły dobra, aby stoczyć ostateczną bitwę z armiami Saurona, hobbici Frodo i Sam kontynuują swoją desperacką misję do wnętrza Mordoru. Ich celem jest Góra Przeznaczenia – jedyne miejsce, w którym można zniszczyć Pierścień Władzy i pokonać Władcę Ciemności.', 'uploads/posters/rotk.jpg', 2003, 'available', 31.00, '2025-11-05 14:56:22'),
(14, 'The Matrix', 'Haker komputerowy Neo odkrywa, że świat, który zna, jest jedynie zaawansowaną symulacją komputerową stworzoną przez maszyny. Dołącza do Morfeusza i grupy rebeliantów, aby walczyć o wolność ludzkości.', 'uploads/posters/matrix.jpg', 1999, 'available', 1.00, '2025-11-05 15:17:08'),
(15, 'Interstellar', 'W niedalekiej przyszłości Ziemia staje się niezdatna do życia. Były pilot NASA, Cooper, wyrusza w desperacką misję przez tunel czasoprzestrzenny, aby znaleźć nowy dom dla ludzkości, pozostawiając za sobą swoje dzieci.', 'uploads/posters/interstellar.jpg', 2014, 'available', 1.00, '2025-11-05 15:17:08'),
(16, 'Szeregowiec Ryan', 'Po brutalnym lądowaniu w Normandii podczas II wojny światowej, kapitan John Miller otrzymuje rozkaz poprowadzenia swojego oddziału za linię wroga. Ich misją jest odnalezienie i bezpieczne sprowadzenie do domu szeregowca Jamesa Ryana, którego trzej bracia zginęli już na froncie.', 'uploads/posters/savingprivateryan.jpg', 1998, 'available', 0.00, '2025-11-05 15:17:08'),
(17, 'Chłopcy z ferajny', 'Oparta na faktach historia Henry\'ego Hilla, który od najmłodszych lat wspina się po szczeblach mafijnej kariery w Nowym Jorku. Film ukazuje brutalną rzeczywistość, bogactwo i ostateczny upadek życia w zorganizowanej przestępczości.', 'uploads/posters/goodfellas.jpg', 1990, 'available', 25.00, '2025-11-05 15:17:08'),
(18, 'Król Lew', 'Młody lew, Simba, następca tronu Lwiej Ziemi, zostaje oszukany przez swojego podstępnego wuja, Skazę, i ucieka na wygnanie. Z pomocą przyjaciół, Timona i Pumby, Simba musi dorosnąć i wrócić, by odzyskać swoje prawowite miejsce w \'kręgu życia\'.', 'uploads/posters/lionking.jpg', 1994, 'available', 1.00, '2025-11-05 15:17:08'),
(19, 'Lśnienie', 'Pisarz Jack Torrance przyjmuje posadę zimowego stróża w odcięto od świata hotelu Overlook. Zabiera ze sobą żonę i syna, ale złowroga siła obecna w hotelu oraz przerażająca izolacja powoli doprowadzają go do obłędu.', 'uploads/posters/shining.jpg', 1980, 'available', 1.00, '2025-11-05 15:17:08'),
(20, 'Łowca androidów', 'W dystopijnym Los Angeles 2019 roku, detektyw Rick Deckard, znany jako \'łowca androidów\', zostaje zmuszony do powrotu ze emerytury. Jego zadaniem jest wytropienie i \'eliminowanie\' grupy zbiegłych, zaawansowanych replikantów, którzy przybyli na Ziemię.', 'uploads/posters/bladerunner.jpg', 1982, 'available', 1.00, '2025-11-05 15:17:08'),
(21, 'Amelia', 'Młoda, ekscentryczna kelnerka z paryskiej dzielnicy Montmartre, Amelia Poulain, postanawia w sekrecie pomagać ludziom wokół siebie i naprawiać ich życia. W trakcie tej misji odkrywa miłość w najmniej oczekiwanym momencie.', 'uploads/posters/amelie.jpg', 2001, 'available', 1.00, '2025-11-05 15:17:08'),
(22, 'Casablanca', 'Cyniczny amerykański emigrant, Rick Blaine, prowadzi popularny klub w kontrolowanej przez Francję Vichy Casablance podczas II wojny światowej. Niespodziewanie w jego życiu ponownie pojawia się dawna miłość, Ilsa Lund, która wraz z mężem, przywódcą ruchu oporu, desperacko potrzebuje jego pomocy w ucieczce do Ameryki.', 'uploads/posters/casablanca.jpg', 1942, 'available', 27.00, '2025-11-05 15:17:08'),
(23, 'Poszukiwacze zaginionej Arki', 'Nieustraszony archeolog i poszukiwacz przygód, Indiana Jones, zostaje wynajęty przez rząd USA, aby odnaleźć legendarną Arkę Przymierza. Musi zdążyć, zanim potężny artefakt wpadnie w ręce nazistów, którzy chcą wykorzystać jego moc do zdobycia władzy nad światem.', 'uploads/posters/raiders.jpg', 1981, 'available', 0.00, '2025-11-05 15:17:08'),
(24, 'Transformers: Początek', 'Animowany film opowiada o przyjaźni między Optimusem Prime a Megatronem, która przerodziła się we wrogość, oraz o początkach wojny między Autobotami a Deceptikonami.', 'uploads/posters/6918cabbe7ac6_transformers-one.jpg', 2024, 'available', 23.00, '2025-11-15 18:47:23'),
(25, 'Wicked', 'Kraina Oz. Elphaba, odrzucona przez ludzi ze względu na zielony kolor skóry, niespodziewanie nawiązuje na Uniwersytecie Shiz przyjaźń z popularną studentką Galindą.', 'uploads/posters/691de2616a95c_8151360.png', 2024, 'available', 0.00, '2025-11-19 15:29:37'),
(26, 'Iluzja 3', 'Legendarni Czterej Jeźdźcy łączą siły z grupą młodych, zbuntowanych iluzjonistów, by dokonać zuchwałej kradzieży bezcennego diamentu i pokrzyżować szyki międzynarodowej organizacji przestępczej.', 'uploads/posters/iluzja3.png', 2025, 'upcoming', 2.00, '2025-11-29 16:17:43'),
(27, 'Wicked: Na dobre', 'Ostatni rozdział nieopowiedzianej historii czarownic z Oz rozpoczyna się, gdy Elphaba i Glinda są skłócone i żyją z konsekwencjami swoich decyzji.', 'uploads/posters/wickednadobre.png', 2025, 'upcoming', 2.00, '2025-11-29 16:17:43'),
(28, 'Norymberga', 'Amerykański psychiatra wchodzi w psychologiczną grę z Hermannem Göringiem, by zdobyć niezbite dowody jego winy.', 'uploads/posters/norymberga.png', 2025, 'upcoming', 2.00, '2025-11-29 16:17:43');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `movie_directors`
--

CREATE TABLE `movie_directors` (
  `movie_id` int(11) NOT NULL,
  `director_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_directors`
--

INSERT INTO `movie_directors` (`movie_id`, `director_id`) VALUES
(0, 23),
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 1),
(10, 9),
(11, 10),
(12, 11),
(13, 12),
(14, 13),
(14, 14),
(15, 1),
(16, 11),
(17, 15),
(18, 16),
(18, 17),
(19, 18),
(20, 19),
(21, 20),
(22, 21),
(23, 11),
(24, 22),
(25, 24),
(26, 23),
(27, 24),
(28, 25);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `movie_genres`
--

CREATE TABLE `movie_genres` (
  `movie_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_genres`
--

INSERT INTO `movie_genres` (`movie_id`, `genre_id`) VALUES
(0, 2),
(1, 1),
(2, 1),
(3, 2),
(4, 2),
(5, 3),
(6, 3),
(7, 3),
(8, 3),
(9, 3),
(10, 4),
(11, 3),
(12, 3),
(13, 5),
(14, 1),
(15, 1),
(16, 6),
(17, 3),
(18, 4),
(19, 7),
(20, 1),
(21, 8),
(22, 3),
(23, 9),
(24, 4),
(25, 9),
(25, 11),
(26, 2),
(27, 9),
(27, 11),
(28, 3);

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
  `rating_type` enum('user','critic') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `movie_id`, `rating`, `comment`, `rating_type`, `created_at`) VALUES
(126, 2, 1, 5.0, 'Film ma swoje mocne i słabe strony. Początek był obiecujący, ale w połowie akcja trochę zwolniła. Koncepcja jest ciekawa, ale chyba nie została w pełni wykorzystana. Nie żałuję czasu spędzonego na oglądaniu, ale też nie jest to pozycja, do której będę wracać.', 'critic', '2024-11-06 11:14:59'),
(127, 1, 1, 10.0, 'Rewelacja! 10/10!', 'critic', '2020-07-21 06:25:22'),
(182, 4, 5, 10.0, '0', 'user', '2025-11-10 18:37:12'),
(183, 3, 1, 4.0, 'Bardzo dobry film! Fabuła jest intrygująca i dobrze poprowadzona, choć momentami tempo mogłoby być nieco szybsze. Aktorzy świetnie poradzili sobie ze swoimi rolami, zwłaszcza główny bohater. Wizualnie prezentuje się znakomicie. Zdecydowanie warto obejrzeć.', 'critic', '2020-07-21 06:25:22'),
(225, 4, 13, 10.0, 'test', 'user', '2025-11-15 17:26:00'),
(227, 4, 9, 6.0, 'dskajadjhajbha', 'user', '2025-11-15 17:26:30'),
(241, 4, 3, 7.0, 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.', 'user', '2025-11-15 18:23:44'),
(242, 4, 8, 10.0, 'Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing industries for previewing layouts and visual mockups.', 'user', '2025-11-15 18:23:55'),
(245, 5, 4, 8.0, 'dsadsdaa', 'critic', '2025-11-15 21:57:16'),
(246, 5, 1, 6.0, 'test', 'user', '2025-11-15 22:01:30'),
(249, 4, 1, 10.0, 'Ten film to absolutne arcydzieło! Od pierwszej do ostatniej minuty trzymał mnie w napięciu, a zakończenie po prostu zwaliło mnie z nóg. Aktorstwo na najwyższym poziomie, reżyseria genialna, a ścieżka dźwiękowa idealnie dopełnia całość. Polecam każdemu, kto szuka niezapomnianych wrażeń kinowych!', 'user', '2025-11-23 15:11:22'),
(1001, 7, 1, 6.0, 'Film mocno przereklamowany. Dłużyzny zabijają klimat.', 'critic', '2025-11-23 16:35:14'),
(1002, 7, 2, 4.0, 'Efekty specjalne nie przykryją dziur w scenariuszu.', 'critic', '2025-11-23 16:35:14'),
(1003, 7, 3, 7.0, 'Nie jest źle, ale do arcydzieła brakuje bardzo dużo.', 'critic', '2025-11-23 16:35:14'),
(1004, 7, 4, 3.0, 'Przewidywalny gniot. Szkoda czasu.', 'critic', '2025-11-23 16:35:14'),
(1005, 7, 5, 2.0, 'Ledwo dotrwałem do końca. Totalne nieporozumienie.', 'critic', '2025-11-23 16:35:14'),
(1006, 7, 6, 8.0, 'Jeden z niewielu filmów w tym roku, który ma sens.', 'critic', '2025-11-23 16:35:14'),
(1007, 7, 7, 5.0, 'Technicznie poprawny, ale emocjonalnie pusty.', 'critic', '2025-11-23 16:35:14'),
(1008, 7, 8, 4.0, 'Dialogi drewniane, aktorzy grają jak za karę.', 'critic', '2025-11-23 16:35:14'),
(1009, 7, 9, 6.0, 'Miał potencjał, ale końcówka wszystko zepsuła.', 'critic', '2025-11-23 16:35:14'),
(1010, 7, 10, 2.0, 'Kto dał pieniądze na tę produkcję?', 'critic', '2025-11-23 16:35:14'),
(1011, 7, 11, 7.0, 'Solidne rzemiosło, choć bez błysku geniuszu.', 'critic', '2025-11-23 16:35:14'),
(1012, 7, 12, 5.0, 'Zbyt sentymentalny, wręcz kiczowaty.', 'critic', '2025-11-23 16:35:14'),
(1013, 7, 13, 8.0, 'Zaskakująco dobry scenariusz.', 'critic', '2025-11-23 16:35:14'),
(1014, 7, 14, 3.0, 'Chaotyczny montaż, bolała mnie głowa.', 'critic', '2025-11-23 16:35:14'),
(1015, 7, 15, 6.0, 'Można obejrzeć, ale zapomina się po godzinie.', 'critic', '2025-11-23 16:35:14'),
(1016, 7, 16, 4.0, 'Próba bycia ambitnym na siłę. Nie wyszło.', 'critic', '2025-11-23 16:35:14'),
(1017, 7, 17, 7.0, 'Dobra gra aktorska ratuje słabą historię.', 'critic', '2025-11-23 16:35:14'),
(1018, 7, 18, 2.0, 'Infantylny humor dla nikogo.', 'critic', '2025-11-23 16:35:14'),
(1019, 7, 19, 5.0, 'Horror, który w ogóle nie straszy.', 'critic', '2025-11-23 16:35:14'),
(1020, 7, 20, 6.0, 'Ciekawy klimat, ale tempo ślimaka.', 'critic', '2025-11-23 16:35:14'),
(1021, 7, 21, 3.0, 'Komedia, na której ani razu się nie zaśmiałem.', 'critic', '2025-11-23 16:35:14'),
(1022, 7, 22, 8.0, 'Piękne zdjęcia, warto dla samej estetyki.', 'critic', '2025-11-23 16:35:14'),
(1023, 7, 23, 4.0, 'Kolejny sequel zrobiony tylko dla kasy.', 'critic', '2025-11-23 16:35:14'),
(1024, 7, 24, 1.0, 'To obraza dla inteligencji widza.', 'critic', '2025-11-23 16:35:14'),
(1025, 7, 25, 5.0, 'Przeciętniak, jakich wiele w telewizji.', 'critic', '2025-11-23 16:35:14'),
(1026, 8, 1, 10.0, 'Absolutne arcydzieło! Takiego kina już się nie robi.', 'critic', '2025-11-23 16:35:14'),
(1027, 8, 2, 7.0, 'Dobre kino rozrywkowe, popcorn smakował lepiej.', 'critic', '2025-11-23 16:35:14'),
(1028, 8, 3, 9.0, 'Poruszający, głęboki, zostaje w pamięci.', 'critic', '2025-11-23 16:35:14'),
(1029, 8, 4, 5.0, 'Mocny średniak. Do obejrzenia i zapomnienia.', 'critic', '2025-11-23 16:35:14'),
(1030, 8, 5, 4.0, 'Niestety, rozczarowanie roku.', 'critic', '2025-11-23 16:35:14'),
(1031, 8, 6, 10.0, 'Genialna reżyseria. Chylę czoła.', 'critic', '2025-11-23 16:35:14'),
(1032, 8, 7, 8.0, 'Wciągająca historia, choć środek trochę siada.', 'critic', '2025-11-23 16:35:14'),
(1033, 8, 8, 6.0, 'Ciekawy pomysł, ale wykonanie kuleje.', 'critic', '2025-11-23 16:35:14'),
(1034, 8, 9, 9.0, 'Niesamowity klimat i muzyka!', 'critic', '2025-11-23 16:35:14'),
(1035, 8, 10, 3.0, 'Nuda, nuda i jeszcze raz nuda.', 'critic', '2025-11-23 16:35:14'),
(1036, 8, 11, 8.0, 'Trzyma w napięciu do ostatniej minuty.', 'critic', '2025-11-23 16:35:14'),
(1037, 8, 12, 9.0, 'Wzruszyłem się. Piękne kino.', 'critic', '2025-11-23 16:35:14'),
(1038, 8, 13, 10.0, 'Epickie zwieńczenie trylogii.', 'critic', '2025-11-23 16:35:14'),
(1039, 8, 14, 6.0, 'Kontrowersyjny, ale daje do myślenia.', 'critic', '2025-11-23 16:35:14'),
(1040, 8, 15, 5.0, 'Zbyt skomplikowany, zgubiłem wątek.', 'critic', '2025-11-23 16:35:14'),
(1041, 8, 16, 4.0, 'Zbyt brutalny, ciężko się to ogląda.', 'critic', '2025-11-23 16:35:14'),
(1042, 8, 17, 9.0, 'Klasyka gatunku w nowym wydaniu.', 'critic', '2025-11-23 16:35:14'),
(1043, 8, 18, 7.0, 'Przyjemna animacja dla całej rodziny.', 'critic', '2025-11-23 16:35:14'),
(1044, 8, 19, 8.0, 'Dawno się tak nie bałem. Polecam!', 'critic', '2025-11-23 16:35:14'),
(1045, 8, 20, 6.0, 'Artystyczny, ale trudny w odbiorze.', 'critic', '2025-11-23 16:35:14'),
(1046, 8, 21, 5.0, 'Lekki, ale żarty bardzo suche.', 'critic', '2025-11-23 16:35:14'),
(1047, 8, 22, 10.0, 'Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów. Romans wszech czasów.', 'critic', '2025-11-23 16:35:14'),
(1048, 8, 23, 7.0, 'Dobra przygoda, czuć ducha oryginału.', 'critic', '2025-11-23 16:35:14'),
(1049, 8, 24, 2.0, 'Katastrofa realizacyjna.', 'critic', '2025-11-23 16:35:14'),
(1050, 8, 25, 6.0, 'Muzyka świetna, ale fabuła banalna.', 'critic', '2025-11-23 16:35:14'),
(1051, 9, 1, 9.0, 'Wybity, wielowarstwowy dramat.', 'critic', '2025-11-23 16:35:14'),
(1052, 9, 2, 2.0, 'Hałas i wybuchy dla mas. Puste.', 'critic', '2025-11-23 16:35:14'),
(1053, 9, 3, 10.0, 'Metafizyczne doświadczenie.', 'critic', '2025-11-23 16:35:14'),
(1054, 9, 4, 4.0, 'Typowe amerykańskie kino akcji. Nuda.', 'critic', '2025-11-23 16:35:14'),
(1055, 9, 5, 3.0, 'Komercyjna papka.', 'critic', '2025-11-23 16:35:14'),
(1056, 9, 6, 9.0, 'Inteligentny scenariusz.', 'critic', '2025-11-23 16:35:14'),
(1057, 9, 7, 8.0, 'Klasyczna forma w najlepszym wydaniu.', 'critic', '2025-11-23 16:35:14'),
(1058, 9, 8, 5.0, 'Zbyt dosłowny, brak mu subtelności.', 'critic', '2025-11-23 16:35:14'),
(1059, 9, 9, 7.0, 'Jak na ten gatunek, całkiem nieźle.', 'critic', '2025-11-23 16:35:14'),
(1060, 9, 10, 6.0, 'Ciekawy eksperyment formalny.', 'critic', '2025-11-23 16:35:14'),
(1061, 9, 11, 8.0, 'Psychologicznie wiarygodny.', 'critic', '2025-11-23 16:35:14'),
(1062, 9, 12, 9.0, 'Poezja ekranu.', 'critic', '2025-11-23 16:35:14'),
(1063, 9, 13, 5.0, 'Za dużo patosu i efektów CGI.', 'critic', '2025-11-23 16:35:14'),
(1064, 9, 14, 7.0, 'Intrygująca wizja przyszłości.', 'critic', '2025-11-23 16:35:14'),
(1065, 9, 15, 8.0, 'Wizualnie zachwycający.', 'critic', '2025-11-23 16:35:14'),
(1066, 9, 16, 6.0, 'Realizm przytłacza, ale doceniam.', 'critic', '2025-11-23 16:35:14'),
(1067, 9, 17, 8.0, 'Świetne studium upadku człowieka.', 'critic', '2025-11-23 16:35:14'),
(1068, 9, 18, 4.0, 'Tylko dla dzieci, dorosły zaśnie.', 'critic', '2025-11-23 16:35:14'),
(1069, 9, 19, 7.0, 'Ciekawa dekonstrukcja horroru.', 'critic', '2025-11-23 16:35:14'),
(1070, 9, 20, 9.0, 'Klimat gęsty jak smoła. Rewelacja.', 'critic', '2025-11-23 16:35:14'),
(1071, 9, 21, 3.0, 'Prostacki humor.', 'critic', '2025-11-23 16:35:14'),
(1072, 9, 22, 10.0, 'Ponadczasowe piękno.', 'critic', '2025-11-23 16:35:14'),
(1073, 9, 23, 4.0, 'Odgrzewany kotlet.', 'critic', '2025-11-23 16:35:14'),
(1074, 9, 24, 1.0, 'Dno artystyczne.', 'critic', '2025-11-23 16:35:14'),
(1075, 9, 25, 5.0, 'Taki sobie musical.', 'critic', '2025-11-23 16:35:14'),
(1076, 10, 1, 8.0, 'Bardzo solidne kino.', 'critic', '2025-11-23 16:35:14'),
(1077, 10, 2, 6.0, 'Może być, ale bez szału.', 'critic', '2025-11-23 16:35:14'),
(1078, 10, 3, 7.0, 'Dobry, ale trochę przydługi.', 'critic', '2025-11-23 16:35:14'),
(1079, 10, 4, 5.0, 'Średni. Spodziewałem się więcej.', 'critic', '2025-11-23 16:35:14'),
(1080, 10, 5, 4.0, 'Słaby scenariusz.', 'critic', '2025-11-23 16:35:14'),
(1081, 10, 6, 9.0, 'Świetny, naprawdę warto.', 'critic', '2025-11-23 16:35:14'),
(1082, 10, 7, 8.0, 'Dobre aktorstwo.', 'critic', '2025-11-23 16:35:14'),
(1083, 10, 8, 7.0, 'Ciekawa historia.', 'critic', '2025-11-23 16:35:14'),
(1084, 10, 9, 8.0, 'Bawiłem się nieźle.', 'critic', '2025-11-23 16:35:14'),
(1085, 10, 10, 5.0, 'Mieszane uczucia.', 'critic', '2025-11-23 16:35:14'),
(1086, 10, 11, 7.0, 'Solidny thriller.', 'critic', '2025-11-23 16:35:14'),
(1087, 10, 12, 8.0, 'Ładny film.', 'critic', '2025-11-23 16:35:14'),
(1088, 10, 13, 9.0, 'Wielkie widowisko.', 'critic', '2025-11-23 16:35:14'),
(1089, 10, 14, 6.0, 'Ciekawy pomysł, gorzej z realizacją.', 'critic', '2025-11-23 16:35:14'),
(1090, 10, 15, 7.0, 'Warto zobaczyć.', 'critic', '2025-11-23 16:35:14'),
(1091, 10, 16, 5.0, 'Trochę męczący.', 'critic', '2025-11-23 16:35:14'),
(1092, 10, 17, 8.0, 'Dobre kino gangsterskie.', 'critic', '2025-11-23 16:35:14'),
(1093, 10, 18, 6.0, 'Sympatyczny.', 'critic', '2025-11-23 16:35:14'),
(1094, 10, 19, 7.0, 'Ma momenty.', 'critic', '2025-11-23 16:35:14'),
(1095, 10, 20, 6.0, 'Specyficzny klimat.', 'critic', '2025-11-23 16:35:14'),
(1096, 10, 21, 5.0, 'Średnia komedia.', 'critic', '2025-11-23 16:35:14'),
(1097, 10, 22, 9.0, 'Klasyk.', 'critic', '2025-11-23 16:35:14'),
(1098, 10, 23, 6.0, 'Tylko dla fanów serii.', 'critic', '2025-11-23 16:35:14'),
(1099, 10, 24, 2.0, 'Słabizna.', 'critic', '2025-11-23 16:35:14'),
(1100, 10, 25, 6.0, 'Poprawny.', 'critic', '2025-11-23 16:35:14'),
(1101, 11, 1, 10.0, 'Genialny! 10/10!', 'critic', '2025-11-23 16:35:14'),
(1102, 11, 2, 9.0, 'Świetna zabawa!', 'critic', '2025-11-23 16:35:14'),
(1103, 11, 3, 10.0, 'Musicie to zobaczyć!', 'critic', '2025-11-23 16:35:14'),
(1104, 11, 4, 8.0, 'Bardzo dobry film akcji.', 'critic', '2025-11-23 16:35:14'),
(1105, 11, 5, 7.0, 'Całkiem przyjemny seans.', 'critic', '2025-11-23 16:35:14'),
(1106, 11, 6, 10.0, 'Rewelacja roku!', 'critic', '2025-11-23 16:35:14'),
(1107, 11, 7, 9.0, 'Wspaniały!', 'critic', '2025-11-23 16:35:14'),
(1108, 11, 8, 8.0, 'Bardzo mi się podobał.', 'critic', '2025-11-23 16:35:14'),
(1109, 11, 9, 9.0, 'Super!', 'critic', '2025-11-23 16:35:14'),
(1110, 11, 10, 7.0, 'Fajny, polecam.', 'critic', '2025-11-23 16:35:14'),
(1111, 11, 11, 9.0, 'Niesamowite napięcie!', 'critic', '2025-11-23 16:35:14'),
(1112, 11, 12, 10.0, 'Wyciskacz łez.', 'critic', '2025-11-23 16:35:14'),
(1113, 11, 13, 10.0, 'Monumentalne dzieło.', 'critic', '2025-11-23 16:35:14'),
(1114, 11, 14, 8.0, 'Ciekawy i wciągający.', 'critic', '2025-11-23 16:35:14'),
(1115, 11, 15, 9.0, 'Piękna wizja.', 'critic', '2025-11-23 16:35:14'),
(1116, 11, 16, 7.0, 'Mocny, ale dobry.', 'critic', '2025-11-23 16:35:14'),
(1117, 11, 17, 9.0, 'Świetny scenariusz.', 'critic', '2025-11-23 16:35:14'),
(1118, 11, 18, 8.0, 'Uroczy.', 'critic', '2025-11-23 16:35:14'),
(1119, 11, 19, 9.0, 'Strach się bać!', 'critic', '2025-11-23 16:35:14'),
(1120, 11, 20, 8.0, 'Niesamowity klimat.', 'critic', '2025-11-23 16:35:14'),
(1121, 11, 21, 7.0, 'Śmieszny.', 'critic', '2025-11-23 16:35:14'),
(1122, 11, 22, 10.0, 'Arcydzieło.', 'critic', '2025-11-23 16:35:14'),
(1123, 11, 23, 9.0, 'Super przygoda.', 'critic', '2025-11-23 16:35:14'),
(1124, 11, 24, 4.0, 'No, to akurat nie wyszło.', 'critic', '2025-11-23 16:35:14'),
(1125, 11, 25, 8.0, 'Bardzo przyjemny.', 'critic', '2025-11-23 16:35:14'),
(1126, 12, 1, 8.0, 'Solidne 8 gwiazdek. Warto.', 'user', '2025-11-23 16:35:14'),
(1127, 12, 2, 6.0, 'Może być na wieczór z piwkiem.', 'user', '2025-11-23 16:35:14'),
(1128, 12, 3, 5.0, 'Trochę przynudzał momentami.', 'user', '2025-11-23 16:35:14'),
(1129, 12, 4, 7.0, 'Fajne strzelaniny, fabuła taka sobie.', 'user', '2025-11-23 16:35:14'),
(1130, 12, 5, 3.0, 'Zmarnowany potencjał.', 'user', '2025-11-23 16:35:14'),
(1131, 12, 6, 9.0, 'Naprawdę dobry film.', 'user', '2025-11-23 16:35:14'),
(1132, 12, 7, 7.0, 'Stare kino, ale daje radę.', 'user', '2025-11-23 16:35:14'),
(1133, 12, 8, 6.0, 'Obejrzałem i zapomniałem.', 'user', '2025-11-23 16:35:14'),
(1134, 12, 9, 8.0, 'Fajny Batman.', 'user', '2025-11-23 16:35:14'),
(1135, 12, 10, 4.0, 'Dziwny jakiś.', 'user', '2025-11-23 16:35:14'),
(1136, 12, 11, 7.0, 'Trzyma w napięciu.', 'user', '2025-11-23 16:35:14'),
(1137, 12, 12, 8.0, 'Dziewczynie się podobał, mi też.', 'user', '2025-11-23 16:35:14'),
(1138, 12, 13, 9.0, 'Efekty robią robotę.', 'user', '2025-11-23 16:35:14'),
(1139, 12, 14, 5.0, 'Przekombinowany.', 'user', '2025-11-23 16:35:14'),
(1140, 12, 15, 6.0, 'Ładne widoczki.', 'user', '2025-11-23 16:35:14'),
(1141, 12, 16, 4.0, 'Za brutalny dla mnie.', 'user', '2025-11-23 16:35:14'),
(1142, 12, 17, 8.0, 'Dobre gangsterskie kino.', 'user', '2025-11-23 16:35:14'),
(1143, 12, 18, 7.0, 'Dzieciaki zadowolone.', 'user', '2025-11-23 16:35:14'),
(1144, 12, 19, 6.0, 'Może być, ale mało straszny.', 'user', '2025-11-23 16:35:14'),
(1145, 12, 20, 5.0, 'Nic z tego nie zrozumiałem.', 'user', '2025-11-23 16:35:14'),
(1146, 12, 21, 6.0, 'Parę razy się uśmiechnąłem.', 'user', '2025-11-23 16:35:14'),
(1147, 12, 22, 7.0, 'Klasyk to klasyk.', 'user', '2025-11-23 16:35:14'),
(1148, 12, 23, 7.0, 'Fajna przygodówka.', 'user', '2025-11-23 16:35:14'),
(1149, 12, 24, 2.0, 'Szkoda czasu, serio.', 'user', '2025-11-23 16:35:14'),
(1150, 12, 25, 6.0, 'Nie lubię musicali, ale uszło.', 'user', '2025-11-23 16:35:14'),
(1151, 13, 1, 4.0, 'Wszyscy się zachwycają, a to nuda.', 'user', '2025-11-23 16:35:14'),
(1152, 13, 2, 2.0, 'Głupi film dla głupich ludzi.', 'user', '2025-11-23 16:35:14'),
(1153, 13, 3, 3.0, 'Pretensjonalny bełkot.', 'user', '2025-11-23 16:35:14'),
(1154, 13, 4, 2.0, 'Dno i wodorosty.', 'user', '2025-11-23 16:35:14'),
(1155, 13, 5, 1.0, 'Wyłączyłem po 20 minutach.', 'user', '2025-11-23 16:35:14'),
(1156, 13, 6, 5.0, 'Średni, nie wiem o co ten szum.', 'user', '2025-11-23 16:35:14'),
(1157, 13, 7, 4.0, 'Zestarzał się fatalnie.', 'user', '2025-11-23 16:35:14'),
(1158, 13, 8, 3.0, 'Słaba gra aktorska.', 'user', '2025-11-23 16:35:14'),
(1159, 13, 9, 6.0, 'Jedyny znośny film z tej serii.', 'user', '2025-11-23 16:35:14'),
(1160, 13, 10, 2.0, 'Co ja właśnie obejrzałem? Tragedia.', 'user', '2025-11-23 16:35:14'),
(1161, 13, 11, 4.0, 'Przewidywalny do bólu.', 'user', '2025-11-23 16:35:14'),
(1162, 13, 12, 5.0, 'Tani wyciskacz łez.', 'user', '2025-11-23 16:35:14'),
(1163, 13, 13, 6.0, 'Za dużo CGI, za mało treści.', 'user', '2025-11-23 16:35:14'),
(1164, 13, 14, 2.0, 'Strata pieniędzy na bilet.', 'user', '2025-11-23 16:35:14'),
(1165, 13, 15, 3.0, 'Nudne to jak flaki z olejem.', 'user', '2025-11-23 16:35:14'),
(1166, 13, 16, 2.0, 'Ohydny film.', 'user', '2025-11-23 16:35:14'),
(1167, 13, 17, 5.0, 'Wiele hałasu o nic.', 'user', '2025-11-23 16:35:14'),
(1168, 13, 18, 4.0, 'Disneya stać na więcej.', 'user', '2025-11-23 16:35:14'),
(1169, 13, 19, 3.0, 'Bardziej śmieszny niż straszny.', 'user', '2025-11-23 16:35:14'),
(1170, 13, 20, 2.0, 'Uśpij mnie, proszę.', 'user', '2025-11-23 16:35:14'),
(1171, 13, 21, 1.0, 'Żenujący humor.', 'user', '2025-11-23 16:35:14'),
(1172, 13, 22, 4.0, 'Czarno-biała nuda.', 'user', '2025-11-23 16:35:14'),
(1173, 13, 23, 3.0, 'Zniszczyli legendę Indiany.', 'user', '2025-11-23 16:35:14'),
(1174, 13, 24, 1.0, 'Najgorszy film świata!!!', 'user', '2025-11-23 16:35:14'),
(1175, 13, 25, 2.0, 'Nie trawię śpiewania w filmach.', 'user', '2025-11-23 16:35:14'),
(1176, 14, 1, 10.0, '10/10 bo lubię aktora.', 'user', '2025-11-23 16:35:14'),
(1177, 14, 2, 1.0, 'Tragedia.', 'user', '2025-11-23 16:35:14'),
(1178, 14, 3, 9.0, 'Dobry.', 'user', '2025-11-23 16:35:14'),
(1179, 14, 4, 1.0, 'Nie.', 'user', '2025-11-23 16:35:14'),
(1180, 14, 5, 2.0, 'Słabo.', 'user', '2025-11-23 16:35:14'),
(1181, 14, 6, 10.0, 'Mistrz!', 'user', '2025-11-23 16:35:14'),
(1182, 14, 7, 10.0, 'Król kina.', 'user', '2025-11-23 16:35:14'),
(1183, 14, 8, 5.0, 'Meh.', 'user', '2025-11-23 16:35:14'),
(1184, 14, 9, 8.0, 'Spoko.', 'user', '2025-11-23 16:35:14'),
(1185, 14, 10, 1.0, 'WTF?', 'user', '2025-11-23 16:35:14'),
(1186, 14, 11, 7.0, 'Ujdzie.', 'user', '2025-11-23 16:35:14'),
(1187, 14, 12, 10.0, 'Płakałem jak bóbr.', 'user', '2025-11-23 16:35:14'),
(1188, 14, 13, 9.0, 'Wow.', 'user', '2025-11-23 16:35:14'),
(1189, 14, 14, 3.0, 'Za trudny.', 'user', '2025-11-23 16:35:14'),
(1190, 14, 15, 8.0, 'Ładne.', 'user', '2025-11-23 16:35:14'),
(1191, 14, 16, 1.0, 'Obrzydliwe.', 'user', '2025-11-23 16:35:14'),
(1192, 14, 17, 9.0, 'Mafia górą.', 'user', '2025-11-23 16:35:14'),
(1193, 14, 18, 5.0, 'Dla dzieciaków.', 'user', '2025-11-23 16:35:14'),
(1194, 14, 19, 8.0, 'Creepy.', 'user', '2025-11-23 16:35:14'),
(1195, 14, 20, 2.0, 'Zzzzzz.', 'user', '2025-11-23 16:35:14'),
(1196, 14, 21, 4.0, 'Suchar.', 'user', '2025-11-23 16:35:14'),
(1197, 14, 22, 10.0, 'Love it.', 'user', '2025-11-23 16:35:14'),
(1198, 14, 23, 6.0, 'Może być.', 'user', '2025-11-23 16:35:14'),
(1199, 14, 24, 10.0, 'Tak złe, że aż dobre! Kultowe!', 'user', '2025-11-23 16:35:14'),
(1200, 14, 25, 3.0, 'Nie moje klimaty.', 'user', '2025-11-23 16:35:14'),
(1201, 15, 1, 9.0, 'Wspaniała historia o nadziei.', 'user', '2025-11-23 16:35:14'),
(1202, 15, 2, 7.0, 'Efekty super, ale fabuła kuleje.', 'user', '2025-11-23 16:35:14'),
(1203, 15, 3, 8.0, 'Bardzo angażujący emocjonalnie.', 'user', '2025-11-23 16:35:14'),
(1204, 15, 4, 6.0, 'Spodziewałem się więcej akcji.', 'user', '2025-11-23 16:35:14'),
(1205, 15, 5, 5.0, 'Film na raz. Nic specjalnego.', 'user', '2025-11-23 16:35:14'),
(1206, 15, 6, 10.0, 'Absolutny majstersztyk reżyserski.', 'user', '2025-11-23 16:35:14'),
(1207, 15, 7, 9.0, 'Mimo lat wciąż ogląda się świetnie.', 'user', '2025-11-23 16:35:14'),
(1208, 15, 8, 7.0, 'Dobra gra aktorska, ale scenariusz dziurawy.', 'user', '2025-11-23 16:35:14'),
(1209, 15, 9, 9.0, 'Joker skradł całe show.', 'user', '2025-11-23 16:35:14'),
(1210, 15, 10, 6.0, 'Wizualnie piękny, ale treść niezrozumiała.', 'user', '2025-11-23 16:35:14'),
(1211, 15, 11, 8.0, 'Siedziałem jak na szpilkach.', 'user', '2025-11-23 16:35:14'),
(1212, 15, 12, 9.0, 'Wzruszająca opowieść o życiu.', 'user', '2025-11-23 16:35:14'),
(1213, 15, 13, 9.0, 'Godne pożegnanie z bohaterami.', 'user', '2025-11-23 16:35:14'),
(1214, 15, 14, 7.0, 'Trochę zawiły, trzeba obejrzeć dwa razy.', 'user', '2025-11-23 16:35:14'),
(1215, 15, 15, 8.0, 'Epicka podróż przez kosmos.', 'user', '2025-11-23 16:35:14'),
(1216, 15, 16, 6.0, 'Mocne, brutalne kino, nie dla każdego.', 'user', '2025-11-23 16:35:14'),
(1217, 15, 17, 9.0, 'Ojciec Chrzestny to to nie jest, ale blisko.', 'user', '2025-11-23 16:35:14'),
(1218, 15, 18, 7.0, 'Bardzo sympatyczna bajka.', 'user', '2025-11-23 16:35:14'),
(1219, 15, 19, 8.0, 'Klimat grozy jest tutaj niesamowity.', 'user', '2025-11-23 16:35:14'),
(1220, 15, 20, 7.0, 'Film artystyczny, wymaga skupienia.', 'user', '2025-11-23 16:35:14'),
(1221, 15, 21, 6.0, 'Lekka komedia na niedzielne popołudnie.', 'user', '2025-11-23 16:35:14'),
(1222, 15, 22, 10.0, 'Casablanca zawsze w moim sercu.', 'user', '2025-11-23 16:35:14'),
(1223, 15, 23, 8.0, 'Dobre kino nowej przygody.', 'user', '2025-11-23 16:35:14'),
(1224, 15, 24, 3.0, 'Niestety, duży zawód.', 'user', '2025-11-23 16:35:14'),
(1225, 15, 25, 7.0, 'Fajna muzyka i choreografia.', 'user', '2025-11-23 16:35:14'),
(1226, 16, 1, 7.0, 'Dobry, ale bez przesady z tymi 10tkami.', 'user', '2025-11-23 16:35:14'),
(1227, 16, 2, 5.0, 'Taki tam blockbuster.', 'user', '2025-11-23 16:35:14'),
(1228, 16, 3, 8.0, 'Bardzo dobry film.', 'user', '2025-11-23 16:35:14'),
(1229, 16, 4, 4.0, 'Słabizna.', 'user', '2025-11-23 16:35:14'),
(1230, 16, 5, 6.0, 'Można obejrzeć.', 'user', '2025-11-23 16:35:14'),
(1231, 16, 6, 9.0, 'Świetny.', 'user', '2025-11-23 16:35:14'),
(1232, 16, 7, 8.0, 'Klasyk.', 'user', '2025-11-23 16:35:14'),
(1233, 16, 8, 5.0, 'Średni.', 'user', '2025-11-23 16:35:14'),
(1234, 16, 9, 10.0, 'Mój ulubiony!', 'user', '2025-11-23 16:35:14'),
(1235, 16, 10, 3.0, 'Nie podobał mi się.', 'user', '2025-11-23 16:35:14'),
(1236, 16, 11, 7.0, 'Okej.', 'user', '2025-11-23 16:35:14'),
(1237, 16, 12, 8.0, 'Warto.', 'user', '2025-11-23 16:35:14'),
(1238, 16, 13, 9.0, 'Super widowisko.', 'user', '2025-11-23 16:35:14'),
(1239, 16, 14, 4.0, 'Nudny.', 'user', '2025-11-23 16:35:14'),
(1240, 16, 15, 7.0, 'Ciekawy.', 'user', '2025-11-23 16:35:14'),
(1241, 16, 16, 5.0, 'Zbyt brutalny.', 'user', '2025-11-23 16:35:14'),
(1242, 16, 17, 8.0, 'Dobry.', 'user', '2025-11-23 16:35:14'),
(1243, 16, 18, 6.0, 'Dla dzieci ok.', 'user', '2025-11-23 16:35:14'),
(1244, 16, 19, 7.0, 'Może być.', 'user', '2025-11-23 16:35:14'),
(1245, 16, 20, 4.0, 'Zasnąłem.', 'user', '2025-11-23 16:35:14'),
(1246, 16, 21, 5.0, 'Mało śmieszny.', 'user', '2025-11-23 16:35:14'),
(1247, 16, 22, 9.0, 'Piękny.', 'user', '2025-11-23 16:35:14'),
(1248, 16, 23, 8.0, 'Fajna zabawa.', 'user', '2025-11-23 16:35:14'),
(1249, 16, 24, 2.0, 'Tragedia.', 'user', '2025-11-23 16:35:14'),
(1250, 16, 25, 6.0, 'Średni musical.', 'user', '2025-11-23 16:35:14'),
(1251, 6, 13, 10.0, 'Super', 'user', '2025-11-27 12:38:25'),
(1252, 5, 17, 10.0, 'dsa', 'user', '2025-12-14 17:29:30');

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

--
-- Dumping data for table `review_likes`
--

INSERT INTO `review_likes` (`id`, `user_id`, `rating_id`, `created_at`) VALUES
(11, 4, 227, '2025-11-19 15:32:40'),
(13, 5, 1047, '2025-11-23 16:57:18'),
(14, 5, 1067, '2025-11-23 17:16:32'),
(15, 5, 1117, '2025-11-23 17:16:33'),
(16, 5, 1001, '2025-11-27 14:06:29');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','critic','admin','owner') NOT NULL DEFAULT 'user',
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_banner_url` varchar(255) DEFAULT NULL,
  `critic_description` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_banned`, `phone_number`, `created_at`, `profile_banner_url`, `critic_description`, `avatar_url`) VALUES
(1, 'Wiktor Zawadzki', 'wiktorzawadzki007@gmail.com', '$2y$10$Nwx9Bxpd.5iT3pi1cBUc..SxbEpZq3SF9fmQzXjSnpccanGlqEOn6', 'user', 0, NULL, '2025-10-13 15:00:50', NULL, NULL, 'uploads/avatars/thumb-1920-926492.jpg'),
(2, 'Piotr Galik', 'h@d', '$2y$10$zQBeV3/L.elHQRuUuaBlXuYVo5XbFeeDeBj0Rhl96xFCr/zmb7jSO', 'user', 0, NULL, '2025-10-13 15:05:17', NULL, NULL, 'uploads/avatars/avatar1.png'),
(3, 'Gabriel Wojak', 'wiktorzawadzki@gmail.com', '$2y$10$ICUDLAA64J2gxf6xD1hpMOTFjtiAb3SR2SIsRMRxFlauivbtqFNRy', 'user', 0, NULL, '2025-10-13 15:31:49', NULL, NULL, 'uploads/avatars/avatar2.png'),
(4, 'Rafał Greb', 'test@gmail.com', '$2y$10$xJK3MsM6KGD9T41BpfUx7uZgACl21lKtmhBS/V9yRxf1WcWTNr/ie', 'admin', 0, NULL, '2025-11-09 21:08:29', 'uploads/posters/joker.jpg', NULL, 'uploads/avatars/4_1763909662.png'),
(5, 'Dominik Kwiatek', 'owner@gmail.com', '$2y$10$Wt3p2pXDc3dXfclktWINFOIN6WedupYmF.U7i5cqGMurgjMJ6tPbu', 'owner', 0, '123123123', '2025-11-15 19:47:08', 'uploads/posters/godfather.jpg', 'Naczelny fan polskich produkcji filmowych.', 'uploads/avatars/5_1763913377.webp'),
(6, 'Patryk Góralski', 'sesja@gmail.com', '$2y$10$.UxFoN1nAaL3Ikvo6bB4MO3aWatXhHl/cU009Hw8eunDSJY2L9cfK', 'user', 0, NULL, '2025-11-19 15:53:24', NULL, NULL, 'uploads/avatars/avatar3.png'),
(7, 'Jan Kowalski', 'jan.kowalski@example.com', '$2y$10$placeholderpassword', 'critic', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar4.png'),
(8, 'Anna Nowak', 'anna.nowak@example.com', '$2y$10$placeholderpassword', 'critic', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar5.png'),
(9, 'Piotr Wiśniewski', 'piotr.wisniewski@example.com', '$2y$10$placeholderpassword', 'critic', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar6.png'),
(10, 'Katarzyna Wójcik', 'katarzyna.wojcik@example.com', '$2y$10$placeholderpassword', 'critic', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar7.png'),
(11, 'Marcin Lewandowski', 'marcin.lewandowski@example.com', '$2y$10$placeholderpassword', 'critic', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar8.png'),
(12, 'Ewa Dąbrowska', 'ewa.dabrowska@example.com', '$2y$10$placeholderpassword', 'user', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar9.png'),
(13, 'Tomasz Zieliński', 'tomasz.zielinski@example.com', '$2y$10$placeholderpassword', 'user', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar10.png'),
(14, 'Magdalena Szymańska', 'magdalena.szymanska@example.com', '$2y$10$placeholderpassword', 'user', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar11.png'),
(15, 'Krzysztof Kozłowski', 'krzysztof.kozlowski@example.com', '$2y$10$placeholderpassword', 'user', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar12.png'),
(16, 'Agnieszka Jankowska', 'agnieszka.jankowska@example.com', '$2y$10$placeholderpassword', 'user', 0, NULL, '2025-11-23 16:17:06', NULL, NULL, 'uploads/avatars/avatar13.png'),
(17, '252841', '252841@gmail.com', '$2y$10$6NkPyeq5kiYNvDcsgmjLwe4ofZON5xcAO8U5VGTX6B3j.EgRKuj1y', 'user', 0, NULL, '2025-12-07 14:12:43', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_achievements`
--

INSERT INTO `user_achievements` (`id`, `user_id`, `achievement_id`, `earned_at`) VALUES
(1, 4, 4, '2025-11-19 14:53:31'),
(2, 4, 1, '2025-11-23 15:11:12'),
(3, 4, 2, '2025-11-23 15:11:12'),
(4, 5, 1, '2025-11-23 15:50:18'),
(5, 5, 2, '2025-11-23 15:50:18'),
(6, 6, 1, '2025-11-27 12:38:25'),
(7, 6, 2, '2025-11-27 12:38:25'),
(8, 1, 1, '2025-11-27 12:38:25'),
(9, 1, 2, '2025-11-27 12:38:25'),
(10, 1, 3, '2025-11-27 12:38:25'),
(11, 1, 4, '2025-11-27 12:38:25'),
(12, 1, 5, '2025-11-27 12:38:25');

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
(26, 5, 13, 'favorite', '2025-11-15 20:06:00'),
(27, 4, 12, 'watchlist', '2025-11-19 14:53:31'),
(28, 4, 4, 'watchlist', '2025-11-19 14:53:33'),
(29, 4, 8, 'watchlist', '2025-11-19 14:53:36'),
(30, 4, 13, 'watchlist', '2025-11-19 14:53:37'),
(31, 4, 9, 'watchlist', '2025-11-19 14:53:42'),
(32, 6, 8, 'favorite', '2025-11-19 15:53:36'),
(33, 6, 12, 'watchlist', '2025-11-19 15:53:36'),
(34, 5, 22, 'watchlist', '2025-11-23 17:15:49'),
(35, 5, 22, 'favorite', '2025-11-23 17:15:49'),
(120, 4, 27, 'watchlist', '2025-11-29 17:51:42'),
(122, 4, 28, 'watchlist', '2025-11-29 17:51:45'),
(131, 4, 26, 'watchlist', '2025-11-29 17:52:05'),
(138, 5, 28, 'watchlist', '2025-11-30 13:18:34'),
(139, 5, 27, 'watchlist', '2025-11-30 13:19:25'),
(141, 5, 26, 'watchlist', '2025-11-30 13:20:00'),
(143, 5, 17, 'favorite', '2025-12-14 17:29:37'),
(144, 5, 17, 'watchlist', '2025-12-14 17:29:37');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

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
-- Indeksy dla tabeli `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD PRIMARY KEY (`movie_id`,`genre_id`),
  ADD KEY `fk_mg_genre` (`genre_id`);

--
-- Indeksy dla tabeli `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_movie_rating` (`user_id`,`movie_id`),
  ADD KEY `movie_id` (`movie_id`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_achievement_unique` (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

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
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `directors`
--
ALTER TABLE `directors`
  MODIFY `director_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1253;

--
-- AUTO_INCREMENT for table `review_likes`
--
ALTER TABLE `review_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_movie_lists`
--
ALTER TABLE `user_movie_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_md_director` FOREIGN KEY (`director_id`) REFERENCES `directors` (`director_id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD CONSTRAINT `fk_mg_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD CONSTRAINT `review_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_likes_ibfk_2` FOREIGN KEY (`rating_id`) REFERENCES `ratings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_movie_lists`
--
ALTER TABLE `user_movie_lists`
  ADD CONSTRAINT `user_movie_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
