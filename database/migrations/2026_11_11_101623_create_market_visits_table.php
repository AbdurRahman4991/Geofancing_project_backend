
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
        Schema::create('market_visits', function (Blueprint $table) {
            $table->id();

            // Company & Employee
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('geofence_id');

            // Check-in Location
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();

            // Check-out Location
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();

            // Visit Time
            $table->timestamp('visit_start_time')->nullable();
            $table->timestamp('visit_end_time')->nullable();

            // Duration (Minutes)
            $table->decimal('duration', 10, 2)->nullable();

            // Device Information
            $table->string('device_id')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'user_id']);
            $table->index('geofence_id');
            $table->index('visit_start_time');

            // Active visit lookup
            $table->index([
                'company_id',
                'user_id',
                'visit_end_time'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_visits');
    }
};
