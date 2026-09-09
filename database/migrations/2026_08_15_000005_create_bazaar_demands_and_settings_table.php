<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pre-Bazaar Demand / Shopping List
        Schema::create('bazaar_demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->default('বাজারের শপিং লিস্ট');
            $table->date('target_date');
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Shopping List Items
        Schema::create('bazaar_demand_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bazaar_demand_id')->constrained('bazaar_demands')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('category')->default('কাঁচাবাজার');
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit', 50)->default('কেজি');
            $table->decimal('estimated_price', 10, 2)->nullable();
            $table->boolean('is_purchased')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // System Settings & Monthly Budget Ceiling
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bazaar_demand_items');
        Schema::dropIfExists('bazaar_demands');
        Schema::dropIfExists('system_settings');
    }
};
