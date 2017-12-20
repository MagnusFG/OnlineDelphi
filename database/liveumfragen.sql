-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 23. Mrz 2017 um 11:17
-- Server-Version: 10.1.9-MariaDB
-- PHP-Version: 5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `liveumfragen`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblantworten`
--

CREATE TABLE `tblantworten` (
  `Antworten_ID` int(11) NOT NULL,
  `AntwortenText_txt` text NOT NULL,
  `AntwortenDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tblantworten`
--

INSERT INTO `tblantworten` (`Antworten_ID`, `AntwortenText_txt`, `AntwortenDatum_dat`) VALUES
(1, 'Berlin', '2016-05-24 13:00:30'),
(2, 'Leipzig', '2016-03-29 16:07:56'),
(3, 'M&uumlnchen', '2016-03-29 16:09:03'),
(4, '15-19', '2016-03-29 16:09:03'),
(5, '20-29', '2016-03-29 16:09:55'),
(6, '30-39', '2016-03-29 16:10:04'),
(7, '40-49', '2016-03-29 16:10:32'),
(8, '50-59', '2016-03-29 16:10:32'),
(9, 'sehr schlecht', '2016-03-29 16:44:38'),
(10, 'schlecht', '2016-03-29 16:45:30'),
(11, 'mittel', '2016-03-29 16:44:48'),
(12, 'gut', '2016-03-29 16:44:48'),
(13, 'sehr gut', '2016-03-29 16:44:55'),
(14, '0', '2016-03-30 06:36:23'),
(15, '7', '2016-03-30 06:36:23'),
(16, '1', '2016-03-30 06:37:00'),
(17, 'Tage', '2016-03-30 06:37:00'),
(18, 'VW', '2016-03-30 06:52:08'),
(19, 'BMW', '2016-03-30 06:52:08'),
(20, 'Fiat', '2016-03-30 06:52:20'),
(21, 'Ferrari', '2016-03-30 06:52:20'),
(22, 'Opel', '2016-03-30 06:52:27'),
(23, 'Italien', '2016-03-30 07:04:12'),
(24, 'Frankreich', '2016-03-30 07:04:12'),
(25, 'Spanien', '2016-03-30 07:04:24'),
(26, 'Schweden', '2016-03-30 07:04:24'),
(27, 'Griechenland', '2016-03-30 07:04:33'),
(28, 'Schnitzel mit Pommes', '2016-03-30 07:18:51'),
(29, 'Salat', '2016-03-30 07:18:51'),
(30, 'Pizza', '2016-03-30 07:19:05'),
(31, 'Pasta', '2016-03-30 07:19:05'),
(32, 'Golf', '2016-03-30 07:28:46'),
(33, 'Rugby', '2016-03-30 07:28:46'),
(34, 'Leichtathletik', '2016-03-30 07:29:01'),
(35, 'Tennis', '2016-03-30 07:29:01'),
(36, 'Fussball', '2016-03-30 07:29:10'),
(37, 'Stuttgart', '2016-05-24 12:41:23'),
(38, 'K&oumlln', '2016-05-24 12:59:50'),
(39, '0', '2016-05-24 13:09:32'),
(40, '3', '2016-05-24 13:07:33'),
(41, '0.1', '2016-05-24 13:07:41'),
(42, 'Grad des Bedauerns', '2016-05-24 13:08:04');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblantwortensel`
--

