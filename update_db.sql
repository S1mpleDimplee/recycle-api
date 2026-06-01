-- ============================================================
-- Database update for jaylanovanderv_recycle
-- Run this once to bring the schema in sync with the API
-- MariaDB 10.6+ (supports ADD COLUMN IF NOT EXISTS)
-- ============================================================

USE `jaylanovanderv_recycle`;

-- ── user table ───────────────────────────────────────────────

-- Make credit_id nullable so new users can register without a credit record
ALTER TABLE `user`
  MODIFY `credit_id` int(12) DEFAULT NULL;

-- email_verified: set to 1 after the user clicks the verification link
ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `email_verified` tinyint(1) NOT NULL DEFAULT 0;

-- verification_code: temporary code sent by email
ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `verification_code` varchar(64) DEFAULT NULL;

-- created_at: set automatically on insert
ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ── p (products) table ───────────────────────────────────────

-- Index so lookups by user are fast
ALTER TABLE `p`
  ADD INDEX IF NOT EXISTS `idx_p_user_id` (`user_id`);

-- ── credit table ─────────────────────────────────────────────

-- amount defaults to 0 for new records
ALTER TABLE `credit`
  MODIFY `amount` int(11) NOT NULL DEFAULT 0;
