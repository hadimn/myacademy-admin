<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('answered_questions', function (Blueprint $table) {
            $table->index(['user_id', 'earned_points'], 'aq_user_points_index');
        });
    }

    public function down(): void
    {
        Schema::table('answered_questions', function (Blueprint $table) {
            $table->dropIndex('aq_user_points_index');
        });
    }
};
