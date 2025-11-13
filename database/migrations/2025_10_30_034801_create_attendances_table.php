<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {

//     public function up(): void
//     {
//         Schema::create('attendances', function (Blueprint $table) {
//             $table->id();
//             $table->unsignedBigInteger('user_id');           
//             $table->dateTime('check_in_time')->nullable();
//             $table->decimal('check_in_latitude', 10, 6)->nullable();
//             $table->decimal('check_in_longitude', 10, 6)->nullable();
//             $table->dateTime('check_out_time')->nullable();
//             $table->string('work_hour')->nullable();
//             $table->string('late')->nullable();
//             $table->decimal('check_out_latitude', 10, 6)->nullable();
//             $table->decimal('check_out_longitude', 10, 6)->nullable();
//             $table->enum('status', [
//                 'Checked In',
//                 'Checked Out',
//                 'Auto Checked Out',
//                 'Outside Area',
//                 'Absent',
//                 'Weekend Holyday',
//                 'Goverment Holyday'
//             ])->default('Checked In');
//             $table->float('distance_from_office')->nullable();
//             $table->string('device_id')->nullable();
//             $table->text('remarks')->nullable();
//             $table->timestamps();
//             $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
//         });
//     }
    

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('attendances');
//     }
    
// };


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // 🔗 Foreign Key
            $table->unsignedBigInteger('user_id');

            // 🕒 Attendance times
            $table->dateTime('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 6)->nullable();
            $table->decimal('check_in_longitude', 10, 6)->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->string('work_hour')->nullable();
            $table->string('late')->nullable();

            // 📍 Checkout location
            $table->decimal('check_out_latitude', 10, 6)->nullable();
            $table->decimal('check_out_longitude', 10, 6)->nullable();

            // 🧾 Status options
            $table->enum('status', [
                'Checked In',
                'Checked Out',
                'Auto Checked Out',
                'Outside Area',
                'Absent',
                'Weekend Holyday',
                'Goverment Holyday'
            ])->default('Checked In');

            // 📏 Extra info
            $table->float('distance_from_office')->nullable();
            $table->string('device_id')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            // 🧩 Foreign key relation
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // ⚡ Indexes for better query performance
            $table->index('user_id');
            $table->index('check_in_time');
            $table->index('status');
            $table->index('late');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
