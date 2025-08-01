-- Create promo_codes table
CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `description` text,
  `type` enum('percentage', 'fixed_amount', 'free_shipping') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,3) NOT NULL,
  `min_order_amount` decimal(10,3) DEFAULT 0.000,
  `max_discount` decimal(10,3) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT NULL,
  `applies_to` enum('all', 'categories', 'products') DEFAULT 'all',
  `category_ids` text DEFAULT NULL,
  `product_ids` text DEFAULT NULL,
  `excluded_categories` text DEFAULT NULL,
  `excluded_products` text DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_first_time_only` tinyint(1) DEFAULT 0,
  `is_single_use` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create promo_code_usage table to track usage per user
CREATE TABLE IF NOT EXISTS `promo_code_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_code_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,3) NOT NULL,
  `used_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_promo_code_id` (`promo_code_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample promo codes
INSERT INTO `promo_codes` (`code`, `name`, `description`, `type`, `value`, `min_order_amount`, `max_discount`, `usage_limit`, `start_date`, `end_date`, `is_active`) VALUES
('WELCOME10', 'Welcome Discount', 'Get 10% off your first order', 'percentage', 10.000, 50.000, 25.000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1),
('SAVE20', 'Summer Sale', 'Save 20% on all items', 'percentage', 20.000, 100.000, 50.000, 50, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1),
('FREESHIP', 'Free Shipping', 'Free shipping on orders over $50', 'free_shipping', 0.000, 50.000, NULL, 200, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), 1),
('FLAT15', 'Flat Discount', 'Get $15 off your order', 'fixed_amount', 15.000, 75.000, 15.000, 75, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), 1),
('NEWCUSTOMER', 'New Customer Special', '25% off for new customers', 'percentage', 25.000, 25.000, 30.000, 1000, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY), 1); 