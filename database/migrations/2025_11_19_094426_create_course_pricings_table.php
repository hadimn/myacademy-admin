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
        Schema::create('course_pricings', function (Blueprint $table) {
            $table->id("pricing_id");
            $table->foreignId('course_id')->constrained(table:"courses", column:"course_id")->onDelete('cascade');
            $table->decimal('price');
            $table->boolean('is_free')->default(false);
            $table->decimal('discount_price')->nullable();
            $table->timestamp('discount_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_pricings');
    }
};
