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
         Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // 🔹 ইউজার আইডি (Foreign key)
            $table->unsignedBigInteger('user_id');

            // 🔹 Check-in তথ্য
            $table->dateTime('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 6)->nullable();
            $table->decimal('check_in_longitude', 10, 6)->nullable();

            // 🔹 Check-out তথ্য
            $table->dateTime('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 6)->nullable();
            $table->decimal('check_out_longitude', 10, 6)->nullable();

            // 🔹 বর্তমান অবস্থা
            $table->enum('status', [
                'Checked In',
                'Checked Out',
                'Auto Checked Out',
                'Outside Area'
            ])->default('Checked In');

            // 🔹 অতিরিক্ত তথ্য
            $table->float('distance_from_office')->nullable();
            $table->string('device_id')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            // 🔹 Foreign key constraint (optional)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
