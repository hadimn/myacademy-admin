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
        Schema::create('courses', function (Blueprint $table) {
            $table->id('course_id');
            $table->string('title');
            $table->longText('description');
            $table->enum('level',['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->json('topics')->nullable();
            $table->longText('video_url')->charset('binary')->nullable();
            $table->longText('image_url')->charset('binary')->nullable();
            $table->string('language');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
