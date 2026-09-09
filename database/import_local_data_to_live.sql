-- MySQL Export of Local SQLite Data
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES (1, 'Brig Gen Akhter Shahid, SUP (BAR), ndc, psc, G+, MPhil (LPR) PRINCIPAL', 'principal@expense.com', '01713144920', '$2y$12$9XXbPltcmVW6PRVvj6XCg.GJwZ3KrkjOpHbOcajZi5.UKlERFASZ6', 'principal', 'avatars/4xt8xkRcFZ9SfGsMCa9AHCSMeyOBgXApb7cAQgXo.png', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE `name`='Brig Gen Akhter Shahid, SUP (BAR), ndc, psc, G+, MPhil (LPR) PRINCIPAL', `phone_number`='01713144920', `role`='principal';
INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES (2, 'Md Aktar Hossain', 'pa@expense.com', '01714276498', '$2y$12$prOtJ8Xs0kVd8rScPM5QlODbx7/Z6thSD84jXmyHgt0LAWgOjPR32', 'pa', 'avatars/iKiGZ4kdacK2QUGotGtwo0MNYvk5w6kDadAEDAZ2.jpg', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE `name`='Md Aktar Hossain', `phone_number`='01714276498', `role`='pa';
INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES (4, 'পারিবারিক সদস্য (Viewer)', 'family@expense.com', '01711000005', '$2y$12$kQfRfhsRt0D1KIYGEFz9We3BbpUUjxnbIpkIAi3fmPo6B33bIA9mW', 'family', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE `name`='পারিবারিক সদস্য (Viewer)', `phone_number`='01711000005', `role`='family';
INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES (6, 'Md Tareqzzaman', NULL, '01710694170', '$2y$12$KzFeI69CeFUIDkIAmny8Y.HkcPKHKhk.CRJkD/xGo7qd5z3NoWx1a', 'messenger', 'avatars/qwXgWjclQgKow7dfi8YjB1G5KQnfFBOSmFPweKpx.jpg', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE `name`='Md Tareqzzaman', `phone_number`='01710694170', `role`='messenger';
INSERT INTO `wallets` (`id`, `user_id`, `current_balance`, `low_balance_alert_limit`, `currency`, `created_at`, `updated_at`) VALUES (3, 6, 0.00, 500.00, 'BDT', NOW(), NOW()) ON DUPLICATE KEY UPDATE `current_balance`=0.00;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
