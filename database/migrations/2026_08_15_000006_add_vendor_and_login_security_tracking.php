<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add vendor_name to expenses table
        if (!Schema::hasColumn('expenses', 'vendor_name')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('vendor_name', 150)->nullable()->after('memo_no');
            });
        }

        // Add last_login_at and last_login_ip to users table
        if (!Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expenses', 'vendor_name')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('vendor_name');
            });
        }

        if (Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['last_login_at', 'last_login_ip']);
            });
        }
    }
};
