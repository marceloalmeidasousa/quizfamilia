<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('nivel', 32)->index();
            $table->string('code', 16)->unique();
            $table->string('categoria', 80);
            $table->string('emoji', 16)->nullable();
            $table->text('pergunta');
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order');
            $table->string('texto', 255);
            $table->string('emoji', 16)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['question_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
