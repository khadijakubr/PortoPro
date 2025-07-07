-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 07, 2025 at 02:29 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `PortoPro`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$5yDgpLt3DUYOCz.ny37Ihun31SDbC1mZM507hTovKbphJKnRDdXwK');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int NOT NULL,
  `categoryname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `categoryname`) VALUES
(4, 'ANYTHING WITH MANGO SERIES'),
(2, 'COFFEE'),
(3, 'DESSERT'),
(1, 'MATCHA');

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `brandname` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_info`
--

INSERT INTO `contact_info` (`id`, `name`, `email`, `brandname`) VALUES
(1, 'Nanda', 'nandaaaaa@gmail.com', 'brand name'),
(2, 'Wanda', 'wandaaaaa@gmail.com', 'brand_name');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `category_id`, `title`, `description`, `thumbnail`, `link`) VALUES
(1, 1, 'Cold Whisk Matcha', 'In this video, I use the cold whisk technique to brew matcha without hot water. The result is a smoother and less bitter that brings out the natural notes of the matcha powder.', 'IMG_7081.jpg', 'https://www.instagram.com/reel/DBv3_BMvNp6/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(3, 1, 'Cold Whisk Matcha 2.0', 'This is newer recipe of cold whisk matcha, using oat milk and ceremonial grade matcha makes it way silkier than ever.', 'Cold Whisk Matcha.JPG', 'https://www.instagram.com/reel/DFz9cKnT_GL/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(4, 1, 'Pistachio Matcha Latte', 'Jumping on the pistachio Dubai trend, but turning it into a drink instead, it&#039;s creamy, nutty, and so comforting.', 'IMG_6497.JPG', 'https://www.instagram.com/reel/DCB7n8bvChI/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(5, 1, 'Matchagato', 'I created a matchagato—an affogato but with matcha instead of espresso—as a small, cozy way to celebrate a birthday. It’s a simple yet comforting dessert, combining creamy vanilla ice cream with warm, earthy matcha, perfect for a quiet celebration.', 'IMG_7031.JPG', 'https://www.instagram.com/reel/DHgOF_iTXln/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(6, 1, 'Blueberry Matcha Latte', 'Made a blueberry matcha latte, blending fresh blueberry compote with earthy matcha for a vibrant, refreshing drink. It’s a simple recipe that feels special, perfect for a calm afternoon treat.', 'C786B155-2791-4F32-BA69-2A2D5EE894F3.JPG', 'https://www.instagram.com/reel/DKW7lQpTZES/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(8, 2, 'Pandan Latte', 'Made a pandan latte by mixing homemade pandan syrup with milk, creating a fragrant, lightly sweet pandan-flavored milk. It’s a simple, comforting drink with a taste that feels like home.', 'IMG_7176 (1).jpg', 'https://www.instagram.com/reel/DK9hY6DzIWE/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(9, 2, 'Cucumber Coffee', 'made this by juicing fresh cucumber with water and sugar, then topping it with a shot of espresso. The result is a refreshing, crisp drink with a unique balance of clean cucumber notes and rich coffee', 'IMG_5869.JPG', 'https://www.instagram.com/reel/C7jWxjWPafR/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(10, 2, 'Milk Tea Latte', 'Made a milk tea latte using milk tea as the base for an iced latte. It combines the comforting flavors of tea with the creaminess of milk, creating a refreshing drink perfect for any time of day.', 'IMG_5992.JPG', 'https://www.instagram.com/reel/C8ZcPFSywj4/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(11, 4, 'Mango Sticky Rice', 'EP. 1 | this is the first episode of anything with mango series, made this because mango sticky rice is one of my favorite treats.', 'IMG_6521.JPG', 'https://www.instagram.com/reel/DCJv8AIvoKc/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(13, 4, 'HK Style Mango Pancake', 'EP. 2 | This is one of the world’s most famous mango desserts. It’s simple to make with pantry-friendly ingredients, and as long as you have a good mango, you can’t go wrong.', 'IMG_6545 (1).jpg', 'https://www.instagram.com/reel/DCT-EJZvmyB/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(14, 4, 'Mango Dessert Cup', 'EP. 3 | All you need is 10 minutes and 4 ingredients to make this quick and easy yet delicious mango dessert cup', 'IMG_6534.JPG', 'https://www.instagram.com/reel/DCbqa8ivKrC/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(16, 4, 'Mango Juice but Better', 'EP. 4 | Inspired by king mango thai, but it&#039;s probably healthier cause i don&#039;t use extra sugar in this one', 'IMG_6589.jpg', 'https://www.instagram.com/reel/DCl9jgzPRKd/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA=='),
(17, 4, 'Mango Sandwich', 'EP. 5 | Inspired by ichigo sandwich from Japan that was viral before, but of course instead of ichigo (strawberry), i use mango in this one', 'IMG_6609.JPG', 'https://www.instagram.com/reel/DCtrJUfTVRw/?utm_source=ig_web_copy_link&amp;igsh=MzRlODBiNWFlZA==');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categoryname` (`categoryname`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
