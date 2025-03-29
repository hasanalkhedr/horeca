<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable(); // Optional: Report name
        $table->json('components'); // Store selected components as JSON
        $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
        $table->timestamps();
    });
    Schema::table('contracts', function(Blueprint $table) {
        $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropColumns('contracts', 'report_id');
        Schema::dropIfExists('reports');
    }
};
