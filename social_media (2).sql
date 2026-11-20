-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 11:37 AM
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
-- Database: `social_media`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_details`
--

CREATE TABLE `admin_details` (
  `id` int(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `picturepr` varchar(255) NOT NULL,
  `coverpic` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_details`
--

INSERT INTO `admin_details` (`id`, `first_name`, `last_name`, `email`, `password`, `picturepr`, `coverpic`) VALUES
(1, 'Badmus', 'Bolaji', 'badmusbolaji71@gmail.com', '1', '', ''),
(2, 'Badmus', 'Shakirullahi', 'badmusshakirullah@gmail.com', 'bolaji', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` int(255) NOT NULL,
  `post_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `date` datetime(1) NOT NULL DEFAULT current_timestamp(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follow`
--

CREATE TABLE `follow` (
  `id` int(11) NOT NULL,
  `follower_id` varchar(100) DEFAULT NULL,
  `following_id` varchar(100) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(244) NOT NULL DEFAULT 'following'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follow`
--

INSERT INTO `follow` (`id`, `follower_id`, `following_id`, `date`, `status`) VALUES
(86, '16', '14', '2026-11-03 11:22:36', 'following'),
(89, '16', '19', '2026-11-03 11:24:47', 'following'),
(94, '14', '16', '2026-11-03 11:43:23', 'following'),
(97, '14', '13', '2026-11-03 11:50:56', 'following'),
(98, '16', '18', '2026-11-03 12:04:58', 'following'),
(100, '19', '20', '2026-11-03 14:08:31', 'following'),
(115, '17', '25', '2026-11-04 09:59:51', 'following'),
(117, '17', '24', '2026-11-04 10:12:55', 'following'),
(118, '17', '26', '2026-11-04 10:13:06', 'following'),
(127, '27', '23', '2026-11-04 11:58:43', 'following'),
(128, '27', '19', '2026-11-04 12:06:46', 'following'),
(129, '27', '17', '2026-11-04 12:07:59', 'following'),
(130, '27', '16', '2026-11-04 12:09:31', 'following'),
(131, '27', '13', '2026-11-04 12:10:59', 'following'),
(132, '13', '23', '2026-11-04 12:12:09', 'following'),
(135, '16', '23', '2026-11-04 12:14:33', 'following'),
(136, '17', '23', '2026-11-04 12:14:58', 'following'),
(137, '18', '23', '2026-11-04 12:15:44', 'following'),
(138, '19', '23', '2026-11-04 12:16:21', 'following'),
(139, '20', '23', '2026-11-04 12:17:10', 'following'),
(140, '21', '23', '2026-11-04 12:17:37', 'following'),
(142, '23', '18', '2026-11-04 12:22:20', 'following'),
(143, '22', '23', '2026-11-04 12:31:21', 'following'),
(148, '23', '16', '2026-11-05 09:42:41', 'following'),
(149, '16', '20', '2026-11-05 10:53:04', 'following'),
(151, '20', '16', '2026-11-05 11:01:29', 'following'),
(152, '20', '19', '2026-11-05 11:04:28', 'following'),
(153, '20', '22', '2026-11-05 11:32:22', 'following'),
(154, '20', '18', '2026-11-05 11:36:28', 'following'),
(155, '20', '14', '2026-11-05 12:49:38', 'following'),
(156, '25', '15', '2026-11-05 13:20:17', 'following'),
(157, '15', '21', '2026-11-05 13:37:49', 'following'),
(158, '15', '14', '2026-11-05 13:47:33', 'following'),
(160, '22', '15', '2026-11-05 13:56:29', 'following'),
(162, '15', '17', '2026-11-06 11:10:41', 'following'),
(163, '15', '22', '2026-11-06 11:10:59', 'following'),
(166, '15', '23', '2026-11-06 11:18:32', 'following'),
(171, '16', '17', '2026-11-06 14:49:55', 'following'),
(175, '28', '23', '2026-11-08 19:21:07', 'following'),
(176, '20', '13', '2026-11-09 14:15:07', 'following'),
(177, '14', '17', '2026-11-10 09:24:19', 'following'),
(178, '14', '23', '2026-11-10 09:26:54', 'following');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `post_id` varchar(255) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `time`) VALUES
(126, '15', '130', '2026-11-06 11:47:15'),
(127, '23', '130', '2026-11-06 11:48:13'),
(129, '23', '116', '2026-11-06 12:11:30'),
(130, '23', '127', '2026-11-06 14:33:33'),
(131, '16', '121', '2026-11-06 14:51:27'),
(132, '16', '112', '2026-11-06 15:26:55'),
(135, '20', '111', '2026-11-09 14:11:23'),
(136, '20', '130', '2026-11-09 14:50:47'),
(153, '14', '123', '2026-11-10 10:31:30');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(255) NOT NULL,
  `sender_id` varchar(255) NOT NULL,
  `receiver_id` varchar(676) NOT NULL,
  `message` mediumtext NOT NULL,
  `sent_at` time NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `sent_at`) VALUES
