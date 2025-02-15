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
        Schema::table('events', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->string('description')->nullable();
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('special_design_text')->nullable();
            $table->decimal('special_design_price')->default(0);
            $table->decimal('special_design_amount')->default(0);
            $table->boolean('if_water')->default(false);
            $table->boolean('if_electricity')->default(false);
            $table->string('electricity_text')->nullable();
            $table->decimal('water_electricity_amount')->default(0);
            $table->string('new_product')->nullable();
            $table->foreignId('sponsor_package_id')->nullable()->constrained('sponsor_packages')->nullOnDelete();
            $table->string('specify_text')->nullable();
            $table->string('notes1')->nullable();
            $table->string('notes2')->nullable();
            $table->renameColumn('total_amount', 'sub_total_1');
            $table->decimal('d_i_a')->default(0);
            $table->decimal('sub_total_2')->default(0);
            $table->decimal('vat_amount')->default(0);
            $table->decimal('net_total')->default(0);
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['country', 'city', 'address']);
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign('contracts_sponsor_package_id_foreign');
            $table->dropForeign('contracts_category_id_foreign');
            $table->dropColumn([
                'special_design_text',
                'special_design_price',
                'special_design_amount',
                'if_water',
                'if_electricity',
                'electricity_text',
                'water_electricity_amount',
                'new_product',
                'sponsor_package_id',
                'specify_text',
                'notes1',
                'notes2',
                'd_i_a',
                'sub_total_2',
                'vat_amount',
                'net_total',
                'category_id',
            ]);
            $table->renameColumn('sub_total_1', 'total_amount');
        });
    }
};
