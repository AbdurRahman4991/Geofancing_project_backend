<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('module', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'module', 'name']);
            $table->index(['company_id', 'module', 'is_active']);
        });

        Schema::create('approval_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('name', 150);
            $table->string('approver_type', 40);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approver_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('approver_role')->nullable();
            $table->timestamps();
            $table->unique(['workflow_id', 'step_order']);
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->restrictOnDelete();
            $table->string('module', 100);
            $table->unsignedBigInteger('document_id');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('current_step')->default(1);
            $table->string('status', 20)->default('pending');
            $table->timestamp('submitted_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'module']);
            $table->index(['module', 'document_id']);
        });

        Schema::create('approval_request_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('name', 150);
            $table->string('approver_type', 40);
            $table->json('assigned_approver_ids');
            $table->string('status', 20);
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['approval_request_id', 'step_order']);
            $table->index(['approval_request_id', 'status', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflow_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
