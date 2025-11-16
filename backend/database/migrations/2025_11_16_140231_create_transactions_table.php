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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tuuid')->unique();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 20, 2);
            $table->decimal('commission_fee', 20, 2)->default(0.00);
            $table->enum('type', ['transfer', 'deposit', 'commission', 'withdrawal'])->default('transfer');
            $table->enum('status', ['completed', 'failed', 'pending'])->default('completed');
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['sender_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index('tuuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
