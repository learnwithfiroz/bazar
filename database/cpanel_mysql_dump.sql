-- ==============================================================================
-- MySQL Database Dump for cPanel Production Deployment
-- Principal & Daily Expense SaaS Management System
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- High Performance Composite Indexes Included for 10,000+ Users
-- ==============================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+06:00";

-- ------------------------------------------------------------------------------
-- 1. Users Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('principal','pa','messenger','family') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'messenger',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_number_unique` (`phone_number`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `idx_users_role_active` (`role`, `is_active`),
  KEY `idx_users_phone_active` (`phone_number`, `is_active`),
  KEY `idx_users_email_active` (`email`, `is_active`),
  KEY `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Users (Password: password)
INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'প্রিন্সিপাল (Super Admin)', 'admin@expense.com', '01713144920', '$2y$10$xBknODXziW2Sgn0SN7x4neG/xW4qytEfr2/34lSjcW6jsBoHYvvj6', 'principal', NULL, 1, NOW(), NOW()),
(2, 'প্রিন্সিপালের পিএ (Manager)', 'pa@expense.com', '01711000002', '$2y$10$xBknODXziW2Sgn0SN7x4neG/xW4qytEfr2/34lSjcW6jsBoHYvvj6', 'pa', NULL, 1, NOW(), NOW()),
(3, 'বাজার মেসেঞ্জার (Staff)', 'messenger@expense.com', '01711000003', '$2y$10$xBknODXziW2Sgn0SN7x4neG/xW4qytEfr2/34lSjcW6jsBoHYvvj6', 'messenger', NULL, 1, NOW(), NOW()),
(4, 'পারিবারিক সদস্য (Viewer)', 'family@expense.com', '01711000005', '$2y$10$xBknODXziW2Sgn0SN7x4neG/xW4qytEfr2/34lSjcW6jsBoHYvvj6', 'family', NULL, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `password`=VALUES(`password`), `role`=VALUES(`role`), `is_active`=VALUES(`is_active`);

-- ------------------------------------------------------------------------------
-- 2. Wallets Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `current_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `low_balance_alert_limit` decimal(12,2) NOT NULL DEFAULT 500.00,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BDT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_user_id_unique` (`user_id`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wallets` (`id`, `user_id`, `current_balance`, `low_balance_alert_limit`, `currency`, `created_at`, `updated_at`) VALUES
(1, 3, 0.00, 500.00, 'BDT', NOW(), NOW())
ON DUPLICATE KEY UPDATE `current_balance`=VALUES(`current_balance`);

-- ------------------------------------------------------------------------------
-- 3. Wallet Transactions Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `performed_by` bigint(20) UNSIGNED NOT NULL,
  `type` enum('CREDIT','DEBIT','ADJUSTMENT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `balance_before` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(12,2) NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_wallet_id_foreign` (`wallet_id`),
  KEY `wallet_transactions_performed_by_foreign` (`performed_by`),
  KEY `idx_wallet_tx_wallet_created` (`wallet_id`, `created_at`),
  KEY `idx_wallet_tx_perf_created` (`performed_by`, `created_at`),
  CONSTRAINT `wallet_transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_transactions_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. Expenses Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `expense_date` date NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'দৈনিক বাজার',
  `memo_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('SUBMITTED','REVIEWED','FLAGGED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SUBMITTED',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `idx_expenses_date_status` (`expense_date`, `status`),
  KEY `idx_expenses_creator_date` (`created_by`, `expense_date`),
  KEY `idx_expenses_vendor` (`vendor_name`),
  KEY `idx_expenses_memo` (`memo_no`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. Expense Items Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expense_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'কাঁচাবাজার',
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'কেজি',
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_items_expense_id_foreign` (`expense_id`),
  KEY `idx_expense_items_category` (`category`),
  KEY `idx_expense_items_name` (`item_name`),
  KEY `idx_expense_items_exp_cat` (`expense_id`, `category`),
  CONSTRAINT `expense_items_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. Slips Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `slips` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slips_expense_id_foreign` (`expense_id`),
  CONSTRAINT `slips_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. Expense Comments Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expense_comments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_comments_expense_id_foreign` (`expense_id`),
  KEY `expense_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `expense_comments_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 8. Fund Requests Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fund_requests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `action_by` bigint(20) UNSIGNED DEFAULT NULL,
  `action_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fund_requests_wallet_id_foreign` (`wallet_id`),
  KEY `fund_requests_requested_by_foreign` (`requested_by`),
  KEY `idx_fund_requests_status` (`status`),
  KEY `idx_fund_requests_wallet_status` (`wallet_id`, `status`),
  CONSTRAINT `fund_requests_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fund_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 9. Bazaar Demands Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bazaar_demands` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'বাজারের শপিং লিস্ট',
  `target_date` date NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','COMPLETED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bazaar_demands_created_by_foreign` (`created_by`),
  KEY `bazaar_demands_assigned_to_foreign` (`assigned_to`),
  KEY `idx_bazaar_demands_target_status` (`target_date`, `status`),
  CONSTRAINT `bazaar_demands_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bazaar_demands_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. Bazaar Demand Items Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bazaar_demand_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bazaar_demand_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'কাঁচাবাজার',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'কেজি',
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `is_purchased` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bazaar_demand_items_bazaar_demand_id_foreign` (`bazaar_demand_id`),
  KEY `idx_demand_items_demand_purchased` (`bazaar_demand_id`, `is_purchased`),
  CONSTRAINT `bazaar_demand_items_bazaar_demand_id_foreign` FOREIGN KEY (`bazaar_demand_id`) REFERENCES `bazaar_demands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 11. System Settings Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('monthly_budget_limit', '50000', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

-- ------------------------------------------------------------------------------
-- 12. Sessions Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 13. Cache & Locks Table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
