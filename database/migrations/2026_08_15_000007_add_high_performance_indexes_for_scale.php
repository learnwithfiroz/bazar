<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite and high-performance indexes for 10,000+ users & 1,000,000+ transactions.
     */
    public function up(): void
    {
        // 1. Users Table Indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_active'], 'idx_users_role_active');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['name'], 'idx_users_name');
        });

        // 2. Expenses Table Indexes
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['expense_date', 'status'], 'idx_expenses_date_status');
            $table->index(['created_by', 'expense_date'], 'idx_expenses_creator_date');
            $table->index(['vendor_name'], 'idx_expenses_vendor');
            $table->index(['memo_no'], 'idx_expenses_memo');
        });

        // 3. Expense Items Indexes
        Schema::table('expense_items', function (Blueprint $table) {
            $table->index(['category'], 'idx_expense_items_category');
            $table->index(['item_name'], 'idx_expense_items_name');
            $table->index(['expense_id', 'category'], 'idx_expense_items_exp_cat');
        });

        // 4. Wallet Transactions Indexes
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->index(['wallet_id', 'created_at'], 'idx_wallet_tx_wallet_created');
            $table->index(['performed_by', 'created_at'], 'idx_wallet_tx_perf_created');
        });

        // 5. Fund Requests Indexes
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_fund_req_status_created');
            $table->index(['requested_by', 'status'], 'idx_fund_req_user_status');
        });

        // 6. Bazaar Demands Indexes
        Schema::table('bazaar_demands', function (Blueprint $table) {
            $table->index(['status', 'target_date'], 'idx_demands_status_date');
            $table->index(['created_by', 'status'], 'idx_demands_creator_status');
            $table->index(['assigned_to', 'status'], 'idx_demands_assignee_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_created_at');
            $table->dropIndex('idx_users_name');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_date_status');
            $table->dropIndex('idx_expenses_creator_date');
            $table->dropIndex('idx_expenses_vendor');
            $table->dropIndex('idx_expenses_memo');
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropIndex('idx_expense_items_category');
            $table->dropIndex('idx_expense_items_name');
            $table->dropIndex('idx_expense_items_exp_cat');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_wallet_tx_wallet_created');
            $table->dropIndex('idx_wallet_tx_perf_created');
        });

        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropIndex('idx_fund_req_status_created');
            $table->dropIndex('idx_fund_req_user_status');
        });

        Schema::table('bazaar_demands', function (Blueprint $table) {
            $table->dropIndex('idx_demands_status_date');
            $table->dropIndex('idx_demands_creator_status');
            $table->dropIndex('idx_demands_assignee_status');
        });
    }
};
