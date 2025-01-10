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
        Schema::create('contract_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_type_id')->constrained('contract_types')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_fields');
    }
};
