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
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('contact_person')->constrained('clients')->nullOnDelete()->name('fk_contracts_contactPerson');
            $table->foreignId('exhabition_coordinator')->constrained('clients')->nullOnDelete()->name('fk_contracts_coordinator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('contact_person');
            $table->dropColumn('exhabition_coordinator');
        });
    }
};
