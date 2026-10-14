<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hierarchy_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('hierarchy_level_id')->constrained('hierarchy_levels')->cascadeOnDelete();            
            $table->string('name', 150);
            $table->string('code', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['company_id', 'hierarchy_level_id']);           
            $table->unique(['company_id', 'hierarchy_level_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hierarchy_nodes');
    }
};