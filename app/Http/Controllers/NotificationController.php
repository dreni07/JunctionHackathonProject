<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * The signed-in user's most recent notifications, plus the unread count.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read' => $notification->read(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'data' => $notifications,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $request->user()->notifications()
            ->whereKey($notification)
            ->first()
            ?->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
