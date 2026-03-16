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
        // First, remove any duplicate records if they exist
        // This is optional but recommended to ensure the migration runs smoothly
        DB::statement('
            DELETE t1 FROM stands t1
            INNER JOIN stands t2
            WHERE t1.id > t2.id
            AND t1.no = t2.no
            AND t1.event_id = t2.event_id
        ');

        // Add the unique constraint
        Schema::table('stands', function (Blueprint $table) {
            $table->unique(['no', 'event_id'], 'stands_no_event_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stands', function (Blueprint $table) {
            $table->dropUnique('stands_no_event_unique');
        });
    }
};
