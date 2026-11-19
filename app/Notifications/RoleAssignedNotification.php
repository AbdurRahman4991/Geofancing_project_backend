<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class RoleAssignedNotification extends Notification
{
    /**
     * @param array<int, string> $roleNames
     */
    public function __construct(private array $roleNames)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $roleList = implode(', ', $this->roleNames);

        return [
            'type' => 'role_assigned',
            'title' => 'Role assigned',
            'message' => 'You have been assigned the following role' . (count($this->roleNames) === 1 ? ': ' : 's: ') . $roleList . '.',
            'roles' => $this->roleNames,
        ];
    }
}
