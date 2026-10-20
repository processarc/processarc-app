<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('cycle_time_hours', 8, 1)->default(0);
            $table->decimal('target_cycle_hours', 8, 1)->default(0);
            $table->timestamps();
            $table->index(['status', 'category']);
        });

        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('owner_name')->nullable();
            $table->string('status')->default('not_started');
            $table->unsignedInteger('position')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['process_id', 'position']);
        });

        Schema::create('process_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->decimal('cycle_time_hours', 8, 1);
            $table->unsignedInteger('exception_count')->default(0);
            $table->date('measured_at');
            $table->timestamps();
            $table->index(['process_id', 'measured_at']);
        });

        Schema::create('optimization_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('identified');
            $table->string('owner_name')->nullable();
            $table->decimal('impact_hours', 8, 1)->default(0);
            $table->timestamps();
            $table->index(['status', 'priority']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->text('description');
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('optimization_opportunities');
        Schema::dropIfExists('process_metrics');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('processes');
    }
};
