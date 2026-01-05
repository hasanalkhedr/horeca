<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stands', function (Blueprint $table) {
            $table->foreignId('parent_stand_id')->nullable()->constrained('stands')->onDelete('set null');
            $table->boolean('is_merged')->default(false);
            $table->string('original_no')->nullable();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('stands', function (Blueprint $table) {
            $table->dropForeign(['parent_stand_id']);
            $table->dropColumn(['parent_stand_id', 'is_merged', 'original_no', 'deleted_at']);
        });
    }
};
