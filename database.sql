-- ============================================================
-- jaylanovanderv_recycle  –  full schema (fresh install)
-- Run this on an EMPTY database. Drops nothing, safe to re-run.
-- MariaDB 10.6+
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ── credits ──────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `credits` (
  `id`     int(11) NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── users ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `users` (
  `id`                  int(12)      NOT NULL AUTO_INCREMENT,
  `credit_id`           int(12)      DEFAULT NULL,
  `role`                varchar(255) NOT NULL DEFAULT 'user',
  `name`                varchar(255) NOT NULL DEFAULT '',
  `username`            varchar(255) NOT NULL DEFAULT '',
  `surname`             varchar(255) NOT NULL DEFAULT '',
  `email`               varchar(255) NOT NULL,
  `password`            varchar(255) NOT NULL,
  `adress`              varchar(255) NOT NULL DEFAULT '',
  `phonenumber`         varchar(25)  NOT NULL DEFAULT '',
  `profile_img`         varchar(255) DEFAULT NULL,
  `email_verified`      tinyint(1)   NOT NULL DEFAULT 0,
  `verification_code`   varchar(64)  DEFAULT NULL,
  `two_fa_code`         varchar(10)  DEFAULT NULL,
  `two_fa_expires`      datetime     DEFAULT NULL,
  `reset_token`         varchar(128) DEFAULT NULL,
  `reset_token_expires` datetime     DEFAULT NULL,
  `created_at`          datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── products ─────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `products` (
  `id`                   int(12)      NOT NULL AUTO_INCREMENT,
  `user_id`              int(12)      NOT NULL,
  `product_name`         varchar(255) NOT NULL,
  `product_price`        int(11)      NOT NULL DEFAULT 0,
  `product_img`          varchar(255) NOT NULL DEFAULT '',
  `product_description`  varchar(255) NOT NULL DEFAULT '',
  `product_availability` varchar(20)  NOT NULL DEFAULT 'available',
  `listing_type`         varchar(10)  NOT NULL DEFAULT 'bid',
  `bid_deadline`         datetime     DEFAULT NULL,
  `created_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_products_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── bids ─────────────────────────────────────────────────────
-- status: pending | accepted | rejected | cancelled

CREATE TABLE IF NOT EXISTS `bids` (
  `id`         int(12)     NOT NULL AUTO_INCREMENT,
  `product_id` int(12)     NOT NULL,
  `bidder_id`  int(12)     NOT NULL,
  `amount`     int(11)     NOT NULL,
  `status`     varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bids_product` (`product_id`),
  KEY `idx_bids_bidder`  (`bidder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── purchases ────────────────────────────────────────────────
-- Created automatically when a seller accepts a bid.

CREATE TABLE IF NOT EXISTS `purchases` (
  `id`          int(12)  NOT NULL AUTO_INCREMENT,
  `bid_id`      int(12)  NOT NULL,
  `product_id`  int(12)  NOT NULL,
  `buyer_id`    int(12)  NOT NULL,
  `seller_id`   int(12)  NOT NULL,
  `amount_paid` int(11)  NOT NULL,
  `created_at`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_purchases_buyer`  (`buyer_id`),
  KEY `idx_purchases_seller` (`seller_id`),
  KEY `idx_purchases_bid`    (`bid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

COMMIT;
