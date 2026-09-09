<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('expense_date');
            $table->string('title')->default('দৈনিক বাজার');
            $table->string('memo_no')->nullable(); // বিল / ক্যাশ মেমো নম্বর
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('status', ['SUBMITTED', 'REVIEWED', 'FLAGGED'])->default('SUBMITTED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->onDelete('cascade');
            $table->string('item_name');
            $table->string('category')->default('কাঁচাবাজার');
            $table->decimal('quantity', 8, 2);
            $table->string('unit')->default('কেজি');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        Schema::create('slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->onDelete('cascade');
            $table->string('image_path');
            $table->string('original_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slips');
        Schema::dropIfExists('expense_items');
        Schema::dropIfExists('expenses');
    }
};
