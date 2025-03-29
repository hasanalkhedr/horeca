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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('CODE')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('apply_start_date');
            $table->date('apply_deadline_date');
            $table->decimal('total_space');
            $table->decimal('space_to_sell');
            $table->decimal('free_space');
            $table->decimal('remaining_space_to_sell');
            $table->decimal('remaining_free_space');
            $table->decimal('vat_rate');
            $table->text('payment_method')->nullable();
            $table->timestamps();
        });

        Schema::create('category_event',function(Blueprint $table){
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        });
        Schema::create('currency_event',function(Blueprint $table){
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_event');
        Schema::dropIfExists('currency_event');
        Schema::dropIfExists('events');

    }
};
