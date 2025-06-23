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
        Schema::create('eff_ads_options', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('currency_eff_ads_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eff_ads_option_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['eff_ads_option_id', 'currency_id']);
        });
        Schema::create('eff_ads_option_eff_ads_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eff_ads_option_id')->constrained()->onDelete('cascade');
            $table->foreignId('eff_ads_package_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(
                ['eff_ads_option_id', 'eff_ads_package_id'],
                'option_package_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_eff_ads_option');
        Schema::dropIfExists('eff_ads_option_eff_ads_package');
        Schema::dropIfExists('eff_ads_options');
    }
};
