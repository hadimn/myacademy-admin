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
        Schema::create('sections', function (Blueprint $table) {
            $table->id('section_id');
            $table->foreignId('course_id')->constrained('courses', 'course_id');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->longText('image_url')->charset('binary')->nullable();
            $table->integer('order');
            $table->boolean('is_last')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
