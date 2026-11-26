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
        Schema::create('market_geography_node_assigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('market_node_id')
                ->constrained('hierarchy_nodes')
                ->cascadeOnDelete();

            $table->foreignId('country_node_id')
                ->constrained('hierarchy_nodes')
                ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['company_id', 'market_node_id'],
                'unique_company_market_node'
            );

            $table->index(
                ['company_id', 'country_node_id'],
                'idx_company_country_node'
            );                       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_geography_node_assigns');
    }
};
