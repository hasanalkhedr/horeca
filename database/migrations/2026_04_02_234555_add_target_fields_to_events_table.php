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
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('target_space')->nullable()->after('remaining_free_space');
            $table->decimal('target_space_amount')->nullable()->after('target_space');
            $table->decimal('target_sponsor_amount')->nullable()->after('target_space_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['target_space', 'target_space_amount', 'target_sponsor_amount']);
        });
    }
};
