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
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->foreignId('user_id')->constrained(table:"users", column:"id")->onDelete('cascade');
            $table->foreignId('course_id')->constrained(table:"courses", column:"course_id")->onDelete('cascade');
            $table->foreignId('section_id')->constrained(table:"sections", column:"section_id")->onDelete('cascade');
            $table->foreignId('unit_id')->constrained(table:"units", column:"unit_id")->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained(table:"lessons", column:"lesson_id")->onDelete('cascade');
            $table->boolean('is_completed')->default(0);
            $table->integer('time_spent')->default(0);
            $table->integer('points')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};
