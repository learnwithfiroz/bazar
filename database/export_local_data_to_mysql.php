<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\Slip;
use App\Models\ExpenseComment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\FundRequest;
use App\Models\BazaarDemand;
use App\Models\BazaarDemandItem;
use App\Models\SystemSetting;

echo "=== Local SQLite Data Count ===\n";
echo "Users: " . User::count() . "\n";
echo "Expenses: " . Expense::count() . "\n";
echo "Expense Items: " . ExpenseItem::count() . "\n";
echo "Slips: " . Slip::count() . "\n";
echo "Comments: " . ExpenseComment::count() . "\n";
echo "Wallets: " . Wallet::count() . "\n";
echo "Transactions: " . WalletTransaction::count() . "\n";
echo "Fund Requests: " . FundRequest::count() . "\n";
echo "Bazaar Demands: " . BazaarDemand::count() . "\n";
echo "Demand Items: " . BazaarDemandItem::count() . "\n";

// Generate MySQL Dump
$sql = "-- MySQL Export of Local SQLite Data\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\nSTART TRANSACTION;\n\n";

// 1. Users
$users = User::all();
foreach ($users as $u) {
    $name = addslashes($u->name);
    $email = $u->email ? "'" . addslashes($u->email) . "'" : "NULL";
    $phone = addslashes($u->phone_number);
    $pass = addslashes($u->password);
    $role = addslashes($u->role);
    $avatar = $u->avatar ? "'" . addslashes($u->avatar) . "'" : "NULL";
    $active = $u->is_active ? 1 : 0;
    $sql .= "INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES ({$u->id}, '{$name}', {$email}, '{$phone}', '{$pass}', '{$role}', {$avatar}, {$active}, NOW(), NOW()) ON DUPLICATE KEY UPDATE `name`='{$name}', `phone_number`='{$phone}', `role`='{$role}';\n";
}

// 2. Wallets
$wallets = Wallet::all();
foreach ($wallets as $w) {
    $sql .= "INSERT INTO `wallets` (`id`, `user_id`, `current_balance`, `low_balance_alert_limit`, `currency`, `created_at`, `updated_at`) VALUES ({$w->id}, {$w->user_id}, {$w->current_balance}, {$w->low_balance_alert_limit}, '{$w->currency}', NOW(), NOW()) ON DUPLICATE KEY UPDATE `current_balance`={$w->current_balance};\n";
}

// 3. Wallet Transactions
$txs = WalletTransaction::all();
foreach ($txs as $t) {
    $refType = $t->reference_type ? "'" . addslashes($t->reference_type) . "'" : "NULL";
    $refId = $t->reference_id ?: "NULL";
    $notes = $t->notes ? "'" . addslashes($t->notes) . "'" : "NULL";
    $sql .= "INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `type`, `amount`, `balance_after`, `performed_by`, `reference_type`, `reference_id`, `notes`, `created_at`, `updated_at`) VALUES ({$t->id}, {$t->wallet_id}, '{$t->type}', {$t->amount}, {$t->balance_after}, {$t->performed_by}, {$refType}, {$refId}, {$notes}, '{$t->created_at}', '{$t->updated_at}') ON DUPLICATE KEY UPDATE `amount`={$t->amount};\n";
}

// 4. Expenses
$expenses = Expense::all();
foreach ($expenses as $e) {
    $title = addslashes($e->title);
    $memo = $e->memo_no ? "'" . addslashes($e->memo_no) . "'" : "NULL";
    $vendor = $e->vendor_name ? "'" . addslashes($e->vendor_name) . "'" : "NULL";
    $notes = $e->notes ? "'" . addslashes($e->notes) . "'" : "NULL";
    $sql .= "INSERT INTO `expenses` (`id`, `created_by`, `expense_date`, `title`, `memo_no`, `vendor_name`, `total_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES ({$e->id}, {$e->created_by}, '{$e->expense_date}', '{$title}', {$memo}, {$vendor}, {$e->total_amount}, '{$e->status}', {$notes}, '{$e->created_at}', '{$e->updated_at}') ON DUPLICATE KEY UPDATE `total_amount`={$e->total_amount};\n";
}

// 5. Expense Items
$items = ExpenseItem::all();
foreach ($items as $i) {
    $itemName = addslashes($i->item_name);
    $category = addslashes($i->category ?: 'অন্যান্য');
    $unit = addslashes($i->unit);
    $sql .= "INSERT INTO `expense_items` (`id`, `expense_id`, `item_name`, `category`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ({$i->id}, {$i->expense_id}, '{$itemName}', '{$category}', {$i->quantity}, '{$unit}', {$i->unit_price}, {$i->total_price}, '{$i->created_at}', '{$i->updated_at}') ON DUPLICATE KEY UPDATE `total_price`={$i->total_price};\n";
}

// 6. Slips
$slips = Slip::all();
foreach ($slips as $s) {
    $caption = $s->caption ? "'" . addslashes($s->caption) . "'" : "NULL";
    $sql .= "INSERT INTO `slips` (`id`, `expense_id`, `image_path`, `caption`, `file_size`, `mime_type`, `created_at`, `updated_at`) VALUES ({$s->id}, {$s->expense_id}, '{$s->image_path}', {$caption}, {$s->file_size}, '{$s->mime_type}', '{$s->created_at}', '{$s->updated_at}') ON DUPLICATE KEY UPDATE `image_path`='{$s->image_path}';\n";
}

// 7. Bazaar Demands
$demands = BazaarDemand::all();
foreach ($demands as $d) {
    $title = addslashes($d->title);
    $assignedTo = $d->assigned_to ?: "NULL";
    $notes = $d->notes ? "'" . addslashes($d->notes) . "'" : "NULL";
    $sql .= "INSERT INTO `bazaar_demands` (`id`, `created_by`, `assigned_to`, `title`, `target_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES ({$d->id}, {$d->created_by}, {$assignedTo}, '{$title}', '{$d->target_date}', '{$d->status}', {$notes}, '{$d->created_at}', '{$d->updated_at}') ON DUPLICATE KEY UPDATE `status`='{$d->status}';\n";
}

// 8. Bazaar Demand Items
$dItems = BazaarDemandItem::all();
foreach ($dItems as $di) {
    $itemName = addslashes($di->item_name);
    $cat = addslashes($di->category ?: 'কাঁচাবাজার');
    $unit = addslashes($di->unit);
    $estPrice = $di->estimated_price ?: "NULL";
    $isPurchased = $di->is_purchased ? 1 : 0;
    $notes = $di->notes ? "'" . addslashes($di->notes) . "'" : "NULL";
    $sql .= "INSERT INTO `bazaar_demand_items` (`id`, `bazaar_demand_id`, `item_name`, `category`, `quantity`, `unit`, `estimated_price`, `is_purchased`, `notes`, `created_at`, `updated_at`) VALUES ({$di->id}, {$di->bazaar_demand_id}, '{$itemName}', '{$cat}', {$di->quantity}, '{$unit}', {$estPrice}, {$isPurchased}, {$notes}, '{$di->created_at}', '{$di->updated_at}') ON DUPLICATE KEY UPDATE `is_purchased`={$isPurchased};\n";
}

$sql .= "\nSET FOREIGN_KEY_CHECKS=1;\nCOMMIT;\n";

file_put_contents(database_path('import_local_data_to_live.sql'), $sql);
echo "Generated database/import_local_data_to_live.sql successfully!\n";
