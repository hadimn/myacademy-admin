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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');
            $table->foreignId('user_id')->constrained(table:"users", column:"id")->onDelete('cascade');
            $table->foreignId('course_id')->constrained(table:"courses", column:"course_id")->onDelete('cascade');
            $table->decimal('amount_paid')->default(0);
            $table->enum("payment_status", ['pending', 'paid', 'failed', 'refunded', 'canceled']);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->timestamp('enrolled_at')->nullable();   
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
