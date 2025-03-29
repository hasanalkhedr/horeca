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
        Schema::create('ads_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('ads_package_currency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ads_package_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->unique(['ads_package_id', 'currency_id']);
        });
        Schema::create('ads_package_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ads_package_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['ads_package_id', 'event_id']);
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('ads_package_id')->nullable()->constrained('ads_packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_package_currency');
        Schema::dropIfExists('ads_package_event');
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeignIdFor('contract');
        });
        Schema::dropColumns('contracts', 'ads_package_id');
        Schema::dropIfExists('ads_packages');
    }
};
