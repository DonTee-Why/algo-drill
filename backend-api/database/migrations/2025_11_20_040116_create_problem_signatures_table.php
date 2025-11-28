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
        Schema::create('problem_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('problem_id')->constrained('problems')->onDelete('cascade');
            $table->string('lang');
            $table->string('function_name');
            $table->json('params');
            $table->json('returns');
            $table->timestamps();

            $table->unique(['problem_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_signatures');
    }
};
