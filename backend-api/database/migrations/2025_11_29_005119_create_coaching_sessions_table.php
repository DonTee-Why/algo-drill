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
        Schema::create('coaching_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('problem_id')->constrained()->cascadeOnDelete();
            $table->string('state');
            $table->json('scores')->nullable();
            $table->json('hints_used')->nullable();
            $table->json('timers')->nullable();
            $table->json('revealed_langs')->nullable();
            $table->timestamp('revealed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'problem_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_sessions');
    }
};
