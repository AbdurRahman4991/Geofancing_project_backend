<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hierarchy_nodes', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('hierarchy_level_id')
                ->constrained('hierarchy_nodes')
                ->nullOnDelete();
            $table->index(['company_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('hierarchy_nodes', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'parent_id']);
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
