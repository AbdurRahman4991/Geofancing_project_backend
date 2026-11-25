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

        Schema::create('country_hiearchy_nodes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('hierarchy_level_id')
                ->constrained('country_hiearchy_levels')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('country_hiearchy_nodes')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->string('code', 100)->nullable();

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();

            $table->index(['company_id', 'hierarchy_level_id']);
            $table->index(['company_id', 'parent_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_hiearchy_nodes');
    }
};
