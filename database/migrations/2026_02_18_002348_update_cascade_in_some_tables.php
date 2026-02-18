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
            // First, ensure all columns are nullable since SET NULL requires nullable columns
            $table->unsignedBigInteger('company_id')->nullable()->change();
            $table->unsignedBigInteger('stand_id')->nullable()->change();
            $table->unsignedBigInteger('price_id')->nullable()->change();
            $table->unsignedBigInteger('event_id')->nullable()->change();

            // Drop existing foreign keys
            $table->dropForeign(['company_id']);
            $table->dropForeign(['stand_id']);
            $table->dropForeign(['price_id']);
            $table->dropForeign(['event_id']);

            // Re-create foreign keys with ON DELETE SET NULL
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('set null');

            $table->foreign('stand_id')
                  ->references('id')
                  ->on('stands')
                  ->onDelete('set null');

            $table->foreign('price_id')
                  ->references('id')
                  ->on('prices')
                  ->onDelete('set null');

            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop the SET NULL constraints
            $table->dropForeign(['company_id']);
            $table->dropForeign(['stand_id']);
            $table->dropForeign(['price_id']);
            $table->dropForeign(['event_id']);

            // Re-create original CASCADE constraints
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');

            $table->foreign('stand_id')
                  ->references('id')
                  ->on('stands')
                  ->onDelete('cascade');

            $table->foreign('price_id')
                  ->references('id')
                  ->on('prices')
                  ->onDelete('cascade');

            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->onDelete('cascade');

            // Revert columns back to NOT NULL (if that was the original state)
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->unsignedBigInteger('stand_id')->nullable(false)->change();
            $table->unsignedBigInteger('event_id')->nullable(false)->change();
            // Note: price_id was originally nullable, so we leave it nullable
        });
    }
};