(1, '23', '16', 'Hello I need chicken and ', '11:10:03'),
(2, '16', '23', 'Yes it is available', '11:16:00'),
(16, '16', '20', 'How many do you need', '11:55:49'),
(17, '16', '20', 'How many do you need', '11:55:58'),
(18, '16', '20', 'hello', '11:58:08'),
(19, '23', '20', 'Haffa bro', '11:59:23'),
(20, '23', '20', 'Haffa bro', '11:59:26'),
(21, '20', '23', 'active', '12:00:24'),
(22, '20', '23', 'ejeh', '12:00:38'),
(23, '20', '16', 'hi', '12:02:32'),
(24, '20', '16', 'i need 2 chicken and one chips', '12:02:49'),
(25, '20', '19', 'I need wears', '12:04:53'),
(26, '20', '23', 'oga mhi', '12:28:10'),
(27, '20', '22', 'Hi PEAK ?', '12:33:23'),
(28, '23', '20', 'How have you been ?', '12:35:57'),
(29, '20', '18', 'Hi', '12:36:34'),
(30, '20', '23', 'ttt', '12:37:40'),
(31, '20', '23', 'fghjuk', '12:37:51'),
(32, '20', '23', 'reply boss', '13:44:22'),
(33, '20', '14', 'gooood day my boss', '13:49:57'),
(34, '20', '14', 'helo', '13:59:55'),
(35, '25', '15', 'hay u mad', '14:20:32'),
(36, '15', '25', 'I\'m not please', '14:34:09'),
(37, '15', '21', 'i need shoes please', '14:38:09'),
(38, '15', '25', 'hui', '14:47:13'),
(39, '15', '14', 'hi foodie', '14:47:42'),
(40, '22', '15', 'Hi', '14:56:47'),
(41, '15', '22', 'how was your night milk', '14:57:06'),
(42, '22', '15', 'it was fine durg', '14:57:23'),
(43, '15', '22', 'good i need milk please', '14:57:43'),
(44, '22', '15', 'like how many pack', '14:57:56'),
(45, '15', '22', 'like 89pcs', '14:58:14'),
(46, '22', '15', 'ok nah just sicky', '14:58:26'),
(47, '22', '15', 'place order fro website', '14:58:38'),
(48, '22', '15', 'peak.ng', '14:58:46'),
(49, '15', '22', 'so how i go do am direct me please', '14:59:13'),
(50, '15', '22', 'direct me wella ogo', '15:00:13'),
(51, '22', '15', 'u no get sense?', '15:03:50'),
(52, '15', '22', 'i get sense guy', '15:04:17'),
(53, '22', '15', 'wetyn you dey sell sef or wetyn you wan buy ?', '15:04:58'),
(54, '15', '22', 'i wan buy 56 pcs of peak milk ,', '15:05:29'),
(55, '15', '22', 'and 78 pcs of hollandia youghurt', '15:06:22'),
(56, '15', '22', 'available??', '15:06:32'),
(57, '22', '15', 'e dey available my bro buh shey you guide ??', '15:06:55'),
(58, '22', '15', 'and how you wan buy am ?', '15:09:13'),
(59, '15', '22', 'crypto cureency boss???', '15:09:39'),
(60, '22', '15', 'you wan reap me ??', '15:10:05'),
(61, '15', '22', 'yes of course fool', '15:10:32'),
(62, '22', '15', 'Are you normal at all ?', '15:10:49'),
(63, '15', '22', 'i am actually', '15:11:07'),
(64, '22', '15', 'im not sure bro', '15:11:22'),
(65, '15', '22', 'maybe coz you\'re not', '15:11:50'),
(66, '22', '15', 'pleaseeeeee', '15:12:00'),
(67, '15', '22', 'don\'t you know customer are always right ?', '15:12:33'),
(68, '22', '15', 'really ?? you need to tell me nah fool', '15:12:50'),
(69, '15', '22', 'Are you ready to sell ??', '15:13:13'),
(70, '22', '15', 'are you ready to buy as well ?? coz im always ready', '15:13:40'),
(71, '15', '22', 'you maynot be officially', '15:14:20'),
(72, '15', '22', 'coz only god knows', '15:17:32'),
(73, '22', '15', 'really ?? did you even go to school?', '15:18:06'),
(74, '15', '22', 'yes of course', '15:19:01'),
(75, '15', '22', 'why asking ??', '15:19:10'),
(76, '22', '15', 'you seems dull ?', '15:19:28'),
(77, '22', '15', 'Hi , how\'re you doing ?', '15:19:46'),
(78, '15', '22', 'i\'m doing great', '15:20:05'),
(79, '15', '22', 'please introduce yourself .......', '15:20:25'),
(80, '22', '15', 'My name is Azeez what about you ?', '15:20:50'),
(81, '15', '22', 'Call me Basit nice meeting you .......', '15:21:45'),
(82, '15', '22', 'HI', '21:59:04'),
(83, '23', '20', 'hi', '15:37:00'),
(84, '23', '16', 'hi', '15:38:16'),
(85, '16', '23', 'hello', '15:42:38'),
(86, '23', '16', 'how\'re you doing', '15:44:08'),
(87, '23', '16', 'hi', '15:46:49'),
(88, '16', '23', 'hello', '15:47:00'),
(89, '23', '16', 'chickeeeeennnnnn', '15:47:13'),
(90, '16', '23', 'yes please', '15:47:26'),
(91, '23', '16', 'Sure pass me 10', '15:47:57'),
(92, '16', '17', 'eJEH', '15:50:26'),
(93, '16', '23', 'really????????????', '16:34:13'),
(94, '23', '16', 'yes i have the fund', '19:00:24'),
(95, '23', '16', 'yes i have the fund', '19:00:26'),
(96, '28', '23', 'Hi vlast ?', '20:21:22'),
(97, '28', '23', 'how\'re you doing I need blue thick how may I get one', '20:22:01'),
(98, '20', '13', 'hi', '15:25:55'),
(99, '20', '23', 'good day', '15:48:54'),
(100, '14', '23', 'Number one\\', '16:29:54'),
(101, '14', '15', 'wassup', '16:32:28'),
(102, '14', '17', 'hOW FAr', '10:24:29'),
(103, '14', '17', 'How far', '10:24:52');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(255) NOT NULL,
  `post_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `my_id` varchar(255) NOT NULL,
  `date` datetime(5) NOT NULL DEFAULT current_timestamp(5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `id` int(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `picturepr` varchar(244) NOT NULL,
  `coverpic` varchar(255) NOT NULL,
  `bio` varchar(544) NOT NULL,
  `followers` varchar(255) NOT NULL,
  `following` varchar(255) NOT NULL,
  `dates` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `age` int(255) NOT NULL,
  `status` varchar(244) NOT NULL DEFAULT 'Agreed terms and condition',
  `verified` int(50) NOT NULL DEFAULT 0,
  `memes_admin` int(50) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `first_name`, `last_name`, `email`, `password`, `picturepr`, `coverpic`, `bio`, `followers`, `following`, `dates`, `age`, `status`, `verified`, `memes_admin`) VALUES
(13, 'Azeez', 'Sherifat', 'sherifat@gmail.com', 'ayomide', 'IMG-20251129-WA0138.jpg', 'IMG-20260201-WA0184.jpg', 'without effort no success', '0', '0', '2026-10-26 12:49:17.358885', 0, 'Agreed terms and condition', 1, 0),
(14, 'Food Vendor', 'in abj', 'food@gmail.com', '11', 'IMG-20260415-WA0007.jpg', 'IMG-20260414-WA0155.jpg', 'Lifestyle', '0', '0', '2026-10-26 12:49:17.358885', 0, 'Agreed terms and condition', 0, 0),
(15, 'Adesina', 'Basit', 'basit@gmail.com', '123', 'IMG-20251204-WA0186.jpg', 'IMG-20260201-WA0182.jpg', 'Calmness doesn\'t mean being a novice , it\'s just a way to cover up and escape narrowly', '0', '0', '2026-10-26 12:49:17.358885', 0, 'Agreed terms and condition', 0, 0),
(16, 'Chicken', 'Taste', 'chickentatse_@gmail.com', '11', 'IMG-20260414-WA0162.jpg', 'IMG-20260414-WA0156.jpg', 'Your food is ready', '', '', '2026-10-26 12:49:17.358885', 0, 'Agreed terms and condition', 0, 0),
(17, 'Abdllah', 'Wajud', 'wa@gmail.com', '1', 'IMG-20251212-WA0092.jpg', 'IMG-20260201-WA0182.jpg', '', '', '', '2026-10-26 13:01:46.989119', 0, 'Agreed terms and condition', 0, 0),
(18, 'Sherifat', 'Sodik', 'badmusbolaji@gmail.com', '45', 'IMG-20251125-WA0006.jpg', 'IMG-20260415-WA0007.jpg', '', '', '', '2026-10-26 13:14:28.163949', 0, 'Agreed terms and condition', 0, 0),
(19, 'Brand', 'Wears', 'a@gmail.com', '1', '0b9861ab353206cd835e9aa50634f227.jpg', 'fb55739f4bdc5e0f70fa244858b642c9.jpg', 'We sell all kind of wears, patronize us today', '', '', '2026-10-26 14:33:15.237755', 0, 'Agreed terms and condition', 0, 0),
(20, 'Adeleke', 'Abdullah', 'abdullah@gmail.com', '12', 'Snapchat-1280883011.jpg', 'Screenshot_20260514-233809~3.png', '', '', '', '2026-10-26 15:23:51.394038', 0, 'Agreed terms and condition', 1, 0),
(21, 'Foot', 'Wears', 'd@gmail.com', '1', '2f8cfe1c511ff2c45b4bff1fccaffac4.jpg', 'f2e131b312cbd0e604ddb4abbb81490b.jpg', 'we sell foot wears', '', '', '2026-11-03 15:25:07.835677', 0, 'Agreed terms and condition', 0, 0),
(22, 'Azeez', 'Adekunle', 'peak@gmail.com', 'peak', 'IMG-20260201-WA0184.jpg', 'IMG-20260201-WA0182.jpg', 'Buy your milk!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\r\npeakmilk.com', '', '', '2026-11-03 15:43:30.593480', 0, 'Agreed terms and condition', 0, 0),
(23, 'VLAST', 'gen', 'vlast@gmail.com', 'vlast', 'file_0000000073588207b5bdd50f6ab48157.png', 'file_0000000073588207b5bdd50f6ab48157.png', 'Welcome to VLAST', '', '', '2026-11-03 15:54:52.500849', 0, 'Agreed terms and condition', 1, 0),
(24, 'Badmus', 'Bolaji', 'cvbm@gmail.com', '123', '', '', '', '', '', '2026-11-04 10:24:12.493597', 0, 'Agreed terms and condition', 0, 0),
(25, 'Badmus', 'Bolaji', 'badmusbolaji7@gmail.com', '7', 'IMG-20260320-WA0137.jpg', 'images (2).jfif', 'rttttttttttttttttttttttttttttttttttttttttt', '', '', '2026-11-04 10:26:12.028928', 0, 'Agreed terms and condition', 0, 0),
(26, 'Badmus', 'Bolaji', 'badmusbolaj@gmail.com', '5', '', '', '', '', '', '2026-11-04 10:28:09.435564', 0, 'Agreed terms and condition', 0, 0),
(27, 'sdhj', 'ghgg', 'ggg@gmail.com', '7', '', '', '', '', '', '2026-11-04 11:56:45.331217', 54, 'Agreed terms and condition', 0, 0),
(28, 'Adesina', 'Yusroh', 'yusrohade@gmail.com', 'yusroh', 'IMG-20251204-WA0186.jpg', 'IMG-20251204-WA0161.jpg', '', '', '', '2026-11-08 20:18:39.588866', 19, 'Agreed terms and condition', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_post`
--

