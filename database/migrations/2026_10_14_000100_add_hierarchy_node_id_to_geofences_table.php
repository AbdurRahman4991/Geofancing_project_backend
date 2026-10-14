<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->foreignId('hierarchy_node_id')
                ->nullable()
                ->after('area_id')
                ->constrained('hierarchy_nodes')
                ->nullOnDelete();
            $table->index(['company_id', 'hierarchy_node_id']);
        });
    }

    public function down(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'hierarchy_node_id']);
            $table->dropConstrainedForeignId('hierarchy_node_id');
        });
    }
};
