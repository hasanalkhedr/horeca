<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $command;
    /**
     * Run the migrations.
     */
   public function up()
    {
        // Get all tables with a more reliable method
        $databaseName = DB::connection()->getDatabaseName();

        $tables = DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
            AND TABLE_TYPE = 'BASE TABLE'
        ", [$databaseName]);

        $alteredCount = 0;

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;

            // Skip migration table and system tables
            if (in_array($tableName, ['migrations', 'failed_jobs', 'password_resets', 'personal_access_tokens'])) {
                continue;
            }

            // Get all DECIMAL columns
            $decimalColumns = DB::select("
                SELECT COLUMN_NAME, NUMERIC_PRECISION, NUMERIC_SCALE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND DATA_TYPE = 'decimal'
                AND (NUMERIC_PRECISION < 15 OR NUMERIC_SCALE != 2)
            ", [$databaseName, $tableName]);

            foreach ($decimalColumns as $column) {
                try {
                    DB::statement("
                        ALTER TABLE `{$tableName}`
                        MODIFY `{$column->COLUMN_NAME}` DECIMAL(15,2)
                    ");

                    $alteredCount++;

                    if ($this->command) {
                        $this->command->line("✓ Altered: {$tableName}.{$column->COLUMN_NAME}");
                    }
                } catch (\Exception $e) {
                    if ($this->command) {
                        $this->command->error("✗ Failed: {$tableName}.{$column->COLUMN_NAME} - {$e->getMessage()}");
                    }
                }
            }
        }

        if ($this->command) {
            $this->command->info("Altered {$alteredCount} decimal columns to DECIMAL(15,2)");
        }
    }

    public function down()
    {
        if ($this->command) {
            $this->command->error("This migration cannot be automatically reversed.");
            $this->command->line("Please restore from backup or manually revert changes.");
        }
    }
};
