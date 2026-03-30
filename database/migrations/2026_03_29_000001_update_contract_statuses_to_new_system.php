<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Contract;

return new class extends Migration
{
    public function up(): void
    {
        // Get all valid statuses from the Contract model
        $validStatuses = array_keys(Contract::getStatuses());

        // Update any contracts with invalid statuses to 'INT' first
        \Illuminate\Support\Facades\DB::table('contracts')
            ->whereNotIn('status', $validStatuses)
            ->update(['status' => Contract::STATUS_INTERESTED]);

        // Change column to ENUM type (for MySQL/MariaDB)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('contracts', function (Blueprint $table) use ($validStatuses) {
                $table->enum('status', $validStatuses)
                    ->default(Contract::STATUS_INTERESTED)
                    ->change();
            });
        } else {
            // For other databases, we'll keep it as a string but add a check constraint
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('status')->change();
            });
        }
    }

    public function down(): void
    {
        // Revert to old statuses (including draft)
        $oldStatuses = ['draft', 'INT', 'S&NP', 'S&P'];

        // Update contracts with new statuses to 'INT'
        \Illuminate\Support\Facades\DB::table('contracts')
            ->whereNotIn('status', $oldStatuses)
            ->update(['status' => 'INT']);

        // Change column back to old ENUM type (for MySQL/MariaDB)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('contracts', function (Blueprint $table) use ($oldStatuses) {
                $table->enum('status', $oldStatuses)
                    ->default('draft')
                    ->change();
            });
        } else {
            // For other databases, keep as string
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('status')->change();
            });
        }
    }
};
