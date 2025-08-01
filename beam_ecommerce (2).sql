-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 04:49 AM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ReorderProductImages` (IN `p_product_id` INT, IN `p_image_orders` JSON)   BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE image_id INT;
    DECLARE new_order INT;
    DECLARE json_length INT;
    
    -- Get the length of the JSON array
    SET json_length = JSON_LENGTH(p_image_orders);
    
    -- Update each image's order
    WHILE i < json_length DO
        SET image_id = JSON_EXTRACT(p_image_orders, CONCAT('$[', i, '].image_id'));
        SET new_order = JSON_EXTRACT(p_image_orders, CONCAT('$[', i, '].order'));
        
        UPDATE product_images 
        SET image_order = new_order, sort_order = new_order
        WHERE id = image_id AND product_id = p_product_id;
        
        SET i = i + 1;
    END WHILE;
    
    -- Update primary image (first in order)
    UPDATE product_images 
    SET is_primary = 1 
    WHERE product_id = p_product_id AND image_order = 0;
    
    UPDATE product_images 
    SET is_primary = 0 
    WHERE product_id = p_product_id AND image_order > 0;
    
    SELECT 'Images reordered successfully' as message;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetNextImageOrder` (`p_product_id` INT) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE next_order INT;
    
    SELECT COALESCE(MAX(image_order), -1) + 1
    INTO next_order
    FROM product_images
    WHERE product_id = p_product_id;
    
    RETURN next_order;
END$$

