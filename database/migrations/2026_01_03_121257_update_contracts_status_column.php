<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Contract;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update invalid statuses to 'draft' first
        $validStatuses = array_keys(Contract::getStatuses());

        \Illuminate\Support\Facades\DB::table('contracts')
            ->whereNotIn('status', $validStatuses)
            ->update(['status' => Contract::STATUS_DRAFT]);

        // Change column to ENUM type (for MySQL/MariaDB)
        Schema::table('contracts', function (Blueprint $table) use ($validStatuses) {
            $table->enum('status', $validStatuses)
                ->default(Contract::STATUS_DRAFT)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Revert to string type
            $table->string('status')->default('draft')->change();
        });
    }
};