CREATE TABLE `user_post` (
  `id` int(255) NOT NULL,
  `text` varchar(255) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `uploader_id` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `comment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_post`
--

INSERT INTO `user_post` (`id`, `text`, `picture`, `uploader_id`, `date`, `comment`) VALUES
(109, ' ', 'IMG-20251204-WA0161.jpg', '14', '2026-10-23 10:38:39', ''),
(110, ' ', 'f2e131b312cbd0e604ddb4abbb81490b.jpg', '15', '2026-10-23 10:49:28', ''),
(111, ' ', '0b9861ab353206cd835e9aa50634f227.jpg', '13', '2026-10-23 16:21:54', ''),
(112, ' ', '0b9861ab353206cd835e9aa50634f227.jpg', '13', '2026-10-23 16:22:16', ''),
(113, ' ', '0b9861ab353206cd835e9aa50634f227.jpg', '14', '2026-10-26 10:17:31', ''),
(114, 'S ', '', '19', '2026-10-26 15:18:49', ''),
(115, 'ASAAASSQWWEWEWRR ', '', '19', '2026-10-26 15:19:10', ''),
(116, 'Who build this app ? ', '', '19', '2026-10-26 15:20:35', ''),
(117, 'Hello guys ', '', '20', '2026-10-26 15:24:59', ''),
(118, 'topo ', '0b9861ab353206cd835e9aa50634f227.jpg', '20', '2026-10-26 15:33:50', ''),
(119, 'today ', '', '16', '2026-11-03 12:16:19', ''),
(120, 'Available ', '0b9861ab353206cd835e9aa50634f227.jpg', '19', '2026-11-03 15:15:01', ''),
(121, 'Active ', 'fb55739f4bdc5e0f70fa244858b642c9.jpg', '19', '2026-11-03 15:24:40', ''),
(122, 'patronize me today ', '885562e2f19cf3177f46de97aaa951cf.jpg', '21', '2026-11-03 15:27:56', ''),
(123, 'Available  ', 'd5f0d73e7e798e566dd454986842cf0d.jpg', '21', '2026-11-03 15:28:23', ''),
(124, 'welcome to peak world ', 'download (8).jpeg', '22', '2026-11-03 15:47:05', ''),
(125, ' ', 'download (15).jpeg', '22', '2026-11-03 15:53:42', ''),
(126, 'Welcome to the VLAST world \r\nconnect with real people  ', '55b98e30821649ef247497b8ab86c746.jpg', '23', '2026-11-03 16:04:23', ''),
(127, 'updateee !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\r\n ', 'Screenshot 2026-07-26 122249.png', '23', '2026-11-03 16:06:54', ''),
(128, 'Vlast is with the crown ', '3fdf68a44b005164db7dd12fa34a07c8.jpg', '23', '2026-11-03 16:28:24', ''),
(129, 'hii ', '', '17', '2026-11-04 11:11:14', ''),
(130, ' ', 'fe4c2dd3a4f1002a4a54375457099baf.jpg', '25', '2026-11-05 14:23:34', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_details`
--
ALTER TABLE `admin_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`following_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_post`
--
ALTER TABLE `user_post`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_details`
--
ALTER TABLE `admin_details`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follow`
--
ALTER TABLE `follow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `user_post`
--
ALTER TABLE `user_post`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
