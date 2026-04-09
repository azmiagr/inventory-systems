<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
            $table->uuid('created_by');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
