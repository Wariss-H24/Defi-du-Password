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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->onDelete('cascade');
            $table->foreignId('current_step_id')->nullable()->constrained('steps')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress');
            $table->unsignedInteger('progress')->default(0); // % progression
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
