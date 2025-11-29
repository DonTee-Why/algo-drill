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
        Schema::create('attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coaching_session_id')->constrained()->cascadeOnDelete();
            $table->string('stage');
            $table->json('payload')->nullable();
            $table->text('coach_msg')->nullable();
            $table->json('rubric_scores')->nullable();
            $table->timestamps();

            $table->index('coaching_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
