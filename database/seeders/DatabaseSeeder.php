<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the initial users with zero dummy data.
     */
    public function run(): void
    {
        // 1. Super Admin (Principal)
        $principal = User::create([
            'name'         => 'প্রিন্সিপাল (Super Admin)',
            'email'        => 'admin@expense.com',
            'phone_number' => '01711000001',
            'password'     => Hash::make('password'),
            'role'         => 'principal',
            'is_active'    => true,
        ]);

        // 2. PA (Manager)
        $pa = User::create([
            'name'         => 'প্রিন্সিপালের পিএ (Manager)',
            'email'        => 'pa@expense.com',
            'phone_number' => '01711000002',
            'password'     => Hash::make('password'),
            'role'         => 'pa',
            'is_active'    => true,
        ]);

        // 3. Messenger (Staff)
        $messenger = User::create([
            'name'         => 'বাজার মেসেঞ্জার (Staff)',
            'email'        => 'messenger@expense.com',
            'phone_number' => '01711000003',
            'password'     => Hash::make('password'),
            'role'         => 'messenger',
            'is_active'    => true,
        ]);

        // Initialize clean wallet with 0 balance for Messenger
        Wallet::create([
            'user_id'                 => $messenger->id,
            'current_balance'         => 0.00,
            'low_balance_alert_limit' => 500.00,
            'currency'                => 'BDT',
        ]);

        // 4. Family Member (Viewer)
        $family = User::create([
            'name'         => 'পারিবারিক সদস্য (Viewer)',
            'email'        => 'family@expense.com',
            'phone_number' => '01711000005',
            'password'     => Hash::make('password'),
            'role'         => 'family',
            'is_active'    => true,
        ]);
    }
}
