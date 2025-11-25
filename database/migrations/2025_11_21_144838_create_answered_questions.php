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
        Schema::create('answered_questions', function (Blueprint $table) {
            $table->id('answered_id');
            $table->foreignId('user_id')->constrained(table:"users", column:"id");
            $table->foreignId('questions_id')->constrained(table:"questions", column:"questions_id");
            $table->integer('earned_points')->default(0);
            $table->boolean('is_passed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answered_questions');
    }
};
