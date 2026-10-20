<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_dependencies', function (Blueprint $table) {
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_process_id')->constrained('processes')->cascadeOnDelete();
            $table->primary(['process_id', 'depends_on_process_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_dependencies');
    }
};
