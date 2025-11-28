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
        Schema::create('problem_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('problem_id')->constrained('problems')->onDelete('cascade')->index();
            $table->json('input');
            $table->json('expected');
            $table->boolean('is_edge')->default(false);
            $table->smallInteger('weight')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_tests');
    }
};
