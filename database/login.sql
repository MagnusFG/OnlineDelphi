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
-- Datenbank: `login`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbllogin`
--

CREATE TABLE `tbllogin` (
  `Login_ID` int(11) NOT NULL,
  `LoginUsername_txt` text NOT NULL,
  `LoginPassword_txt` text NOT NULL,
  `LoginDatum_dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LoginAdmin_bln` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tbllogin`
--

INSERT INTO `tbllogin` (`Login_ID`, `LoginUsername_txt`, `LoginPassword_txt`, `LoginDatum_dat`, `LoginAdmin_bln`) VALUES
(1, 'MARTIN', 'anv234k', '2016-05-24 13:35:55', 0),
(2, 'DANIEL', 'kml789s', '2016-05-24 13:46:37', 0),
(3, 'ALEXANDER', 'ajds83s', '2016-05-24 13:48:17', 0),
(4, 'FERNANDO', 'cow93ik', '2016-05-24 13:48:34', 0),
(5, 'SEBASTIAN', 'xowu923l', '2016-05-24 13:49:15', 0),
(6, 'FELIX', '3246ss', '2016-05-24 14:07:06', 0),
(7, 'ADMIN', '12345', '2016-05-24 08:52:51', 1),
(8, 'TEST', '1q2w3e4r', '2016-05-25 09:13:19', 0),
(9, 'PRAESI', '098765', '2016-05-25 09:27:10', 0),
(10, 'QWER', 'qwer', '2016-05-30 07:21:41', 0),
(11, 'NEU', '$2y$10$mQPPCuyIuMggjUgfGZbPUuHf/TyjAf6uNhZTKzpS3iTaobyz4s1TG', '2016-09-09 07:50:29', 0),
(12, 'FELIXTEST', 'magnus', '2017-03-23 09:20:10', 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `tbllogin`
--
ALTER TABLE `tbllogin`
  ADD PRIMARY KEY (`Login_ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `tbllogin`
--
ALTER TABLE `tbllogin`
  MODIFY `Login_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
