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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('pipe_id')->unsigned()->nullable()->after('id')->unique();
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('pipe_id')->unsigned()->nullable()->after('id')->unique();
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->integer('pipe_id')->unsigned()->nullable()->after('id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['pipe_id']);
            $table->dropColumn('pipe_id');
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['pipe_id']);
            $table->dropColumn('pipe_id');
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['pipe_id']);
            $table->dropColumn('pipe_id');
        });
    }
};
