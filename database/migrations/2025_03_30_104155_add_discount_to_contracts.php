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
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('space_discount',10,2)->default(0);
            $table->decimal('space_net',10,2)->default(0);
            $table->decimal('sponsor_discount',10,2)->default(0);
            $table->decimal('sponsor_net',10,2)->default(0);
            $table->decimal('ads_discount',10,2)->default(0);
            $table->decimal('ads_net',10,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('space_discount');
            $table->dropColumn('space_net');
            $table->dropColumn('sponsor_discount');
            $table->dropColumn('sponsor_net');
            $table->dropColumn('ads_discount');
            $table->dropColumn('ads_net');
        });
    }
};
