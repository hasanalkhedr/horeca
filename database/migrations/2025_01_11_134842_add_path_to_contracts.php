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
            $table->string('path')->nullable();
            $table->decimal('space_amount')->default(0);
            $table->decimal('sponsor_amount')->default(0);
            $table->decimal('advertisment_amount')->default(0);
            $table->decimal('total_amount')->default(0);
            $table->string('status');
            $table->date('contract_date')->nullable();
            $table->decimal('price_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('path');
            $table->dropColumn('space_amount');
            $table->dropColumn('sponsor_amount');
            $table->dropColumn('advertisment_amount');
            $table->dropColumn('total_amount');
            $table->dropColumn('status');
            $table->dropColumn('contract_date');
            $table->dropColumn('price_amount');
        });
    }
};
