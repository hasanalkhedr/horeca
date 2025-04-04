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
        Schema::table("prices", function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
            $table->dropColumn('amount');
        });
        Schema::create('currency_price', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->unique(['price_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_price');
        Schema::table("prices", function (Blueprint $table) {
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('amount', 8,2);
        });
    }
};
