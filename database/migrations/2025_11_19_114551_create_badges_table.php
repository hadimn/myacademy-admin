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
        Schema::create('badges', function (Blueprint $table) {
            $table->id('badge_id');
            $table->string('name');
            $table->text('description');
            $table->longText('icon')->charset('binary')->nullable();
            $table->enum('type', ['streak', 'course_completion', 'points', 'lesson_completion', 'time_spent']);
            $table->json('criteria');
            $table->integer('points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
