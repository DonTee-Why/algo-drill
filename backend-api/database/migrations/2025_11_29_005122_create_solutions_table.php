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
        Schema::create('solutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('problem_id')->constrained()->cascadeOnDelete();
            $table->string('lang');
            $table->text('pseudocode')->nullable();
            $table->text('reference_code')->nullable();
            $table->text('explanation')->nullable();
            $table->json('complexity')->nullable();
            $table->json('alternatives')->nullable();
            $table->timestamps();

            $table->unique(['problem_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solutions');
    }
};
