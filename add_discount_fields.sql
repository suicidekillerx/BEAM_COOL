-- Add discount and promo code fields to orders table
ALTER TABLE `orders` 
ADD COLUMN `discount` decimal(10,3) DEFAULT 0.000 AFTER `shipping_cost`,
ADD COLUMN `promo_code_id` int(11) DEFAULT NULL AFTER `discount`,
ADD COLUMN `promo_code` varchar(50) DEFAULT NULL AFTER `promo_code_id`;

-- Add foreign key constraint for promo_code_id
ALTER TABLE `orders` 
ADD CONSTRAINT `fk_orders_promo_code` 
FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE; 