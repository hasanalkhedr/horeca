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
        Schema::create('sponsor_options', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
        Schema::create('sponsor_option_sponsor_package', function(Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('sponsor_packages','id')->onDelete('cascade');
            $table->foreignId('option_id')->constrained('sponsor_options','id')->onDelete('cascade');
            $table->unique(['package_id', 'option_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsor_option_sponsor_package');
        Schema::dropIfExists('sponsor_options');
    }
};
