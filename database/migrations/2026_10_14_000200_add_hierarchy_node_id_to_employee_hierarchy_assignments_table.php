<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_hierarchy_assignments', function (Blueprint $table) {
            $table->foreignId('hierarchy_node_id')
                ->nullable()
                ->after('user_id')
                ->constrained('hierarchy_nodes')
                ->nullOnDelete();
            $table->index(['user_id', 'hierarchy_node_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::table('employee_hierarchy_assignments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'hierarchy_node_id', 'is_current']);
            $table->dropConstrainedForeignId('hierarchy_node_id');
        });
    }
};
