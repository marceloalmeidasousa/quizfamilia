<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('pin', 6)->unique();
            $table->string('nivel', 32);
            $table->string('host_token', 64)->unique();
            $table->string('status', 20)->default('lobby');
            $table->unsignedTinyInteger('current_index')->default(0);
            $table->timestamp('question_started_at')->nullable();
            $table->json('questions');
            $table->timestamps();
        });

        Schema::create('live_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->string('name', 40);
            $table->uuid('token')->unique();
            $table->unsignedInteger('score')->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('live_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('live_player_id')->constrained('live_players')->cascadeOnDelete();
            $table->unsignedTinyInteger('question_index');
            $table->unsignedTinyInteger('choice');
            $table->boolean('correct')->default(false);
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('answered_at_ms')->default(0);
            $table->timestamps();

            $table->unique(['live_player_id', 'question_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_answers');
        Schema::dropIfExists('live_players');
        Schema::dropIfExists('live_sessions');
    }
};
