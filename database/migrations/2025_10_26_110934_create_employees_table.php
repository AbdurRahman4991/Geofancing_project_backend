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

        $table->string('name')->nullable();
        $table->string('employee_id')->unique();       

        // Relations
        $table->foreignId('company_id')
            ->constrained('companies')
            ->onDelete('cascade');        

        // Basic Info
        $table->string('phone')->nullable();

        // New fields you required
        $table->string('status')->default('active');         // active, inactive, terminated
        $table->string('nature_of_employment')->nullable();             // permanent / contract / part-time
        $table->string('department_id')->nullable();
        $table->string('unit_id')->nullable();
        $table->date('date_of_joining')->nullable();
        $table->string('division_id')->nullable();
        $table->string('designation_id')->nullable();
        $table->string('reporting_person')->nullable();
        $table->string('email')->nullable();
        $table->date('dob')->nullable();
        $table->string('section_id')->nullable();

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
