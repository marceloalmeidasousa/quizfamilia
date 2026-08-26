<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_states', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('questions_generation_status', 32)->nullable();
            $table->text('questions_generation_error')->nullable();
            $table->unsignedSmallInteger('questions_generation_total')->nullable();
            $table->unsignedSmallInteger('questions_generation_done')->default(0);
            $table->timestamps();
        });

        DB::table('system_states')->insert([
            'key' => 'family_questions',
            'questions_generation_done' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_states');
    }
};
