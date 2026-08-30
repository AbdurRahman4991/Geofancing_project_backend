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
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')
            ->constrained('areas')
            ->cascadeOnDelete();
            $table->foreignId('company_id')
            ->constrained('companies')
            ->cascadeOnDelete(); // যেমন: Head Office, Uttara Zone, Mirpur Area            
            $table->string('firm_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('radius')->default(100); // মিটারে এলাকা
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
