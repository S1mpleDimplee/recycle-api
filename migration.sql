-- Run this in phpMyAdmin on jaylanovanderv_recycle

-- 1. Add missing columns to user table
ALTER TABLE `user`
  ADD COLUMN `email_verified`    tinyint(1)   NOT NULL DEFAULT 0   AFTER `password`,
  ADD COLUMN `verification_code` varchar(255)          DEFAULT NULL AFTER `email_verified`,
  ADD COLUMN `created_at`        datetime     NOT NULL DEFAULT current_timestamp() AFTER `verification_code`,
  MODIFY     `phonenumber`       varchar(25)  NOT NULL DEFAULT '';

-- 2. Rename tables to plural
RENAME TABLE `user`     TO `users`;
RENAME TABLE `p`        TO `products`;
RENAME TABLE `bid`      TO `bids`;
RENAME TABLE `credit`   TO `credits`;
RENAME TABLE `purchase` TO `purchases`;

-- 3. Add columns for password reset, 2FA, and profile image
ALTER TABLE `users`
  ADD COLUMN `reset_token`         varchar(255) DEFAULT NULL,
  ADD COLUMN `reset_token_expires` datetime     DEFAULT NULL,
  ADD COLUMN `two_fa_code`         varchar(6)   DEFAULT NULL,
  ADD COLUMN `two_fa_expires`      datetime     DEFAULT NULL,
  ADD COLUMN `profile_img`         varchar(255) DEFAULT NULL;

-- 4. Add bid deadline to products
ALTER TABLE `products`
  ADD COLUMN `bid_deadline`  datetime     DEFAULT NULL,
  ADD COLUMN `listing_type`  varchar(10)  NOT NULL DEFAULT 'bid';

-- 5. Allow direct purchases without a bid (bid_id = NULL)
ALTER TABLE `purchases`
  MODIFY `bid_id` int(12) DEFAULT NULL;
