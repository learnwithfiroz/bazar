<?php
/**
 * 1-Click Standalone Database & System Auto-Updater for cPanel
 * Visit: https://bazar.firoz-ahmed.com/auto_update_db.php
 */

header('Content-Type: text/html; charset=utf-8');

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

$results = [];

try {
    // 1. Ensure Storage Directories Exist
    $dirs = [
        storage_path('app/public/avatars'),
        storage_path('app/public/slips'),
        public_path('storage/avatars'),
        public_path('storage/slips'),
        storage_path('framework/cache'),
        storage_path('framework/sessions'),
        storage_path('framework/views'),
    ];
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            @mkdir($dir, 0775, true);
        }
    }
    $results[] = "📁 স্টোরেজ ও পিকচার ফোল্ডারসমূহ নিশ্চিত করা হয়েছে।";

    // 2. Fix wallet_transactions Table Schema
    DB::statement("ALTER TABLE `wallet_transactions` MODIFY COLUMN `type` ENUM('CREDIT','DEBIT','ADJUSTMENT') NOT NULL");
    
    if (!Schema::hasColumn('wallet_transactions', 'balance_before')) {
        DB::statement("ALTER TABLE `wallet_transactions` ADD COLUMN `balance_before` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `amount`");
        $results[] = "✓ `wallet_transactions` টেবিলে `balance_before` কলাম যোগ করা হয়েছে।";
    } else {
        $results[] = "✓ `wallet_transactions` টেবিল সম্পূর্ণ আপডেট রয়েছে।";
    }

    // 3. Fix slips Table Schema
    if (!Schema::hasColumn('slips', 'original_name')) {
        DB::statement("ALTER TABLE `slips` ADD COLUMN `original_name` VARCHAR(255) NULL AFTER `image_path`");
        $results[] = "✓ `slips` টেবিলে `original_name` কলাম যোগ করা হয়েছে।";
    }
    if (!Schema::hasColumn('slips', 'caption')) {
        DB::statement("ALTER TABLE `slips` ADD COLUMN `caption` VARCHAR(255) NULL AFTER `original_name`");
        $results[] = "✓ `slips` টেবিলে `caption` কলাম যোগ করা হয়েছে।";
    }
    if (!Schema::hasColumn('slips', 'mime_type')) {
        DB::statement("ALTER TABLE `slips` ADD COLUMN `mime_type` VARCHAR(50) NULL AFTER `file_size`");
        $results[] = "✓ `slips` টেবিলে `mime_type` কলাম যোগ করা হয়েছে।";
    }

    // 4. Fix expenses Table Schema
    if (!Schema::hasColumn('expenses', 'vendor_name')) {
        DB::statement("ALTER TABLE `expenses` ADD COLUMN `vendor_name` VARCHAR(150) NULL AFTER `memo_no`");
        $results[] = "✓ `expenses` টেবিলে `vendor_name` কলাম যোগ করা হয়েছে।";
    }

    // 5. Fix users Table Schema & Sync Passwords
    if (!Schema::hasColumn('users', 'last_login_at')) {
        DB::statement("ALTER TABLE `users` ADD COLUMN `last_login_at` TIMESTAMP NULL DEFAULT NULL AFTER `remember_token`");
    }
    if (!Schema::hasColumn('users', 'last_login_ip')) {
        DB::statement("ALTER TABLE `users` ADD COLUMN `last_login_ip` VARCHAR(45) NULL DEFAULT NULL AFTER `last_login_at`");
    }

    $principalUser = \App\Models\User::where('phone_number', '01713144920')->orWhere('role', 'principal')->first();
    if ($principalUser) {
        $principalUser->update([
            'phone_number' => '01713144920',
            'password'     => \Illuminate\Support\Facades\Hash::make('01713144920'),
            'is_active'    => true,
        ]);
        $results[] = "👑 সুপার এডমিন (প্রিন্সিপাল) লগইন আপডেট: ফোন = 01713144920, পাসওয়ার্ড = 01713144920";
    }

    // 6. Ensure Sessions & Cache Tables Exist
    DB::statement("
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
    ");

    DB::statement("
        CREATE TABLE IF NOT EXISTS `cache` (
          `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
          `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
          `expiration` int(11) NOT NULL,
          PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $results[] = "✓ সেশন ও ক্যাশ টেবিল নিশ্চিত করা হয়েছে।";

    // 7. Clear application caches
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    $results[] = "⚡ সিস্টেম ক্যাশ ও ভিউ ক্যাশ পরিষ্কার করা হয়েছে।";

    $status = 'success';
} catch (\Throwable $e) {
    $status = 'error';
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ডাটাবেজ অটো-আপডেট সম্পন্ন | Daily Expense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">
    <style>body { font-family: 'SolaimanLipi', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-slate-800/90 border border-slate-700 p-6 sm:p-8 rounded-3xl shadow-2xl space-y-6">
        
        <div class="text-center space-y-2">
            @if($status === 'success')
            <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-2xl flex items-center justify-center text-3xl mx-auto shadow-inner">
                🚀
            </div>
            <h1 class="text-2xl font-black text-white">ডাটাবেজ অটো-আপডেট সফল হয়েছে!</h1>
            <p class="text-xs text-slate-400">সকল টেবিল, কলাম ও সিস্টেম কনফিগারেশন রিয়েল-টাইমে আপডেট করা হয়েছে।</p>
            @else
            <div class="w-16 h-16 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-2xl flex items-center justify-center text-3xl mx-auto shadow-inner">
                ⚠️
            </div>
            <h1 class="text-2xl font-black text-rose-400">ডাটাবেজ আপডেটে সমস্যা হয়েছে</h1>
            <p class="text-xs text-slate-400"><?= htmlspecialchars($errorMessage ?? '') ?></p>
            @endif
        </div>

        @if($status === 'success')
        <div class="bg-slate-900/80 rounded-2xl p-4 border border-slate-700/60 space-y-2 text-xs text-emerald-300 font-semibold">
            <?php foreach ($results as $res): ?>
                <div class="flex items-center gap-2">
                    <span><?= htmlspecialchars($res) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        @endif

        <div class="pt-2">
            <a href="/" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black rounded-2xl text-center block shadow-lg shadow-emerald-600/30 transition">
                ড্যাশবোর্ডে প্রবেশ করুন ➔
            </a>
        </div>
    </div>
</body>
</html>
