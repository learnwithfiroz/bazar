-- Quick SQL Fix for cPanel phpMyAdmin
-- Run this in phpMyAdmin SQL tab to guarantee 100% table schema compatibility

ALTER TABLE `wallet_transactions` 
MODIFY COLUMN `type` ENUM('CREDIT','DEBIT','ADJUSTMENT') NOT NULL,
ADD COLUMN IF NOT EXISTS `balance_before` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `amount`;

ALTER TABLE `slips`
ADD COLUMN IF NOT EXISTS `original_name` VARCHAR(255) NULL AFTER `image_path`,
ADD COLUMN IF NOT EXISTS `caption` VARCHAR(255) NULL AFTER `original_name`,
ADD COLUMN IF NOT EXISTS `mime_type` VARCHAR(50) NULL AFTER `file_size`;
