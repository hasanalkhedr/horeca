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
        Schema::table('currency_event', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_price', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['event_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_event', function (Blueprint $table) {
            $table->dropColumn( 'min_price');
        });
    }
};
