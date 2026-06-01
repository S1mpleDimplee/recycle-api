-- Migration: add product_images table for up to 4 images per product
-- Run this once against your database (safe to re-run, uses IF NOT EXISTS)

CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         int(12)   NOT NULL AUTO_INCREMENT,
  `product_id` int(12)   NOT NULL,
  `image_data` longblob  NOT NULL,
  `position`   tinyint   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_position` (`product_id`, `position`),
  KEY `idx_pi_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Migrate existing single images from products table into product_images (position 1)
INSERT IGNORE INTO `product_images` (`product_id`, `image_data`, `position`)
SELECT `id`, `product_img`, 1
FROM `products`
WHERE `product_img` IS NOT NULL AND LENGTH(`product_img`) > 0;
