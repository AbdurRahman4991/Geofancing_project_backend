<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Notifications\Notification;

class ApprovalNotification extends Notification
{
    public function __construct(private ApprovalRequest $approval, private string $action) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toDatabase(object $notifiable): array
    {
        $step = $this->approval->steps()->where('step_order', $this->approval->current_step)->first();
        return [
            'type' => 'approval', 'action' => $this->action,
            'title' => match ($this->action) {
                'approved' => 'Approval completed', 'rejected' => 'Request rejected', default => 'Approval required',
            },
            'message' => $this->action === 'pending'
                ? "A {$this->approval->module} request is awaiting {$step?->name}."
                : "Your {$this->approval->module} request was {$this->action}.",
            'approval_request_id' => $this->approval->id,
            'company_id' => $this->approval->company_id,
            'module' => $this->approval->module,
            'document_id' => $this->approval->document_id,
            'current_step' => $this->approval->current_step,
        ];
    }
}
