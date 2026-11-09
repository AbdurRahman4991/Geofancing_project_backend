<?php

namespace App\Notifications;

use App\Models\EmployeeHierarchyAssignment;
use Illuminate\Notifications\Notification;

class HierarchyAssignmentNotification extends Notification
{
    public function __construct(
        private EmployeeHierarchyAssignment $assignment,
        private string $action
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->assignment->loadMissing(['user', 'hierarchyNode']);
        $employee = $this->assignment->user;
        $node = $this->assignment->hierarchyNode;

        return [
            'type' => 'hierarchy_assignment',
            'action' => $this->action,
            'title' => $this->action === 'transferred'
                ? 'Employee hierarchy transferred'
                : 'Employee hierarchy assigned',
            'message' => ($employee?->name ?? 'An employee') . ' was '
                . $this->action . ' to ' . ($node?->name ?? 'Unassigned') . '.',
            'assignment_id' => $this->assignment->id,
            'employee_user_id' => $this->assignment->user_id,
            'employee_name' => $employee?->name,
            'company_id' => $this->assignment->company_id,
            'hierarchy_node_id' => $this->assignment->hierarchy_node_id,
            'hierarchy_node_name' => $node?->name,
            'assigned_by' => $this->assignment->assigned_by,
        ];
    }
}
