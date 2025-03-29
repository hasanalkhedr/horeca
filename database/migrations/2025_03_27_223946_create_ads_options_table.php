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
        Schema::create('ads_options', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('ads_option_currency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ads_option_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['ads_option_id', 'currency_id']);
        });
        Schema::create('ads_option_ads_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ads_option_id')->constrained()->onDelete('cascade');
            $table->foreignId('ads_package_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['ads_option_id', 'ads_package_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_option_currency');
        Schema::dropIfExists('ads_option_ads_package');
        Schema::dropIfExists('ads_options');
    }
};
