<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Add columns for tax per sqm
            $table->boolean('enable_tax_per_sqm')->default(false)->after('space_net');
            $table->decimal('tax_per_sqm_amount', 10, 2)->default(0)->after('enable_tax_per_sqm');
            $table->decimal('tax_per_sqm_total', 10, 2)->default(0)->after('tax_per_sqm_amount');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'enable_tax_per_sqm',
                'tax_per_sqm_amount',
                'tax_per_sqm_total',
            ]);
        });
    }
};
