<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_hierarchy_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->nullOnDelete();

            $table->foreignId('division_id')
                ->nullable()
                ->constrained('divisions')
                ->nullOnDelete();

            $table->foreignId('district_id')
                ->nullable()
                ->constrained('districts')
                ->nullOnDelete();

            $table->foreignId('sub_district_id')
                ->nullable()
                ->constrained('sub_districts')
                ->nullOnDelete();

            $table->foreignId('territory_id')
                ->nullable()
                ->constrained('territories')
                ->nullOnDelete();

            $table->foreignId('area_id')
                ->nullable()
                ->constrained('areas')
                ->nullOnDelete();

            $table->date('effective_from');

            $table->date('effective_to')
                ->nullable();

            $table->boolean('is_current')
                ->default(true);

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'is_current'
            ]);

            $table->index([
                'territory_id',
                'area_id'
            ]);
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_hierarchy_assignments');
    }
};
