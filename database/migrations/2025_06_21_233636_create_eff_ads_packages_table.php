<?php

use App\Models\EffAdsPackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eff_ads_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('currency_eff_ads_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eff_ads_package_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->unique(['eff_ads_package_id', 'currency_id']);
        });
        Schema::create('eff_ads_package_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eff_ads_package_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['eff_ads_package_id', 'event_id']);
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('eff_ads_package_id')->nullable()->constrained('eff_ads_packages')->nullOnDelete();
            $table->json('eff_ads_check')->nullable();
            $table->decimal('eff_ads_amount', 10, 2)->default(0);
            $table->decimal('eff_ads_discount', 10, 2)->default(0);
            $table->decimal('eff_ads_net', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key and related columns first
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['eff_ads_package_id']);
            $table->dropColumn([
                'eff_ads_package_id',
                'eff_ads_check',
                'eff_ads_amount',
                'eff_ads_discount',
                'eff_ads_net',
            ]);
        });

        Schema::dropIfExists('eff_ads_package_event');
        Schema::dropIfExists('currency_eff_ads_package');
        Schema::dropIfExists('eff_ads_packages');
    }


};
