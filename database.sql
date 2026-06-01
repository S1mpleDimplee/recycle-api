-- ============================================================
-- jaylanovanderv_recycle  –  full schema (fresh install)
-- Run this on an EMPTY database. Drops nothing, safe to re-run.
-- MariaDB 10.6+
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ── credit ───────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `credit` (
  `id`     int(11) NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── user ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `user` (
  `id`                int(12)      NOT NULL AUTO_INCREMENT,
  `credit_id`         int(12)      DEFAULT NULL,
  `role`              varchar(255) NOT NULL DEFAULT 'user',
  `name`              varchar(255) NOT NULL DEFAULT '',
  `username`          varchar(255) NOT NULL DEFAULT '',
  `surname`           varchar(255) NOT NULL DEFAULT '',
  `email`             varchar(255) NOT NULL,
  `password`          varchar(255) NOT NULL,
  `adress`            varchar(255) NOT NULL DEFAULT '',
  `phonenumber`       varchar(25)  NOT NULL DEFAULT '',
  `email_verified`    tinyint(1)   NOT NULL DEFAULT 0,
  `verification_code` varchar(64)  DEFAULT NULL,
  `created_at`        datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── p (products / listings) ──────────────────────────────────

CREATE TABLE IF NOT EXISTS `p` (
  `id`                   int(12)      NOT NULL AUTO_INCREMENT,
  `user_id`              int(12)      NOT NULL,
  `product_name`         varchar(255) NOT NULL,
  `product_price`        int(11)      NOT NULL DEFAULT 0,
  `product_img`          varchar(255) NOT NULL DEFAULT '',
  `product_description`  varchar(255) NOT NULL DEFAULT '',
  `product_availability` varchar(20)  NOT NULL DEFAULT 'available',
  PRIMARY KEY (`id`),
  KEY `idx_p_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── bid ──────────────────────────────────────────────────────
-- status: pending | accepted | rejected | cancelled

CREATE TABLE IF NOT EXISTS `bid` (
  `id`         int(12)     NOT NULL AUTO_INCREMENT,
  `product_id` int(12)     NOT NULL,
  `bidder_id`  int(12)     NOT NULL,
  `amount`     int(11)     NOT NULL,
  `status`     varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bid_product` (`product_id`),
  KEY `idx_bid_bidder`  (`bidder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ── purchase ─────────────────────────────────────────────────
-- Created automatically when a seller accepts a bid.
-- Records the completed transaction for both buyer and seller history.

CREATE TABLE IF NOT EXISTS `purchase` (
  `id`          int(12)  NOT NULL AUTO_INCREMENT,
  `bid_id`      int(12)  NOT NULL,
  `product_id`  int(12)  NOT NULL,
  `buyer_id`    int(12)  NOT NULL,
  `seller_id`   int(12)  NOT NULL,
  `amount_paid` int(11)  NOT NULL,
  `created_at`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_purchase_buyer`  (`buyer_id`),
  KEY `idx_purchase_seller` (`seller_id`),
  KEY `idx_purchase_bid`    (`bid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

COMMIT;
