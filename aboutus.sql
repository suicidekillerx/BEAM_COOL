-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2025 at 10:22 AM
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
-- Database: `beam_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `aboutus`
--

CREATE TABLE `aboutus` (
  `id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `content_key` varchar(100) NOT NULL,
  `content_type` enum('text','image','number','url','html') NOT NULL,
  `content_value` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `social_links` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aboutus`
--

INSERT INTO `aboutus` (`id`, `section_name`, `content_key`, `content_type`, `content_value`, `sort_order`, `name`, `position`, `bio`, `image_path`, `email`, `social_links`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hero', 'title_line1', 'text', '\n                tesazeat            ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 08:15:34'),
(2, 'hero', 'title_line2', 'text', 'BEAM', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(3, 'hero', 'subtitle', 'text', 'Crafting the future of fashion, one thread at a time', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(4, 'hero', 'cta_text', 'text', 'Discover Our Story', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(5, 'hero', 'background_image', 'image', 'images/hero.webp', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(6, 'story', 'title', 'text', 'Our Story', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(7, 'story', 'paragraph1', 'text', 'Born from a passion for innovation and a commitment to excellence, Beam emerged as a revolutionary force in the fashion industry. We believe that clothing is more than just fabric—it\'s a statement, a lifestyle, and an expression of individuality.', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(8, 'story', 'paragraph2', 'text', 'Founded in 2020, our journey began with a simple vision: to create clothing that transcends trends and speaks to the soul of the modern individual. Every piece we design carries the weight of our values—quality, sustainability, and timeless elegance.', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(9, 'story', 'quote', 'text', 'Fashion is not just about looking good, it\'s about feeling powerful.', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(10, 'story', 'image', 'image', 'images/collection1.webp', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(11, 'story', 'year', 'number', '2020', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(12, 'story', 'year_label', 'text', 'Founded', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(13, 'mission_vision', 'title', 'text', 'Mission & Vision', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(14, 'mission_vision', 'subtitle', 'text', 'We\'re not just creating clothes—we\'re crafting experiences that empower individuals to express their authentic selves.', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(15, 'mission_vision', 'mission_number', 'text', '01', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(16, 'mission_vision', 'mission_title', 'text', 'Our Mission', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(17, 'mission_vision', 'mission_content', 'text', 'To revolutionize the fashion industry by creating sustainable, high-quality clothing that empowers individuals to express their unique identity while maintaining the highest standards of craftsmanship and ethical production.', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(18, 'mission_vision', 'vision_number', 'text', '02', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(19, 'mission_vision', 'vision_title', 'text', 'Our Vision', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(20, 'mission_vision', 'vision_content', 'text', 'To become the global leader in sustainable fashion, setting new standards for quality, innovation, and social responsibility while inspiring a new generation of conscious consumers.', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(21, 'stats', 'countries_number', 'number', '50', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(22, 'stats', 'countries_label', 'text', 'Countries', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(23, 'stats', 'products_number', 'number', '1000', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(24, 'stats', 'products_label', 'text', 'Products', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(25, 'stats', 'customers_number', 'number', '10000', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(26, 'stats', 'customers_label', 'text', 'Happy Customers', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(27, 'stats', 'years_number', 'number', '5', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(28, 'stats', 'years_label', 'text', 'Years', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(29, 'values', 'title', 'text', 'Our Values', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(30, 'values', 'subtitle', 'text', 'The principles that guide everything we do', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(31, 'values', 'value1_title', 'text', 'Quality', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(32, 'values', 'value1_content', 'text', 'We never compromise on quality. Every stitch, every fabric, every detail is carefully selected and crafted to perfection.', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(33, 'values', 'value1_icon', 'html', '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"></path>', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(34, 'values', 'value2_title', 'text', 'Sustainability', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(35, 'values', 'value2_content', 'text', 'We\'re committed to protecting our planet. Our sustainable practices ensure a better future for generations to come.', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(36, 'values', 'value2_icon', 'html', '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z\"></path>', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(37, 'values', 'value3_title', 'text', 'Innovation', 9, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(38, 'values', 'value3_content', 'text', 'We constantly push boundaries, exploring new technologies and creative solutions to redefine fashion.', 10, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(39, 'values', 'value3_icon', 'html', '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 10V3L4 14h7v7l9-11h-7z\"></path>', 11, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(40, 'timeline', 'title', 'text', 'Our Journey', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(41, 'timeline', 'subtitle', 'text', 'From humble beginnings to global recognition', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(42, 'timeline', 'year1', 'text', '2020', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(43, 'timeline', 'year1_title', 'text', 'The Beginning', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(44, 'timeline', 'year1_content', 'text', 'Founded with a vision to revolutionize fashion through sustainable practices and innovative design.', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(45, 'timeline', 'year2', 'text', '2021', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(46, 'timeline', 'year2_title', 'text', 'First Collection', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(47, 'timeline', 'year2_content', 'text', 'Launched our debut collection, receiving critical acclaim and establishing our unique aesthetic.', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(48, 'timeline', 'year3', 'text', '2022', 9, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(49, 'timeline', 'year3_title', 'text', 'Global Expansion', 10, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(50, 'timeline', 'year3_content', 'text', 'Expanded to international markets, bringing our vision to customers worldwide.', 11, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(51, 'timeline', 'year4', 'text', '2023', 12, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(52, 'timeline', 'year4_title', 'text', 'Sustainability Milestone', 13, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(53, 'timeline', 'year4_content', 'text', 'Achieved 100% sustainable production and became carbon neutral.', 14, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(54, 'timeline', 'year5', 'text', '2024', 15, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(55, 'timeline', 'year5_title', 'text', 'Innovation Hub', 16, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(56, 'timeline', 'year5_content', 'text', 'Launched our innovation hub, pioneering new technologies in sustainable fashion.', 17, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(57, 'team', 'title', 'text', 'Meet Our Team', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(58, 'team', 'subtitle', 'text', 'The passionate individuals behind our success', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(59, 'team', 'member1_name', 'text', 'Sarah Johnson', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(60, 'team', 'member1_position', 'text', 'Creative Director', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(61, 'team', 'member1_description', 'text', 'Visionary leader with 10+ years in fashion design', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(62, 'team', 'member1_image', 'image', 'images/collection2.jpg', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(63, 'team', 'member2_name', 'text', 'Michael Chen', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(64, 'team', 'member2_position', 'text', 'Head of Sustainability', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(65, 'team', 'member2_description', 'text', 'Environmental expert driving our green initiatives', 9, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(66, 'team', 'member2_image', 'image', 'images/collection3.webp', 10, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(67, 'team', 'member3_name', 'text', 'Emma Rodriguez', 11, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(68, 'team', 'member3_position', 'text', 'Production Manager', 12, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(69, 'team', 'member3_description', 'text', 'Ensuring quality and efficiency in every process', 13, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(70, 'team', 'member3_image', 'image', 'images/collection4.webp', 14, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(71, 'cta', 'title', 'text', 'Join Our Journey', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(72, 'cta', 'subtitle', 'text', 'Be part of the revolution. Discover our collections and experience the future of fashion.', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(73, 'cta', 'button1_text', 'text', 'Shop Now', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(74, 'cta', 'button1_url', 'url', 'shop.php', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(75, 'cta', 'button2_text', 'text', 'View Collections', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(76, 'cta', 'button2_url', 'url', 'collections.php', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aboutus`
--
ALTER TABLE `aboutus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_key` (`section_name`,`content_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aboutus`
--
ALTER TABLE `aboutus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
