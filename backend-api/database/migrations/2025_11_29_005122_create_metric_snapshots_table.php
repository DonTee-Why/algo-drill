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
        Schema::create('metric_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coaching_session_id')->constrained()->cascadeOnDelete();
            $table->integer('time_to_bf')->nullable();
            $table->integer('passes_to_ac')->nullable();
            $table->smallInteger('edges_missed_pct')->nullable();
            $table->integer('hint_count')->nullable();
            $table->json('relapse_flags')->nullable();
            $table->timestamps();

            $table->index('coaching_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_snapshots');
    }
};
