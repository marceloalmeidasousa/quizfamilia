<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 64)->unique();
            $table->string('logo_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('questions_generation_status', 32)->nullable();
            $table->text('questions_generation_error')->nullable();
            $table->unsignedSmallInteger('questions_generation_total')->nullable();
            $table->unsignedSmallInteger('questions_generation_done')->default(0);
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('id')
                ->constrained('quiz_clients')
                ->nullOnDelete();
            $table->index(['client_id', 'nivel', 'categoria']);
        });

        Schema::table('live_sessions', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('id')
                ->constrained('quiz_clients')
                ->nullOnDelete();
        });

        Schema::table('x1_challenges', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('id')
                ->constrained('quiz_clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('x1_challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });

        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'nivel', 'categoria']);
            $table->dropConstrainedForeignId('client_id');
        });

        Schema::dropIfExists('quiz_clients');
    }
};
