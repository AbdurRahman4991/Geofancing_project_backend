<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->date('plan_date');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'user_id', 'plan_date']);
            $table->index(['company_id', 'status', 'plan_date']);
        });

        Schema::create('tour_plan_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_plan_id')->constrained('tour_plans')->cascadeOnDelete();
            $table->foreignId('geofence_id')->constrained('geofences')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->time('planned_arrival_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tour_plan_id', 'geofence_id']);
            $table->unique(['tour_plan_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_plan_stops');
        Schema::dropIfExists('tour_plans');
    }
};
