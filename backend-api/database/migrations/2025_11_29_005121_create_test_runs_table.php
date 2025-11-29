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
        Schema::create('test_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coaching_session_id')->constrained()->cascadeOnDelete();
            $table->string('lang');
            $table->text('source')->nullable();
            $table->json('result')->nullable();
            $table->integer('cpu_ms')->nullable();
            $table->integer('mem_kb')->nullable();
            $table->boolean('stderr_truncated')->default(false);
            $table->timestamps();

            $table->index(['coaching_session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_runs');
    }
};
