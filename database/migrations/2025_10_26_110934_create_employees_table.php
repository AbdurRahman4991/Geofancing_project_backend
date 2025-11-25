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
       Schema::create('employees', function (Blueprint $table) {
        $table->id();

        $table->string('name');
        $table->string('employee_id')->unique();  
        $table->string('company_id')->nullable();     

        // Relations
        // $table->foreignId('company_id')
        //     ->constrained('companies')
        //     ->onDelete('cascade');

        // Basic Info
        $table->string('phone');

        // New fields you required
        $table->string('status')->default('active');         // active, inactive, terminated
        $table->string('nature_of_employment');             // permanent / contract / part-time
        $table->string('department')->nullable();
        $table->string('unit')->nullable();
        $table->date('date_of_joining');
        $table->string('division')->nullable();
        $table->string('designation')->nullable();
        $table->string('reporting_person')->nullable();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