CREATE TABLE `tblantwortensel` (
  `AntwortenSel_ID` int(11) NOT NULL,
  `AntwortenSelFragenSel_fkey` int(11) NOT NULL,
  `AntwortenSelAntworten_fkey` int(11) NOT NULL,
  `AntwortenSelAktiv_bln` int(11) NOT NULL,
  `AntwortenSelDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tblantwortensel`
--

INSERT INTO `tblantwortensel` (`AntwortenSel_ID`, `AntwortenSelFragenSel_fkey`, `AntwortenSelAntworten_fkey`, `AntwortenSelAktiv_bln`, `AntwortenSelDatum_dat`) VALUES
(1, 1, 1, 1, '2016-03-29 16:33:17'),
(2, 1, 2, 1, '2016-03-30 19:25:05'),
(3, 1, 3, 1, '2016-03-29 16:33:20'),
(4, 1, 37, 1, '2016-05-24 12:59:22'),
(5, 1, 38, 1, '2016-05-24 12:59:03'),
(6, 2, 39, 1, '2016-05-24 13:03:17'),
(7, 2, 40, 1, '2016-05-24 13:04:00'),
(8, 2, 41, 1, '2016-05-24 13:04:03'),
(9, 2, 42, 1, '2016-05-24 13:04:06');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbldata`
--

CREATE TABLE `tbldata` (
  `Data_ID` int(11) NOT NULL,
  `DataFragenSel_fkey` int(11) NOT NULL,
  `DataAntwortenSel_fkey` int(11) DEFAULT NULL,
  `DataWert_dbl` double DEFAULT NULL,
  `DataWert_txt` text,
  `DataUser_fkey` int(11) NOT NULL,
  `DataDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tbldata`
--

INSERT INTO `tbldata` (`Data_ID`, `DataFragenSel_fkey`, `DataAntwortenSel_fkey`, `DataWert_dbl`, `DataWert_txt`, `DataUser_fkey`, `DataDatum_dat`) VALUES
(1, 1, 1, 0, '', 8, '2016-05-24 13:39:51'),
(2, 1, 2, 1, '', 8, '2016-05-24 13:39:51'),
(3, 1, 3, 0, '', 8, '2016-05-24 13:39:51'),
(4, 1, 4, 0, '', 8, '2016-05-24 13:39:51'),
(5, 1, 5, 0, '', 8, '2016-05-24 13:39:51'),
(6, 1, 1, 0, '', 9, '2016-05-24 13:41:55'),
(7, 1, 2, 1, '', 9, '2016-05-24 13:41:55'),
(8, 1, 3, 1, '', 9, '2016-05-24 13:41:55'),
(9, 1, 4, 0, '', 9, '2016-05-24 13:41:55'),
(10, 1, 5, 0, '', 9, '2016-05-24 13:41:55'),
(11, 2, 6, 0, '', 1, '2016-05-24 13:43:25'),
(12, 1, 1, 0, '', 1, '2016-05-24 13:46:12'),
(13, 1, 2, 0, '', 1, '2016-05-24 13:46:12'),
(14, 1, 3, 0, '', 1, '2016-05-24 13:46:12'),
(15, 1, 4, 0, '', 1, '2016-05-24 13:46:12'),
(16, 1, 5, 0, '', 1, '2016-05-24 13:46:12'),
(17, 1, 1, 0, '', 2, '2016-05-24 13:49:29'),
(18, 1, 2, 0, '', 2, '2016-05-24 13:49:46'),
(19, 1, 3, 0, '', 2, '2016-05-24 13:49:29'),
(20, 1, 4, 0, '', 2, '2016-05-24 13:49:29'),
(21, 1, 5, 0, '', 2, '2016-05-24 13:49:29'),
(22, 2, 6, 0.3, '', 2, '2016-05-24 13:50:16'),
(23, 1, 1, 1, '', 10, '2016-05-24 13:51:48'),
(24, 1, 2, 0, '', 10, '2016-05-24 13:51:48'),
(25, 1, 3, 0, '', 10, '2016-05-24 13:51:48'),
(26, 1, 4, 0, '', 10, '2016-05-24 13:51:48'),
(27, 1, 5, 0, '', 10, '2016-05-24 13:51:48'),
(28, 2, 6, 3, '', 11, '2016-05-24 13:52:33'),
(29, 1, 1, 0, '', 12, '2016-05-24 13:53:09'),
(30, 1, 2, 0, '', 12, '2016-05-24 13:53:09'),
(31, 1, 3, 0, '', 12, '2016-05-24 13:53:14'),
(32, 1, 4, 0, '', 12, '2016-05-24 13:53:09'),
(33, 1, 5, 0, '', 12, '2016-05-24 13:53:09'),
(34, 2, 6, 0, '', 13, '2016-05-24 13:53:51'),
(35, 1, 1, 1, '', 14, '2016-05-24 14:07:35'),
(36, 1, 2, 1, '', 14, '2016-05-24 14:07:35'),
(37, 1, 3, 0, '', 14, '2016-05-24 14:07:35'),
(38, 1, 4, 1, '', 14, '2016-05-24 14:07:35'),
(39, 1, 5, 0, '', 14, '2016-05-24 14:07:35'),
(40, 1, 1, 0, '', 15, '2016-05-24 14:07:46'),
(41, 1, 2, 0, '', 15, '2016-05-24 14:07:46'),
(42, 1, 3, 0, '', 15, '2016-05-24 14:07:46'),
(43, 1, 4, 0, '', 15, '2016-05-24 14:07:46'),
(44, 1, 5, 0, '', 15, '2016-05-24 14:07:46'),
(45, 2, 6, 0.8, '', 16, '2016-05-24 14:10:04'),
(46, 1, 1, 1, '', 17, '2016-05-24 14:31:22'),
(47, 1, 2, 0, '', 17, '2016-05-24 14:31:22'),
(48, 1, 3, 1, '', 17, '2016-05-24 14:31:22'),
(49, 1, 4, 0, '', 17, '2016-05-24 14:31:22'),
(50, 1, 5, 0, '', 17, '2016-05-24 14:31:22'),
(51, 2, 6, 0, '', 4, '2016-05-24 14:31:54'),
(52, 2, 6, 0, '', 5, '2016-05-24 14:35:42'),
(53, 1, 1, 1, '', 21, '2016-05-25 09:14:17'),
(54, 1, 2, 1, '', 21, '2016-05-25 09:14:17'),
(55, 1, 3, 0, '', 21, '2016-05-25 09:14:17'),
(56, 1, 4, 0, '', 21, '2016-05-25 09:14:17'),
(57, 1, 5, 0, '', 21, '2016-05-25 09:14:17'),
(58, 1, 1, 0, '', 23, '2016-05-25 11:10:07'),
(59, 1, 2, 1, '', 23, '2016-05-25 11:10:07'),
(60, 1, 3, 1, '', 23, '2016-05-25 11:10:07'),
(61, 1, 4, 0, '', 23, '2016-05-25 11:10:08'),
(62, 1, 5, 0, '', 23, '2016-05-25 11:10:08'),
(63, 2, 6, 1.5, '', 24, '2016-05-25 11:11:07'),
(64, 1, 1, 0, '', 24, '2016-05-27 15:53:31'),
(65, 1, 2, 0, '', 24, '2016-05-27 15:53:31'),
(66, 1, 3, 0, '', 24, '2016-05-27 15:53:31'),
(67, 1, 4, 0, '', 24, '2016-05-27 15:53:31'),
(68, 1, 5, 0, '', 24, '2016-05-27 15:53:31'),
(69, 1, 1, 0, '', 25, '2016-08-11 07:14:07'),
(70, 1, 2, 1, '', 25, '2016-08-11 07:14:07'),
(71, 1, 3, 0, '', 25, '2016-08-11 07:14:07'),
(72, 1, 4, 0, '', 25, '2016-08-11 07:14:07'),
(73, 1, 5, 0, '', 25, '2016-08-11 07:14:07'),
(74, 1, 1, 0, '', 26, '2016-12-14 13:28:29'),
(75, 1, 2, 0, '', 26, '2016-12-14 13:28:29'),
(76, 1, 3, 0, '', 26, '2016-12-14 13:28:29'),
(77, 1, 4, 0, '', 26, '2016-12-14 13:28:29'),
(78, 1, 5, 0, '', 26, '2016-12-14 13:28:29'),
(80, 1, 1, 0, '', 30, '2017-02-16 13:40:25'),
(81, 1, 2, 0, '', 30, '2017-02-16 13:40:25'),
(82, 1, 3, 0, '', 30, '2017-02-16 13:40:25'),
(83, 1, 4, 0, '', 30, '2017-02-16 13:40:25'),
(84, 1, 5, 0, '', 30, '2017-02-16 13:40:25'),
(87, 1, 1, 1, '', 22, '2017-02-16 14:43:36'),
(88, 1, 2, 0, '', 22, '2017-02-16 14:03:40'),
(89, 1, 3, 0, '', 22, '2017-02-16 14:09:33'),
(90, 1, 4, 0, '', 22, '2017-02-16 14:44:12'),
(91, 1, 5, 0, '', 22, '2017-02-16 13:42:33'),
(117, 1, 3, 0, NULL, 22, '2017-02-16 14:20:05'),
(118, 1, NULL, NULL, 'ddd', 22, '2017-02-16 14:23:19'),
(119, 1, NULL, NULL, 'hallo', 22, '2017-02-16 14:43:36'),
(120, 1, NULL, NULL, 'test', 22, '2017-02-16 14:44:12'),
(121, 3, NULL, NULL, 'yc', 5, '2017-02-16 15:14:30'),
(122, 1, 1, 0, NULL, 5, '2017-02-16 15:14:30'),
(123, 1, 2, 0, NULL, 5, '2017-02-16 15:14:30'),
(124, 1, 3, 0, NULL, 5, '2017-02-16 15:14:30'),
(125, 1, 4, 0, NULL, 5, '2017-02-16 15:14:30'),
(126, 1, 5, 0, NULL, 5, '2017-02-16 15:14:30');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblfragen`
--

CREATE TABLE `tblfragen` (
  `Fragen_ID` int(11) NOT NULL,
  `FragenText_txt` text NOT NULL,
  `FragenDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tblfragen`
--

INSERT INTO `tblfragen` (`Fragen_ID`, `FragenText_txt`, `FragenDatum_dat`) VALUES
(1, 'Wo wohnst du?', '2016-03-26 13:07:09'),
(2, 'Wie alt bist du?', '2016-03-26 13:03:19'),
(3, 'Wie bewertest du das Wetter heute?', '2016-03-26 13:07:41'),
(4, 'Wie geht es dir heute?', '2016-03-26 13:07:41'),
(5, 'Wie oft in der Woche machst du Sport?', '2016-03-26 13:09:35'),
(6, 'In welchem Land warst du schon mal im Urlaub?', '2016-03-26 13:09:35'),
(7, 'Welche Automarke magst du?', '2016-03-30 06:51:13'),
(8, 'Sortieren Sie ihre Lieblingsmahlzeit nach Relevanz.', '2016-03-30 07:17:02'),
(9, 'Wie interresant finden sie die gelisteten Sportarten? \r\nVerteilen Sie 100 Punkte auf die gelisteten m&oumlglichen Antworten.', '2016-03-30 07:25:02'),
(10, 'Welches w&aumlre deine bevorzugte Stadt nach Dresden?', '2016-05-24 13:00:06'),
(11, 'Wie sehr bedauerst du, dass der 1. FC N&uumlrnberg zweitklassig bleibt?', '2016-05-24 13:09:04');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblfragensel`
--

CREATE TABLE `tblfragensel` (
  `FragenSel_ID` int(11) NOT NULL,
  `FragenSelUmfragen_fkey` int(11) NOT NULL,
  `FragenSelFragen_fkey` int(11) NOT NULL,
  `FragenSelAktiv_bln` tinyint(1) NOT NULL,
  `FragenSelChart_bln` tinyint(1) NOT NULL,
  `FragenSelBlock_int` int(11) NOT NULL,
  `FragenSelTyp_int` int(11) NOT NULL,
  `FragenSelObligatorisch_bln` tinyint(1) NOT NULL,
  `FragenSelDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tblfragensel`
--

INSERT INTO `tblfragensel` (`FragenSel_ID`, `FragenSelUmfragen_fkey`, `FragenSelFragen_fkey`, `FragenSelAktiv_bln`, `FragenSelChart_bln`, `FragenSelBlock_int`, `FragenSelTyp_int`, `FragenSelObligatorisch_bln`, `FragenSelDatum_dat`) VALUES
(1, 1, 10, 1, 1, 1, 5, 0, '2016-05-24 13:13:35'),
(2, 2, 11, 1, 1, 1, 4, 0, '2016-05-24 13:13:39'),
(3, 1, 7, 1, 0, 1, 9, 1, '2017-02-16 14:07:02');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblumfragen`
--

CREATE TABLE `tblumfragen` (
  `Umfragen_ID` int(11) NOT NULL,
  `UmfragenText_txt` text NOT NULL,
  `UmfragenOffen_bln` tinyint(1) NOT NULL,
  `UmfragenAktiv_bln` tinyint(1) NOT NULL,
  `UmfragenChart_bln` tinyint(1) NOT NULL,
  `UmfragenDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tblumfragen`
--

INSERT INTO `tblumfragen` (`Umfragen_ID`, `UmfragenText_txt`, `UmfragenOffen_bln`, `UmfragenAktiv_bln`, `UmfragenChart_bln`, `UmfragenDatum_dat`) VALUES
(1, 'Wohnort (offene Umfrage)', 1, 1, 1, '2017-02-16 15:14:33'),
(2, '1. FC N&uumlrnberg (geschlossene Umfrage)', 0, 1, 1, '2016-05-25 11:11:50');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbluser`
--

CREATE TABLE `tbluser` (
  `User_ID` int(11) NOT NULL,
  `UserLogin_int` int(11) NOT NULL,
  `UserIP_txt` text NOT NULL,
  `UserDate_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tbluser`
--

INSERT INTO `tbluser` (`User_ID`, `UserLogin_int`, `UserIP_txt`, `UserDate_dat`) VALUES
(1, 1, '::1', '2016-04-18 20:20:55'),
(2, 2, '::1', '2016-04-19 09:45:39'),
(3, 0, '::1', '2016-04-19 10:20:47'),
(4, 3, '::1', '2016-05-11 14:55:45'),
(5, 7, '::1', '2016-05-24 12:37:45'),
(6, 0, '::1', '2016-05-24 13:17:48'),
(7, 0, '::1', '2016-05-24 13:20:12'),
(8, 0, '::1', '2016-05-24 13:39:48'),
(9, 0, '141.76.24.105', '2016-05-24 13:41:46'),
(10, 0, '141.76.24.101', '2016-05-24 13:51:29'),
(11, 5, '141.76.24.101', '2016-05-24 13:52:26'),
(12, 0, '141.76.24.106', '2016-05-24 13:52:50'),
(13, 4, '141.76.24.106', '2016-05-24 13:53:31'),
(14, 0, '::1', '2016-05-24 14:07:27'),
(15, 0, '::1', '2016-05-24 14:07:40'),
(16, 6, '::1', '2016-05-24 14:10:00'),
(17, 0, '141.76.24.110', '2016-05-24 14:30:52'),
(18, 0, '::1', '2016-05-24 14:43:47'),
(19, 0, '::1', '2016-05-24 15:02:45'),
(20, 0, '::1', '2016-05-25 09:12:24'),
(21, 0, '::1', '2016-05-25 09:14:04'),
(22, 8, '::1', '2016-05-25 09:17:42'),
(23, 0, '::1', '2016-05-25 11:09:50'),
(24, 9, '::1', '2016-05-25 11:10:40'),
(25, 0, '::1', '2016-08-11 07:14:02'),
(26, 0, '::1', '2016-12-14 13:28:26'),
(27, 0, '::1', '2016-12-14 13:29:27'),
(28, 0, '::1', '2017-02-16 13:33:09'),
(29, 0, '::1', '2017-02-16 13:35:32'),
(30, 0, '::1', '2017-02-16 13:40:15');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `tblantworten`
--
ALTER TABLE `tblantworten`
  ADD PRIMARY KEY (`Antworten_ID`);

--
-- Indizes für die Tabelle `tblantwortensel`
--
ALTER TABLE `tblantwortensel`
  ADD PRIMARY KEY (`AntwortenSel_ID`),
  ADD KEY `AntwortenSelFragen_fkey` (`AntwortenSelFragenSel_fkey`,`AntwortenSelAntworten_fkey`),
  ADD KEY `AntwortenSelAntworten_fkey` (`AntwortenSelAntworten_fkey`);

--
-- Indizes für die Tabelle `tbldata`
--
ALTER TABLE `tbldata`
  ADD PRIMARY KEY (`Data_ID`),
  ADD KEY `DataFragen_fkey` (`DataAntwortenSel_fkey`),
  ADD KEY `DataFragenSel_fkey` (`DataFragenSel_fkey`),
  ADD KEY `DataUser_fkey` (`DataUser_fkey`);

--
-- Indizes für die Tabelle `tblfragen`
--
ALTER TABLE `tblfragen`
  ADD PRIMARY KEY (`Fragen_ID`),
  ADD KEY `Fragen_ID` (`Fragen_ID`);

--
-- Indizes für die Tabelle `tblfragensel`
--
ALTER TABLE `tblfragensel`
  ADD PRIMARY KEY (`FragenSel_ID`),
  ADD KEY `FragenSelUmfragen_fkey` (`FragenSelUmfragen_fkey`,`FragenSelFragen_fkey`),
  ADD KEY `FragenSelFragen_fkey` (`FragenSelFragen_fkey`);

--
-- Indizes für die Tabelle `tblumfragen`
--
ALTER TABLE `tblumfragen`
  ADD PRIMARY KEY (`Umfragen_ID`),
  ADD KEY `Umfragen_ID` (`Umfragen_ID`),
  ADD KEY `Umfragen_ID_2` (`Umfragen_ID`),
  ADD KEY `Umfragen_ID_3` (`Umfragen_ID`);

--
-- Indizes für die Tabelle `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `tblantworten`
--
ALTER TABLE `tblantworten`
  MODIFY `Antworten_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
--
-- AUTO_INCREMENT für Tabelle `tblantwortensel`
--
ALTER TABLE `tblantwortensel`
  MODIFY `AntwortenSel_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT für Tabelle `tbldata`
--
ALTER TABLE `tbldata`
  MODIFY `Data_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;
--
-- AUTO_INCREMENT für Tabelle `tblfragen`
--
ALTER TABLE `tblfragen`
  MODIFY `Fragen_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT für Tabelle `tblfragensel`
--
ALTER TABLE `tblfragensel`
  MODIFY `FragenSel_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `tblumfragen`
--
ALTER TABLE `tblumfragen`
  MODIFY `Umfragen_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `tblantwortensel`
--
ALTER TABLE `tblantwortensel`
  ADD CONSTRAINT `tblantwortensel_ibfk_1` FOREIGN KEY (`AntwortenSelAntworten_fkey`) REFERENCES `tblantworten` (`Antworten_ID`),
  ADD CONSTRAINT `tblantwortensel_ibfk_2` FOREIGN KEY (`AntwortenSelFragenSel_fkey`) REFERENCES `tblfragensel` (`FragenSel_ID`);

--
-- Constraints der Tabelle `tbldata`
--
ALTER TABLE `tbldata`
  ADD CONSTRAINT `tbldata_ibfk_1` FOREIGN KEY (`DataAntwortenSel_fkey`) REFERENCES `tblantwortensel` (`AntwortenSel_ID`),
  ADD CONSTRAINT `tbldata_ibfk_2` FOREIGN KEY (`DataFragenSel_fkey`) REFERENCES `tblfragensel` (`FragenSel_ID`),
  ADD CONSTRAINT `tbldata_ibfk_3` FOREIGN KEY (`DataUser_fkey`) REFERENCES `tbluser` (`User_ID`);

--
-- Constraints der Tabelle `tblfragensel`
--
ALTER TABLE `tblfragensel`
  ADD CONSTRAINT `tblfragensel_ibfk_1` FOREIGN KEY (`FragenSelUmfragen_fkey`) REFERENCES `tblumfragen` (`Umfragen_ID`),
  ADD CONSTRAINT `tblfragensel_ibfk_2` FOREIGN KEY (`FragenSelFragen_fkey`) REFERENCES `tblfragen` (`Fragen_ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
