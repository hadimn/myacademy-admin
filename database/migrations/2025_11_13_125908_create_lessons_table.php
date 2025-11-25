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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id('lesson_id');
            $table->foreignId('unit_id')->constrained(table: 'units', column:'unit_id');
            $table->string('title');
            $table->longText('description');
            $table->longText('content')->nullable();
            $table->longText('video_url')->charset('binary')->nullable();
            $table->longText('image_url')->charset('binary')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('lesson_type', ['normal', 'review', 'practice'])->default('normal');
            $table->boolean('is_last')->default(0);
            $table->boolean('chest_after')->default(0);
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
