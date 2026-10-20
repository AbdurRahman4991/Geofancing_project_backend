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
             $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
             $table->foreignId('hierarchy_node_id')
                ->nullable()
                ->constrained('hierarchy_nodes')
                ->nullOnDelete();

            $table->boolean('is_current')
                ->default(true);

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'user_id',
                'is_current'
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
