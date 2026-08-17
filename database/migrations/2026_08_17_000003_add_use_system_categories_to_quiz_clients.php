<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_clients', function (Blueprint $table) {
            $table->boolean('use_system_categories')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_clients', function (Blueprint $table) {
            $table->dropColumn('use_system_categories');
        });
    }
};
