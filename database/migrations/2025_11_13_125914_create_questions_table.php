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
        Schema::create('questions', function (Blueprint $table) {
            $table->id('questions_id');
            $table->foreignId('lesson_id')->constrained(table: 'lessons', column: 'lesson_id');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('question_type', ['mcq', 'fill', 'torf', 'checkbox', 'matching']);
            $table->longText('video_url')->charset('binary')->nullable();
            $table->longText('image_url')->charset('binary')->nullable();
            $table->integer('points');
            $table->json('options')->nullable();
            $table->json('correct_answer');
            $table->longText('explanation')->nullable();
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
