<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAction('view');
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $notifications = $user->notifications()
            ->latest()
            ->paginate($validated['per_page'] ?? 10);

        $notifications->through(fn ($notification) => [
            'id' => $notification->id,
            'type' => $notification->type,
            'data' => $notification->data,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ]);

        return response()->json([
            'status' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $notificationId)
    {
        $this->authorizeAction('edit');
        $notification = $request->user()->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read.',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->fresh()->read_at,
            ],
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $this->authorizeAction('edit');
        $updated = $request->user()->unreadNotifications()
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Unread notifications marked as read.',
            'updated_count' => $updated,
        ]);
    }

    
    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('notification.' . $action), 403, 'You do not have permission to ' . $action . ' orders.');
    }
}
