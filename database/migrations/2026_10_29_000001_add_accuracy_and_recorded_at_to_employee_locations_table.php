<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_locations', function (Blueprint $table) {
            $table->decimal('accuracy', 8, 2)->nullable()->after('longitude');
            $table->timestamp('recorded_at')->nullable()->index()->after('accuracy');
        });

        DB::table('employee_locations')->whereNull('recorded_at')->update(['recorded_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('employee_locations', function (Blueprint $table) {
            $table->dropColumn(['accuracy', 'recorded_at']);
        });
    }
};
