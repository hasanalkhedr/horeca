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
        Schema::create('user_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->decimal('target_space', 10, 2)->default(0); // Target space in sqm
            $table->decimal('target_space_amount', 15, 2)->default(0); // Target amount in currency
            $table->decimal('target_sponsor_amount', 15, 2)->default(0); // Target sponsor amount
            $table->decimal('achieved_space', 10, 2)->default(0); // Achieved space in sqm
            $table->decimal('achieved_space_amount', 15, 2)->default(0); // Achieved amount in currency
            $table->decimal('achieved_sponsor_amount', 15, 2)->default(0); // Achieved sponsor amount
            $table->integer('contracts_count')->default(0); // Number of contracts
            $table->decimal('completion_percentage', 5, 2)->default(0); // Percentage of target achieved
            $table->enum('status', ['active', 'completed', 'expired'])->default('active');
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();

            // Unique constraint to ensure one target per user per event
            $table->unique(['user_id', 'event_id']);

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index('completion_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_targets');
    }
};