DELIMITER ;

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
(1, 'hero', 'title_line1', 'text', '\n                THIS IS', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 14:02:37'),
(2, 'hero', 'title_line2', 'text', '\n                BEAM            ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(3, 'hero', 'subtitle', 'text', '\n            Crafting the future of fashion, one thread at a time        ', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(4, 'hero', 'cta_text', 'text', '\n                    \n                    Discover Our Site', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 14:02:56'),
(5, 'hero', 'background_image', 'image', 'images/img-6888d4db7f959.webp', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 14:04:11'),
(6, 'story', 'title', 'text', '\n                    Our Story                ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(7, 'story', 'paragraph1', 'text', '\n                    Born from a passion for innovation and a commitment to excellence, Beam emerged as a revolutionary force in the fashion industry. We believe that clothing is more than just fabric—it\'s a statement, a lifestyle, and an expression of individuality.                ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(8, 'story', 'paragraph2', 'text', '\n                    Founded in 2020, our journey began with a simple vision: to create clothing that transcends trends and speaks to the soul of the modern individual. Every piece we design carries the weight of our values—quality, sustainability, and timeless elegance.                ', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(9, 'story', 'quote', 'text', '\n                            \"Fashion is not just about looking good, it\'s about feeling powerful.\"\n                        ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(10, 'story', 'image', 'image', 'images/img-6888d31b5f2fb.png', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:56:43'),
(11, 'story', 'year', 'number', '\n                                \n                                2020                                                        ', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(12, 'story', 'year_label', 'text', '\n                                Founded                            ', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(13, 'mission_vision', 'title', 'text', '\n                \n                Mission ertetision                        ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:59'),
(14, 'mission_vision', 'subtitle', 'text', '\n                We\'re not just creating clothes—we\'re crafting experiences that empower individuals to express their authentic selves.            ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(15, 'mission_vision', 'mission_number', 'text', '\n                        01                    ', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(16, 'mission_vision', 'mission_title', 'text', '\n                        Our Mission                    ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(17, 'mission_vision', 'mission_content', 'text', '\n                        To revolutionize the fashion industry by creating sustainable, high-quality clothing that empowers individuals to express their unique identity while maintaining the highest standards of craftsmanship and ethical production.                    ', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(18, 'mission_vision', 'vision_number', 'text', '\n                        02                    ', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(19, 'mission_vision', 'vision_title', 'text', '\n                        Our Vision                    ', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(20, 'mission_vision', 'vision_content', 'text', '\n                        To become the global leader in sustainable fashion, setting new standards for quality, innovation, and social responsibility while inspiring a new generation of conscious consumers.                    ', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(21, 'stats', 'countries_number', 'number', '\n                    \n                    24', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-30 23:35:54'),
(22, 'stats', 'countries_label', 'text', 'state', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-30 23:35:45'),
(23, 'stats', 'products_number', 'number', '\n                    \n                    500', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-30 23:35:15'),
(24, 'stats', 'products_label', 'text', '\n                    Products                ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:18'),
(25, 'stats', 'customers_number', 'number', '3500', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-30 23:35:11'),
(26, 'stats', 'customers_label', 'text', '\n                    Happy Customers                ', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(27, 'stats', 'years_number', 'number', '4', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-30 23:35:00'),
(28, 'stats', 'years_label', 'text', '\n                    Years                ', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(29, 'values', 'title', 'text', '\n                \n                Our Values                        ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(30, 'values', 'subtitle', 'text', '\n                \n                The principles that guide everything we do                        ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(31, 'values', 'value1_title', 'text', '\n                    Quality                ', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(32, 'values', 'value1_content', 'text', '\n                    \n                    We never compromise on quality. Every stitch, every fabric, every detail is carefully selected and crafted to perfection.                                ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(33, 'values', 'value1_icon', 'html', '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"></path>', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 14:21:51'),
(34, 'values', 'value2_title', 'text', '\n                    Sustainability                ', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(35, 'values', 'value2_content', 'text', '\n                    We\'re committed to protecting our planet. Our sustainable practices ensure a better future for generations to come.                ', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(36, 'values', 'value2_icon', 'html', '<circle cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"2\" fill=\"none\" /><path d=\"M12 8v4l3 3\" stroke=\"currentColor\" stroke-width=\"2\" fill=\"none\" />', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 14:15:03'),
(37, 'values', 'value3_title', 'text', '\n                    Innovation                ', 9, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(38, 'values', 'value3_content', 'text', '\n                    We constantly push boundaries, exploring new technologies and creative solutions to redefine fashion.                ', 10, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(39, 'values', 'value3_icon', 'html', '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z\"></path>', 11, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 14:21:56'),
(40, 'timeline', 'title', 'text', '\n                Our Journey            ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(41, 'timeline', 'subtitle', 'text', '\n                From humble beginnings to global recognition            ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(42, 'timeline', 'year1', 'text', '\n                        2020                    ', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(43, 'timeline', 'year1_title', 'text', '\n                        The Beginning                    ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(44, 'timeline', 'year1_content', 'text', '\n                        Founded with a vision to revolutionize fashion through sustainable practices and innovative design.                    ', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(45, 'timeline', 'year2', 'text', '\n                        2021                    ', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(46, 'timeline', 'year2_title', 'text', '\n                        First Collection                    ', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(47, 'timeline', 'year2_content', 'text', '\n                        Launched our debut collection, receiving critical acclaim and establishing our unique aesthetic.                    ', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(48, 'timeline', 'year3', 'text', '\n                        2022                    ', 9, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(49, 'timeline', 'year3_title', 'text', '\n                        Global Expansion                    ', 10, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(50, 'timeline', 'year3_content', 'text', '\n                        Expanded to international markets, bringing our vision to customers worldwide.                    ', 11, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(51, 'timeline', 'year4', 'text', '\n                        2023                    ', 12, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(52, 'timeline', 'year4_title', 'text', '\n                        Sustainability Milestone                    ', 13, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(53, 'timeline', 'year4_content', 'text', '\n                        Achieved 100% sustainable production and became carbon neutral.                    ', 14, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(54, 'timeline', 'year5', 'text', '\n                        2024                    ', 15, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(55, 'timeline', 'year5_title', 'text', '\n                        Innovation Hub                    ', 16, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:19'),
(56, 'timeline', 'year5_content', 'text', '\n                        Launched our innovation hub, pioneering new technologies in sustainable fashion.                    ', 17, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(57, 'team', 'title', 'text', '\n                \n                Meezaet Our Team                        ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(58, 'team', 'subtitle', 'text', '\n                The passionate individuals behind our success            ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(59, 'team', 'member1_name', 'text', '\n                    Sarah Johnson                ', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(60, 'team', 'member1_position', 'text', '\n                    Creative Director                ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(61, 'team', 'member1_description', 'text', '\n                    Visionary leader with 10+ years in fashion design                ', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(62, 'team', 'member1_image', 'image', 'images/collection2.jpg', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(63, 'team', 'member2_name', 'text', '\n                    Michael Chen                ', 7, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(64, 'team', 'member2_position', 'text', '\n                    Head of Sustainability                ', 8, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(65, 'team', 'member2_description', 'text', '\n                    Environmental expert driving our green initiatives                ', 9, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(66, 'team', 'member2_image', 'image', 'images/collection3.webp', 10, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(67, 'team', 'member3_name', 'text', '\n                    Emma Rodriguez                ', 11, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(68, 'team', 'member3_position', 'text', '\n                    Production Manager                ', 12, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(69, 'team', 'member3_description', 'text', '\n                    Ensuring quality and efficiency in every process                ', 13, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(70, 'team', 'member3_image', 'image', 'images/collection4.webp', 14, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(71, 'cta', 'title', 'text', '\n            Join Our Journey        ', 1, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(72, 'cta', 'subtitle', 'text', '\n            Be part of the revolution. Discover our collections and experience the future of fashion.        ', 2, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(73, 'cta', 'button1_text', 'text', 'Shop Now', 3, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(74, 'cta', 'button1_url', 'url', '\n                Shop Now            ', 4, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20'),
(75, 'cta', 'button2_text', 'text', 'View Collections', 5, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-27 21:22:28'),
(76, 'cta', 'button2_url', 'url', '\n                View Collections            ', 6, '', '', NULL, NULL, NULL, NULL, 0, 1, '2025-07-27 21:22:28', '2025-07-29 13:55:20');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `session_id`, `product_id`, `size`, `quantity`, `created_at`, `updated_at`) VALUES
(107, 'dhmrknssc0a7ekm1cju8k9s1eg', 10, 'L', 1, '2025-07-27 10:19:13', '2025-07-27 10:19:13'),
(110, 'q8gt6vm2smume5jb5dh3mgaunl', 8, 'M', 1, '2025-07-27 19:23:17', '2025-07-27 19:23:17'),
(117, 'pcau4tjue68hrfmvhej9uf6o4p', 15, 'XS', 3, '2025-07-28 05:02:38', '2025-07-28 05:02:45'),
(123, '2s29o19759naho2qb0oesna7s4', 1, 'M', 2, '2025-07-30 11:39:02', '2025-07-30 11:39:02'),
(124, '2s29o19759naho2qb0oesna7s4', 2, 'M', 2, '2025-07-30 11:39:02', '2025-07-30 11:39:02'),
(125, '65goaeua3mcj04lq6e55kuda12', 1, 'M', 2, '2025-07-30 11:48:12', '2025-07-30 11:48:12'),
(126, '65goaeua3mcj04lq6e55kuda12', 2, 'M', 2, '2025-07-30 11:48:12', '2025-07-30 11:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'mrewel', 'mrewel', 'images/categories/category_1_1753654276.jpg', 'All products', 1, 1, '2025-07-27 01:32:39', '2025-07-27 22:11:16'),
(2, 'TEES', 'tees', 'images/2.webp', 'T-shirts collection', 3, 1, '2025-07-27 01:32:39', '2025-07-28 01:55:07'),
(3, 'HOODIES', 'hoodies', 'images/3.webp', 'Hoodies collection', 9, 1, '2025-07-27 01:32:39', '2025-07-28 01:55:07'),
(4, 'SHORTS', 'shorts', 'images/4.webp', 'Shorts collection', 4, 1, '2025-07-27 01:32:39', '2025-07-28 01:55:07'),
(5, 'JACKETS', 'jackets', 'images/5.webp', 'Jackets collection', 6, 1, '2025-07-27 01:32:39', '2025-07-28 01:55:07'),
(6, 'iyed', 'new-category', 'images/7.webp', 'New category', 7, 1, '2025-07-27 01:32:39', '2025-07-28 01:55:07'),
(7, 'ANOTHER CATEGORY', 'another-category', 'images/8.jpg', 'Another category', 8, 1, '2025-07-27 01:32:39', '2025-07-28 01:55:07'),
(204, 'test category', 'test-category', 'images/categories/category_1753654706_3786.png', 'test category test category test category', 5, 1, '2025-07-27 22:18:26', '2025-07-28 01:55:07'),
(206, 'éazzd', 'azzd', 'images/categories/category_1753667683_8094.png', 'sdsqdsqf', 2, 1, '2025-07-28 01:54:43', '2025-07-28 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `collections`
--

INSERT INTO `collections` (`id`, `name`, `slug`, `image`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'collect iyed', 'summer-vibes', 'images/collection1.webp', 'Explore our latest summer collection', 1, 1, '2025-07-27 01:32:39', '2025-07-27 02:06:30'),
(2, 'URBAN STREET', 'urban-street', 'images/collection2.jpg', 'Street style meets comfort', 2, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(3, 'WINTER ESSENTIALS', 'winter-essentials', 'images/collection3.webp', 'Stay warm and stylish', 3, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(4, 'LIMITED EDITION', 'limited-edition', 'images/collection4.webp', 'Exclusive designs for true fans', 4, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(118, 'youssef', 'youssef', 'images/collections/collection_1753667613_3803.jpg', 'youssef', 0, 1, '2025-07-28 01:53:33', '2025-07-28 01:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `footer_links`
--

CREATE TABLE `footer_links` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `link_text` varchar(255) NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_links`
--

INSERT INTO `footer_links` (`id`, `section_id`, `link_text`, `link_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(6, 2, 'RETURN &=', '#', 0, 1, '2025-07-27 01:32:39', '2025-07-29 22:25:54'),
(7, 2, 'PRIVACY POLICY', '#', 2, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(8, 2, 'COOKIE POLICY', '#', 3, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(9, 2, 'TERMS OF SERVICE', '#', 4, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(10, 2, 'LEGAL NOTICE', '#', 5, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(291, 59, 'test', 'https://weult.com', 0, 1, '2025-07-29 22:34:17', '2025-07-30 01:00:54');

-- --------------------------------------------------------

--
-- Table structure for table `footer_sections`
--

CREATE TABLE `footer_sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_sections`
--

INSERT INTO `footer_sections` (`id`, `section_name`, `section_title`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'bdhjgtdt', 'bdhjgtdt', 2, 1, '2025-07-27 01:32:39', '2025-07-30 01:26:33'),
(59, 'test section', 'test section', 1, 1, '2025-07-29 22:34:52', '2025-07-30 23:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `shipping_notes` text DEFAULT NULL,
  `subtotal` decimal(10,3) NOT NULL,
  `tax` decimal(10,3) NOT NULL,
  `shipping_cost` decimal(10,3) NOT NULL,
  `discount` decimal(10,3) DEFAULT 0.000,
  `promo_code_id` int(11) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `total` decimal(10,3) NOT NULL,
  `payment_method` enum('cash_on_delivery') DEFAULT 'cash_on_delivery',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `session_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `shipping_city`, `shipping_postal_code`, `shipping_notes`, `subtotal`, `tax`, `shipping_cost`, `discount`, `promo_code_id`, `promo_code`, `total`, `payment_method`, `order_status`, `notes`, `created_at`, `updated_at`, `tracking_number`, `shipped_at`, `delivered_at`, `admin_notes`, `payment_status`) VALUES
(3, 'BEAM202507271140136B7D', 'dhmrknssc0a7ekm1cju8k9s1eg', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', 'Delivery Notes', 595.000, 113.050, 15.000, 0.000, NULL, NULL, 723.050, 'cash_on_delivery', 'pending', 'Order Notes', '2025-07-27 09:40:13', '2025-07-27 09:40:13', NULL, NULL, NULL, NULL, 'pending'),
(4, 'BEAM202507272144133A4E', 'pcau4tjue68hrfmvhej9uf6o4p', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 555.000, 83.250, 8.000, 0.000, NULL, NULL, 646.250, 'cash_on_delivery', 'pending', '', '2025-07-27 19:44:13', '2025-07-27 19:44:13', NULL, NULL, NULL, NULL, 'pending'),
(5, 'BEAM202507272342591520', 'pcau4tjue68hrfmvhej9uf6o4p', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', 'belhi , ijewni wra carfour', 180.000, 27.000, 8.000, 0.000, NULL, NULL, 215.000, 'cash_on_delivery', 'pending', 'blhi , 7otou fi sandou9', '2025-07-27 21:42:59', '2025-07-27 21:42:59', NULL, NULL, NULL, NULL, 'pending'),
(6, 'BEAM20250727235419E922', 'pcau4tjue68hrfmvhej9uf6o4p', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Zarzis', '1002', 'ijeni wra carfour', 225.000, 33.750, 8.000, 0.000, NULL, NULL, 266.750, 'cash_on_delivery', 'pending', 'nhebou fi bakou roze', '2025-07-27 21:54:19', '2025-07-27 21:54:19', NULL, NULL, NULL, NULL, 'pending'),
(7, 'BEAM20250728045308DE2E', 'pcau4tjue68hrfmvhej9uf6o4p', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', 'Delivery Notes', 50.000, 7.500, 8.000, 0.000, NULL, NULL, 65.500, 'cash_on_delivery', 'shipped', 'Order Notes', '2025-07-28 02:53:08', '2025-07-28 02:54:39', 'azzaezaezae', NULL, NULL, 'azeazeazezae', 'pending'),
(8, 'BEAM20250729114849B59D', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Gafsa', '1002', '', 135.000, 20.250, 8.000, 0.000, NULL, NULL, 163.250, 'cash_on_delivery', 'confirmed', 'aze adsfgdfshg', '2025-07-29 09:48:49', '2025-07-29 09:53:30', '', NULL, NULL, '', 'pending'),
(9, 'BEAM202507301414598C06', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 85.000, 12.750, 4.000, 0.000, NULL, NULL, 101.750, 'cash_on_delivery', 'pending', 'QS', '2025-07-30 12:14:59', '2025-07-30 12:14:59', NULL, NULL, NULL, NULL, 'pending'),
(10, 'BEAM20250730153719E79B', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 425.000, 63.750, 4.000, 0.000, NULL, NULL, 492.750, 'cash_on_delivery', 'pending', '', '2025-07-30 13:37:19', '2025-07-30 13:37:19', NULL, NULL, NULL, NULL, 'pending'),
(11, 'BEAM2025073015445688A0', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 300.000, 45.000, 4.000, 0.000, NULL, NULL, 349.000, 'cash_on_delivery', 'pending', '', '2025-07-30 13:44:56', '2025-07-30 13:44:56', NULL, NULL, NULL, NULL, 'pending'),
(12, 'BEAM20250730184026FA72', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 48.000, 7.200, 4.000, 0.000, NULL, NULL, 59.200, 'cash_on_delivery', 'pending', '', '2025-07-30 16:40:26', '2025-07-30 16:40:26', NULL, NULL, NULL, NULL, 'pending'),
(16, 'BEAM2025073019240973B7', 'qd7vn2f7h4vftdso896mgrbp3k', 'hama', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 115.000, 17.250, 4.000, 11.500, 11, 'TEST10', 124.750, 'cash_on_delivery', 'delivered', '', '2025-07-30 17:24:09', '2025-07-30 17:30:04', '', NULL, NULL, '', 'pending'),
(17, 'BEAM202507301932077E79', 'qd7vn2f7h4vftdso896mgrbp3k', 'hama', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 650.000, 97.500, 4.000, 0.000, NULL, NULL, 751.500, 'cash_on_delivery', 'delivered', '', '2025-07-30 17:32:07', '2025-07-30 17:32:43', '', NULL, NULL, '', 'pending'),
(18, 'BEAM202507302029245E14', 'qd7vn2f7h4vftdso896mgrbp3k', 'hama', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 260.000, 39.000, 4.000, 0.000, NULL, NULL, 303.000, 'cash_on_delivery', 'confirmed', '', '2025-07-30 18:29:24', '2025-07-30 18:29:55', NULL, NULL, NULL, NULL, 'pending'),
(19, 'BEAM202507302044106AB4', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 325.000, 48.750, 4.000, 0.000, NULL, NULL, 377.750, 'cash_on_delivery', 'confirmed', '', '2025-07-30 18:44:10', '2025-07-30 18:49:34', NULL, NULL, NULL, NULL, 'pending'),
(20, 'BEAM20250730205141F8BB', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 520.000, 78.000, 4.000, 0.000, NULL, NULL, 602.000, 'cash_on_delivery', 'confirmed', '', '2025-07-30 18:51:41', '2025-07-30 18:52:05', NULL, NULL, NULL, NULL, 'pending'),
(21, 'BEAM202507302054238CAD', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 650.000, 97.500, 4.000, 0.000, NULL, NULL, 751.500, 'cash_on_delivery', 'pending', '', '2025-07-30 18:54:23', '2025-07-30 18:54:23', NULL, NULL, NULL, NULL, 'pending'),
(22, 'BEAM202507310104227BE6', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 150.000, 22.500, 4.000, 15.000, 11, 'TEST10', 161.500, 'cash_on_delivery', 'pending', '', '2025-07-30 23:04:22', '2025-07-30 23:04:22', NULL, NULL, NULL, NULL, 'pending'),
(23, 'BEAM2025073101064364D5', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 82.000, 12.300, 4.000, 0.000, NULL, NULL, 98.300, 'cash_on_delivery', 'cancelled', '', '2025-07-30 23:06:43', '2025-07-30 23:07:24', NULL, NULL, NULL, NULL, 'pending'),
(24, 'BEAM202507310124114A19', 'qd7vn2f7h4vftdso896mgrbp3k', 'iyed hosni', 'iyed.hosni123@gmail.com', '27 324 103', '16 Rue Cyrus le Grand', 'Tunis', '1002', '', 400.000, 0.000, 8.000, 40.000, 11, 'TEST10', 368.000, 'cash_on_delivery', 'confirmed', '', '2025-07-30 23:24:11', '2025-07-30 23:25:21', NULL, NULL, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,3) NOT NULL,
  `product_sale_price` decimal(10,3) DEFAULT NULL,
  `size` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `product_sale_price`, `size`, `quantity`, `total_price`, `created_at`) VALUES
(1, 3, 8, 'PREMIUM HOODIE NAVY', 105.000, 85.000, 'XS', 7, 595.000, '2025-07-27 09:40:13'),
(2, 4, 8, 'PREMIUM HOODIE NAVY', 105.000, 85.000, 'M', 6, 510.000, '2025-07-27 19:44:13'),
(3, 4, 9, 'SIGNATURE SHORTS GRAY', 65.000, 45.000, 'L', 1, 45.000, '2025-07-27 19:44:13'),
(4, 5, 9, 'SIGNATURE SHORTS GRAY', 65.000, 45.000, 'S', 4, 180.000, '2025-07-27 21:42:59'),
(5, 6, 12, 'LIMITED EDITION TEE', 95.000, 75.000, 'L', 3, 225.000, '2025-07-27 21:54:19'),
(6, 7, 15, 'Smartphone S24', 50.000, NULL, 'XL', 1, 50.000, '2025-07-28 02:53:08'),
(7, 8, 9, 'SIGNATURE SHORTS GRAY', 65.000, 45.000, 'S', 3, 135.000, '2025-07-29 09:48:49'),
(8, 9, 8, 'PREMIUM HOODIE NAVY', 105.000, 85.000, 'XXL', 1, 85.000, '2025-07-30 12:14:59'),
(9, 10, 8, 'PREMIUM HOODIE NAVY', 105.000, 85.000, 'S', 5, 425.000, '2025-07-30 13:37:19'),
(10, 11, 12, 'LIMITED EDITION TEE', 95.000, 75.000, 'XS', 4, 300.000, '2025-07-30 13:44:56'),
(11, 12, 6, 'BEAM THE TEAM TEE WHITE', 68.000, 48.000, 'S', 1, 48.000, '2025-07-30 16:40:26'),
(15, 16, 5, 'CAMO CARGO SHORTS DARK', 85.000, 65.000, 'XL', 1, 65.000, '2025-07-30 17:24:09'),
(16, 16, 15, 'Smartphone S24', 50.000, NULL, '2XL', 1, 50.000, '2025-07-30 17:24:09'),
(17, 17, 11, 'PREMIUM POLO WHITE', 85.000, 65.000, 'XS', 10, 650.000, '2025-07-30 17:32:07'),
(18, 18, 11, 'PREMIUM POLO WHITE', 85.000, 65.000, 'S', 4, 260.000, '2025-07-30 18:29:24'),
(19, 19, 11, 'PREMIUM POLO WHITE', 85.000, 65.000, 'XS', 5, 325.000, '2025-07-30 18:44:10'),
(20, 20, 11, 'PREMIUM POLO WHITE', 85.000, 65.000, 'XXL', 8, 520.000, '2025-07-30 18:51:41'),
(21, 21, 11, 'PREMIUM POLO WHITE', 85.000, 65.000, 'XS', 10, 650.000, '2025-07-30 18:54:23'),
(22, 22, 15, 'Smartphone S24', 50.000, NULL, '2XL', 3, 150.000, '2025-07-30 23:04:22'),
(23, 23, 1, 'ASTRO HOODIE WHITE', 96.000, 82.000, 'XS', 1, 82.000, '2025-07-30 23:06:43'),
(24, 24, 16, 'yassine', 400.000, NULL, 'XS', 1, 400.000, '2025-07-30 23:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `is_on_sale` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `show_stock` tinyint(1) DEFAULT 1,
  `stock_status` enum('in_stock','low_stock') DEFAULT 'in_stock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `cost_price`, `color`, `category_id`, `collection_id`, `is_featured`, `is_bestseller`, `is_on_sale`, `is_active`, `created_at`, `updated_at`, `show_stock`, `stock_status`) VALUES
(1, 'ASTRO HOODIE WHITE', 'astro-hoodie-white', 'Premium oversized hoodie featuring our signature Astro design. Made from heavyweight cotton blend for ultimate comfort and style.', 'Signature Astro design hoodie', 96.00, 82.00, 45.00, 'White', 3, 1, 1, 0, 1, 1, '2025-07-27 02:02:30', '2025-07-30 23:07:56', 0, 'in_stock'),
(2, 'STRIPED POLO SHIRT NAVY', 'striped-polo-shirt-navy', 'Classic striped polo shirt in navy blue. Made from premium cotton blend for comfort and style.', 'Classic striped polo in navy', 65.00, 45.00, 30.00, 'Navy', 2, 1, 1, 0, 1, 1, '2025-07-27 02:02:30', '2025-07-27 02:02:30', 1, 'in_stock'),
(3, 'CAMO TANK TOP YELLOW', 'camo-tank-top-yellow', 'Eye-catching camo tank top in vibrant yellow. Perfect for summer workouts and casual outings.', 'Vibrant yellow camo tank top', 55.00, 35.00, 20.00, 'Yellow', 2, 1, 1, 0, 1, 1, '2025-07-27 02:02:30', '2025-07-27 02:02:30', 1, 'in_stock'),
(4, 'GRAPHIC TEE WHITE BLACK', 'graphic-tee-white-black', 'Bold graphic t-shirt with striking black and white design. Made from premium cotton for ultimate comfort.', 'Bold black and white graphic tee', 62.00, 42.00, 28.00, 'White', 2, 2, 1, 0, 1, 1, '2025-07-27 02:02:30', '2025-07-27 02:02:30', 1, 'in_stock'),
(5, 'CAMO CARGO SHORTS DARK', 'camo-cargo-shorts-dark', 'Functional cargo shorts with camo pattern. Multiple pockets and comfortable fit for outdoor activities.', 'Functional camo cargo shorts', 85.00, 65.00, 40.00, 'Dark Green', 4, 2, 1, 0, 1, 1, '2025-07-27 02:02:30', '2025-07-27 02:02:30', 1, 'in_stock'),
(6, 'BEAM THE TEAM TEE WHITE', 'beam-the-team-tee-white', 'Official Beam The Team t-shirt featuring our brand logo. Premium quality cotton with perfect fit.', 'Official Beam The Team logo tee', 68.00, 48.00, 32.00, 'White', 2, 4, 1, 0, 1, 1, '2025-07-27 02:02:30', '2025-07-27 02:02:30', 1, 'in_stock'),
(7, 'CLASSIC BEAM TEE BLACK', 'classic-beam-tee-black', 'Our most popular t-shirt design in classic black. Premium cotton with embroidered Beam logo.', 'Classic black Beam logo tee', 75.00, 55.00, 35.00, 'Black', 2, 2, 0, 1, 1, 1, '2025-07-27 02:02:30', '2025-07-27 02:02:30', 1, 'in_stock'),
(8, 'PREMIUM HOODIE NAVY', 'premium-hoodie-navy', 'Premium quality hoodie in navy blue. Heavyweight cotton blend with embroidered logo.', 'Premium navy hoodie with logo', 105.00, 85.00, 50.00, 'Navy', 3, 3, 0, 1, 1, 1, '2025-07-27 02:02:31', '2025-07-27 02:02:31', 1, 'in_stock'),
(9, 'SIGNATURE SHORTS GRAY', 'signature-shorts-gray', 'Signature athletic shorts in gray. Perfect for workouts and casual wear with comfortable fit.', 'Signature gray athletic shorts', 65.00, 45.00, 30.00, 'Gray', 4, 1, 0, 1, 1, 1, '2025-07-27 02:02:31', '2025-07-27 02:02:31', 1, 'in_stock'),
(10, 'ELITE JACKET BLACK', 'elite-jacket-black', 'Elite performance jacket in black. Water-resistant material with multiple pockets.', 'Elite black performance jacket', 150.00, 120.00, 70.00, 'Black', 5, 3, 0, 1, 1, 1, '2025-07-27 02:02:31', '2025-07-27 02:02:31', 1, 'in_stock'),
(11, 'PREMIUM POLO WHITE', 'premium-polo-white', 'Premium polo shirt in white. Classic design with embroidered logo for professional look.', 'Premium white polo shirt', 85.00, 65.00, 40.00, 'White', 2, 1, 0, 1, 1, 1, '2025-07-27 02:02:31', '2025-07-30 18:55:46', 1, 'in_stock'),
(12, 'LIMITED EDITION TEE', 'limited-edition-tee', 'Exclusive limited edition t-shirt with unique design. Only available for a short time.', 'Exclusive limited edition design', 95.00, 75.00, 45.00, 'White', 2, 4, 0, 1, 1, 1, '2025-07-27 02:02:31', '2025-07-27 02:02:31', 1, 'in_stock'),
(15, 'Smartphone S24', 'smartphone-s24', 'Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description Full Description ', 'Short Description', 50.00, NULL, 10.00, 'black', 3, NULL, 1, 0, 0, 1, '2025-07-28 00:44:45', '2025-07-29 09:50:20', 0, 'in_stock'),
(16, 'yassine', 'yassine', 'Full Description Full Description Full Description', 'Short Description Short Description Short Description', 400.00, NULL, 10.00, 'black', 6, 3, 0, 0, 0, 1, '2025-07-30 23:20:21', '2025-07-30 23:25:21', 1, 'in_stock'),
(17, 'iyed hosni', 'iyed-hosni', 'uykreytureduk', 'ugfk:uyomi', 547.00, NULL, 42.00, 'hgfjh,gjyfty', 6, 4, 0, 0, 0, 1, '2025-07-30 23:48:52', '2025-07-30 23:48:52', 1, 'in_stock');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `image_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `sort_order`, `image_order`, `created_at`) VALUES
(1, 1, 'images/product1-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(2, 1, 'images/product1-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(3, 2, 'images/product2-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(4, 2, 'images/product2-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(5, 3, 'images/product3-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(6, 3, 'images/product3-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(7, 4, 'images/product4-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(8, 4, 'images/product4-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(9, 5, 'images/product5-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(10, 5, 'images/product5-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(11, 6, 'images/product6-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(12, 6, 'images/product6-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(13, 7, 'images/product1-image1.webp', 1, 0, 0, '2025-07-27 02:02:30'),
(14, 7, 'images/product1-image2.webp', 0, 1, 1, '2025-07-27 02:02:30'),
(15, 8, 'images/product2-image1.webp', 1, 0, 0, '2025-07-27 02:02:31'),
(16, 8, 'images/product2-image2.webp', 0, 1, 1, '2025-07-27 02:02:31'),
(17, 9, 'images/product3-image1.webp', 1, 0, 0, '2025-07-27 02:02:31'),
(18, 9, 'images/product3-image2.webp', 0, 1, 1, '2025-07-27 02:02:31'),
(19, 10, 'images/product4-image1.webp', 1, 0, 0, '2025-07-27 02:02:31'),
(20, 10, 'images/product4-image2.webp', 0, 1, 1, '2025-07-27 02:02:31'),
(21, 11, 'images/product5-image1.webp', 1, 0, 0, '2025-07-27 02:02:31'),
(22, 11, 'images/product5-image2.webp', 0, 1, 1, '2025-07-27 02:02:31'),
(23, 12, 'images/product6-image1.webp', 1, 0, 0, '2025-07-27 02:02:31'),
(24, 12, 'images/product6-image2.webp', 0, 1, 1, '2025-07-27 02:02:31'),
(26, 15, 'images/products/product_1753663485_1759_0.png', 1, 0, 0, '2025-07-28 00:44:45'),
(27, 15, 'images/products/product_1753663485_8931_1.jpg', 0, 1, 1, '2025-07-28 00:44:45'),
(28, 15, 'images/products/product_1753663485_9226_2.png', 0, 2, 2, '2025-07-28 00:44:45'),
(29, 15, 'images/products/product_1753663485_9102_3.png', 0, 3, 3, '2025-07-28 00:44:45'),
(30, 16, 'images/products/product_1753917621_6201_0.jpeg', 1, 0, 0, '2025-07-30 23:20:21'),
(31, 17, 'images/products/product_1753919332_5846_0.png', 1, 0, 0, '2025-07-30 23:48:52'),
(32, 17, 'images/products/product_1753919332_8019_1.png', 0, 1, 1, '2025-07-30 23:48:52'),
(33, 17, 'images/products/product_1753919332_5802_2.png', 0, 2, 2, '2025-07-30 23:48:52'),
(34, 17, 'images/products/product_1753919332_6625_3.png', 0, 3, 3, '2025-07-30 23:48:52'),
(35, 17, 'images/products/product_1753919332_9971_4.png', 0, 4, 4, '2025-07-30 23:48:52'),
(36, 17, 'images/products/product_1753919332_4070_5.png', 0, 5, 5, '2025-07-30 23:48:52'),
(37, 17, 'images/products/product_1753919332_1898_6.png', 0, 6, 6, '2025-07-30 23:48:52'),
(38, 17, 'images/products/product_1753919332_9567_7.png', 0, 7, 7, '2025-07-30 23:48:52'),
(39, 17, 'images/products/product_1753919332_7485_8.png', 0, 8, 8, '2025-07-30 23:48:52'),
(40, 17, 'images/products/product_1753919332_1940_9.png', 0, 9, 9, '2025-07-30 23:48:52'),
(41, 17, 'images/products/product_1753919332_2667_10.png', 0, 10, 10, '2025-07-30 23:48:52'),
(42, 17, 'images/products/product_1753919332_2253_11.png', 0, 11, 11, '2025-07-30 23:48:52'),
(43, 17, 'images/products/product_1753919332_2920_12.png', 0, 12, 12, '2025-07-30 23:48:52'),
(44, 17, 'images/products/product_1753919332_6416_13.png', 0, 13, 13, '2025-07-30 23:48:52'),
(45, 17, 'images/products/product_1753919332_7442_14.png', 0, 14, 14, '2025-07-30 23:48:52'),
(46, 17, 'images/products/product_1753919332_4571_15.png', 0, 15, 15, '2025-07-30 23:48:52'),
(47, 17, 'images/products/product_1753919332_8827_16.png', 0, 16, 16, '2025-07-30 23:48:52'),
(48, 17, 'images/products/product_1753919332_3845_17.png', 0, 17, 17, '2025-07-30 23:48:52');

--
-- Triggers `product_images`
--
DELIMITER $$
CREATE TRIGGER `tr_product_images_before_insert` BEFORE INSERT ON `product_images` FOR EACH ROW BEGIN
    IF NEW.image_order IS NULL OR NEW.image_order = 0 THEN
        SET NEW.image_order = GetNextImageOrder(NEW.product_id);
        SET NEW.sort_order = NEW.image_order;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_images_ordered`
-- (See below for the actual view)
--
CREATE TABLE `product_images_ordered` (
`id` int(11)
,`product_id` int(11)
,`image_path` varchar(255)
,`is_primary` tinyint(1)
,`sort_order` int(11)
,`image_order` int(11)
,`created_at` timestamp
,`product_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 'XS', 6, '2025-07-27 02:02:30', '2025-07-30 23:07:24'),
(2, 1, 'S', 6, '2025-07-27 02:02:30', '2025-07-29 03:01:06'),
(3, 1, 'M', 8, '2025-07-27 02:02:30', '2025-07-29 03:01:06'),
(4, 1, 'L', 6, '2025-07-27 02:02:30', '2025-07-29 03:01:06'),
(5, 1, 'XL', 5, '2025-07-27 02:02:30', '2025-07-29 03:01:06'),
(6, 1, 'XXL', 8, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(7, 2, 'XS', 10, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(8, 2, 'S', 15, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(9, 2, 'M', 20, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(10, 2, 'L', 18, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(11, 2, 'XL', 12, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(12, 2, 'XXL', 8, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(13, 3, 'XS', 10, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(14, 3, 'S', 15, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(15, 3, 'M', 20, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(16, 3, 'L', 18, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(17, 3, 'XL', 12, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(18, 3, 'XXL', 8, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(19, 4, 'XS', 10, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(20, 4, 'S', 15, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(21, 4, 'M', 20, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(22, 4, 'L', 18, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(23, 4, 'XL', 12, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(24, 4, 'XXL', 8, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(25, 5, 'XS', 10, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(26, 5, 'S', 15, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(27, 5, 'M', 20, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(28, 5, 'L', 18, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(29, 5, 'XL', 12, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(30, 5, 'XXL', 8, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(31, 6, 'XS', 10, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(32, 6, 'S', 15, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(33, 6, 'M', 20, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(34, 6, 'L', 18, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(35, 6, 'XL', 12, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(36, 6, 'XXL', 8, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(37, 7, 'XS', 10, '2025-07-27 02:02:30', '2025-07-27 02:02:30'),
(38, 7, 'S', 15, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(39, 7, 'M', 20, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(40, 7, 'L', 18, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(41, 7, 'XL', 12, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(42, 7, 'XXL', 8, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(43, 8, 'XS', 10, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(44, 8, 'S', 15, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(45, 8, 'M', 20, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(46, 8, 'L', 18, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(47, 8, 'XL', 12, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(48, 8, 'XXL', 8, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(49, 9, 'XS', 10, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(50, 9, 'S', 15, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(51, 9, 'M', 20, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(52, 9, 'L', 18, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(53, 9, 'XL', 12, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(54, 9, 'XXL', 8, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(55, 10, 'XS', 10, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(56, 10, 'S', 15, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(57, 10, 'M', 20, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(58, 10, 'L', 18, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(59, 10, 'XL', 12, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(60, 10, 'XXL', 8, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(61, 11, 'XS', 5, '2025-07-27 02:02:31', '2025-07-30 18:49:34'),
(62, 11, 'S', 11, '2025-07-27 02:02:31', '2025-07-30 18:29:55'),
(63, 11, 'M', 20, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(64, 11, 'L', 18, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(65, 11, 'XL', 12, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(66, 11, 'XXL', 0, '2025-07-27 02:02:31', '2025-07-30 18:52:05'),
(67, 12, 'XS', 10, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(68, 12, 'S', 15, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(69, 12, 'M', 20, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(70, 12, 'L', 18, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(71, 12, 'XL', 12, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(72, 12, 'XXL', 8, '2025-07-27 02:02:31', '2025-07-27 02:02:31'),
(80, 15, 'XS', 43, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(81, 15, 'S', 18, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(82, 15, 'M', 13, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(83, 15, 'L', 38, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(84, 15, 'XL', 28, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(85, 15, '2XL', 47, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(86, 15, '3XL', 22, '2025-07-28 00:44:45', '2025-07-29 09:50:20'),
(87, 16, 'XS', 19, '2025-07-30 23:20:21', '2025-07-30 23:25:21'),
(88, 16, 'S', 0, '2025-07-30 23:20:21', '2025-07-30 23:20:21'),
(89, 16, 'M', 0, '2025-07-30 23:20:21', '2025-07-30 23:20:21'),
(90, 16, 'L', 0, '2025-07-30 23:20:21', '2025-07-30 23:20:21'),
(91, 16, 'XL', 0, '2025-07-30 23:20:21', '2025-07-30 23:20:21'),
(92, 16, '2XL', 0, '2025-07-30 23:20:21', '2025-07-30 23:20:21'),
(93, 16, '3XL', 0, '2025-07-30 23:20:21', '2025-07-30 23:20:21'),
(94, 17, 'XS', 1, '2025-07-30 23:48:52', '2025-07-30 23:48:52'),
(95, 17, 'S', 0, '2025-07-30 23:48:52', '2025-07-30 23:48:52'),
(96, 17, 'M', 0, '2025-07-30 23:48:52', '2025-07-30 23:48:52'),
(97, 17, 'L', 0, '2025-07-30 23:48:52', '2025-07-30 23:48:52'),
(98, 17, 'XL', 0, '2025-07-30 23:48:52', '2025-07-30 23:48:52'),
(99, 17, '2XL', 0, '2025-07-30 23:48:52', '2025-07-30 23:48:52'),
(100, 17, '3XL', 0, '2025-07-30 23:48:52', '2025-07-30 23:48:52');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed_amount','free_shipping') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,3) NOT NULL,
  `min_order_amount` decimal(10,3) DEFAULT 0.000,
  `max_discount` decimal(10,3) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT NULL,
  `applies_to` enum('all','categories','products') DEFAULT 'all',
  `category_ids` text DEFAULT NULL,
  `product_ids` text DEFAULT NULL,
  `excluded_categories` text DEFAULT NULL,
  `excluded_products` text DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_first_time_only` tinyint(1) DEFAULT 0,
  `is_single_use` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `name`, `description`, `type`, `value`, `min_order_amount`, `max_discount`, `usage_limit`, `used_count`, `user_limit`, `applies_to`, `category_ids`, `product_ids`, `excluded_categories`, `excluded_products`, `start_date`, `end_date`, `is_active`, `is_first_time_only`, `is_single_use`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Welcome Discount', 'Get 10% off your first order', 'percentage', 10.000, 50.000, 25.000, 100, 0, NULL, 'all', NULL, NULL, NULL, NULL, '2025-07-30 05:47:53', '2025-08-29 05:47:53', 1, 0, 0, '2025-07-30 04:47:53', '2025-07-30 17:28:00'),
(4, 'FLAT15', 'Flat Discount', 'Get $15 off your order', 'fixed_amount', 15.000, 75.000, 15.000, 75, 0, NULL, 'all', NULL, NULL, NULL, NULL, '2025-07-30 05:47:53', '2025-08-13 05:47:53', 1, 0, 0, '2025-07-30 04:47:53', '2025-07-30 04:47:53'),
(5, 'NEWCUSTOMER', 'New Customer Special', '25% off for new customers', 'percentage', 25.000, 25.000, 30.000, 1000, 0, NULL, 'all', NULL, NULL, NULL, NULL, '2025-07-30 05:47:53', '2025-10-28 05:47:53', 1, 1, 0, '2025-07-30 04:47:53', '2025-07-30 04:47:53'),
(11, 'TEST10', 'Test Discount', '10% off for testing (no minimum)', 'percentage', 10.000, 0.000, 50.000, 1000, 3, NULL, 'all', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, '2025-07-30 11:40:07', '2025-07-30 23:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `promo_code_usage`
--

CREATE TABLE `promo_code_usage` (
  `id` int(11) NOT NULL,
  `promo_code_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,3) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_code_usage`
--

INSERT INTO `promo_code_usage` (`id`, `promo_code_id`, `session_id`, `user_id`, `order_id`, `discount_amount`, `used_at`) VALUES
(1, 11, 'qd7vn2f7h4vftdso896mgrbp3k', NULL, 16, 11.500, '2025-07-30 17:24:09'),
(2, 11, 'qd7vn2f7h4vftdso896mgrbp3k', NULL, 22, 15.000, '2025-07-30 23:04:22'),
(3, 11, 'qd7vn2f7h4vftdso896mgrbp3k', NULL, 24, 40.000, '2025-07-30 23:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'brand_name', 'BEAM™', '2025-07-27 01:32:39', '2025-07-27 02:07:29'),
(2, 'brand_logo', 'images/logo_1753916424_688aa4088fcf3.webp', '2025-07-27 01:32:39', '2025-07-30 23:00:24'),
(3, 'hero_image', 'images/logo_1753916489_688aa449e770f.webp', '2025-07-27 01:32:39', '2025-07-30 23:01:29'),
(4, 'announcement_text_1', 'FOR THE HUSTLERS, BY THE HUSTLERS', '2025-07-27 01:32:39', '2025-07-30 23:03:03'),
(5, 'announcement_text_2', 'waaaaaaaaaaaaaaaaaaaa', '2025-07-27 01:32:39', '2025-07-30 01:24:51'),
(6, 'announcement_text_3', 'FREE SHIPPING TO BenArous, M\'Hamdia', '2025-07-27 01:32:39', '2025-07-30 23:03:03'),
(7, 'what_makes_special_title', 'What Makes Beam Special?', '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(8, 'what_makes_special_description', 'At Beam, we believe in more than just clothing. We stand for conscious creation, timeless design, and a commitment to quality that you can feel. Every piece is thoughtfully crafted to not only elevate your style but also to endure, becoming a cherished part of your personal collection for years to come.', '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(233, 'shipping_cost', '8.000', '2025-07-27 09:56:12', '2025-07-30 23:22:13'),
(235, 'tax_rate', '0', '2025-07-27 18:44:14', '2025-07-30 23:22:13'),
(238, 'brand_logo2', 'images/logo_1753916431_688aa40f0e49b.png', '2025-07-27 19:10:28', '2025-07-30 23:00:31'),
(239, 'site_name', '', '2025-07-29 22:00:15', '2025-07-29 22:00:15'),
(240, 'site_description', '', '2025-07-29 22:00:15', '2025-07-29 22:00:15'),
(241, 'contact_email', 'test@email.com', '2025-07-29 22:00:15', '2025-07-30 23:08:41'),
(242, 'contact_phone', '+21627324103', '2025-07-29 22:00:15', '2025-07-30 01:25:25'),
(243, 'address', '16 Rue Cyrus le Grand', '2025-07-29 22:00:15', '2025-07-30 01:25:25'),
(246, 'currency', 'TND', '2025-07-29 22:00:15', '2025-07-29 22:00:15'),
(247, 'maintenance_mode', '0', '2025-07-29 22:00:15', '2025-07-30 23:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `social_media`
--

CREATE TABLE `social_media` (
  `id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `icon_svg` text DEFAULT NULL,
  `link_url` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `social_media`
--

INSERT INTO `social_media` (`id`, `platform`, `icon_svg`, `link_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Instagram', '<path d=\"M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z\"/>', 'https://www.facebook.com', 0, 1, '2025-07-27 01:32:39', '2025-07-29 22:27:18'),
(2, 'Pinterest', '<path d=\"M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z\"/>', 'https://pinterest.com', 2, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(3, 'YouTube', '<path d=\"M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z\"/>', 'https://youtube.com', 3, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(4, 'TikTok', '<path d=\"M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 2.31-3.59 3.99-6.12 4.51-2.56.52-5.18-.06-7.44-1.61-2.26-1.55-3.96-3.87-4.47-6.46-.51-2.59.06-5.21 1.61-7.47 1.55-2.26 3.87-3.96 6.46-4.47 2.59-.51 5.21.06 7.47 1.61.57.39 1.1.82 1.62 1.26.01-2.92-.01-5.84.02-8.75z\"/>', 'https://tiktok.com', 4, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39'),
(5, 'LinkedIn', '<path d=\"M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z\"/>', 'https://linkedin.com', 5, 1, '2025-07-27 01:32:39', '2025-07-27 01:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `test_order_items`
--

CREATE TABLE `test_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_section`
--

CREATE TABLE `video_section` (
  `id` int(11) NOT NULL,
  `video_path` varchar(255) NOT NULL,
  `slug_text` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_section`
--

INSERT INTO `video_section` (`id`, `video_path`, `slug_text`, `button_text`, `button_link`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'video/COLD CULTURE - THINKING WORLDWIDE.mp4', 'Beam X WeULT', 'JOIN COMMUNITY', '#', 'Join our exclusive community and stay connected with the latest updates, behind-the-scenes content, and exclusive offers from Beam.', 1, '2025-07-27 01:32:39', '2025-07-30 23:39:49'),
(30, 'video/video_1753836431_68896b8fad461.mp4', 'azeaz', 'zaeaz', 'https://www.youtube.com/results?search_query=react+aveyro', 'azeaze', 0, '2025-07-30 00:47:17', '2025-07-30 23:39:48');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `session_id`, `product_id`, `created_at`) VALUES
(2, 'dhmrknssc0a7ekm1cju8k9s1eg', 8, '2025-07-27 07:15:23');

-- --------------------------------------------------------

--
-- Structure for view `product_images_ordered`
--
DROP TABLE IF EXISTS `product_images_ordered`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_images_ordered`  AS SELECT `pi`.`id` AS `id`, `pi`.`product_id` AS `product_id`, `pi`.`image_path` AS `image_path`, `pi`.`is_primary` AS `is_primary`, `pi`.`sort_order` AS `sort_order`, `pi`.`image_order` AS `image_order`, `pi`.`created_at` AS `created_at`, `p`.`name` AS `product_name` FROM (`product_images` `pi` left join `products` `p` on(`pi`.`product_id` = `p`.`id`)) ORDER BY `pi`.`product_id` ASC, `pi`.`image_order` ASC ;

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
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`session_id`,`product_id`,`size`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `footer_links`
--
ALTER TABLE `footer_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `footer_sections`
--
ALTER TABLE `footer_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_shipping_city` (`shipping_city`),
  ADD KEY `idx_total` (`total`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `fk_orders_promo_code` (`promo_code_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `collection_id` (`collection_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_order` (`product_id`,`image_order`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_size` (`product_id`,`size`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `promo_code_usage`
--
ALTER TABLE `promo_code_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promo_code_id` (`promo_code_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `social_media`
--
ALTER TABLE `social_media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_order_items`
--
ALTER TABLE `test_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `video_section`
--
ALTER TABLE `video_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist_item` (`session_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aboutus`
--
ALTER TABLE `aboutus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `footer_links`
--
ALTER TABLE `footer_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

--
-- AUTO_INCREMENT for table `footer_sections`
--
ALTER TABLE `footer_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `promo_code_usage`
--
ALTER TABLE `promo_code_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=641;

--
-- AUTO_INCREMENT for table `social_media`
--
ALTER TABLE `social_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `test_order_items`
--
ALTER TABLE `test_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video_section`
--
ALTER TABLE `video_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `footer_links`
--
ALTER TABLE `footer_links`
  ADD CONSTRAINT `footer_links_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `footer_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promo_code_usage`
--
ALTER TABLE `promo_code_usage`
  ADD CONSTRAINT `promo_code_usage_ibfk_1` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_order_items`
--
ALTER TABLE `test_order_items`
  ADD CONSTRAINT `test_order_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
