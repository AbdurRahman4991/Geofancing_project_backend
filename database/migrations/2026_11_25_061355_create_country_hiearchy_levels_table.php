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
        Schema::create('country_hiearchy_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 100)->nullable();
            $table->unsignedInteger('level_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'level_order']);
            $table->index(['company_id', 'is_active']);            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_hiearchy_levels');
    }
};
