<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_generation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 16);
            $table->foreignId('client_id')->nullable()->constrained('quiz_clients')->cascadeOnDelete();
            $table->string('nivel', 32)->nullable();
            $table->text('prompt');
            $table->json('categories');
            $table->unsignedSmallInteger('total');
            $table->unsignedSmallInteger('done')->default(0);
            $table->boolean('use_emoji')->default(true);
            $table->boolean('use_images')->default(false);
            $table->string('status', 16)->default('queued');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['scope', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_generation_runs');
    }
};
