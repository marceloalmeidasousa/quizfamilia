<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_plays', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16); // solo | live
            $table->string('nivel', 32);
            $table->string('categoria', 80)->nullable();
            $table->foreignId('live_session_id')->nullable()->constrained('live_sessions')->nullOnDelete();
            $table->json('player_names')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country', 80)->nullable();
            $table->string('city', 120)->nullable();
            $table->timestamp('started_at');
            $table->timestamps();

            $table->index(['type', 'started_at']);
            $table->index('nivel');
        });

        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->string('method', 10)->default('GET');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country', 80)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('session_id', 100)->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['visited_at']);
            $table->index(['session_id', 'path']);
        });

        Schema::table('live_sessions', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('question_started_at');
            $table->timestamp('finished_at')->nullable()->after('started_at');
            $table->string('ip_address', 45)->nullable()->after('finished_at');
            $table->string('country', 80)->nullable()->after('ip_address');
            $table->string('city', 120)->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'finished_at', 'ip_address', 'country', 'city']);
        });

        Schema::dropIfExists('site_visits');
        Schema::dropIfExists('game_plays');
    }
};
