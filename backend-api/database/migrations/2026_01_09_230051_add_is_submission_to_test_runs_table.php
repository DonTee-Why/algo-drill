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
        Schema::table('test_runs', function (Blueprint $table) {
            $table->boolean('is_submission')->default(false)->after('stderr_truncated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_runs', function (Blueprint $table) {
            $table->dropColumn('is_submission');
        });
    }
};
