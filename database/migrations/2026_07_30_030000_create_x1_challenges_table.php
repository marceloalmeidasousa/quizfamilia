<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('x1_challenges', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->string('nivel', 32);
            $table->string('categoria', 80)->nullable();
            $table->json('questions');
            $table->string('status', 32)->default('playing_creator');

            $table->string('creator_name', 40);
            $table->unsignedTinyInteger('creator_score')->nullable();
            $table->timestamp('creator_finished_at')->nullable();

            $table->string('opponent_name', 40)->nullable();
            $table->unsignedTinyInteger('opponent_score')->nullable();
            $table->timestamp('opponent_finished_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country', 80)->nullable();
            $table->string('city', 120)->nullable();

            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('x1_challenges');
    }
};
