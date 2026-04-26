<?php

namespace App\Http\Controllers;

use App\Events\NotificationRead;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the latest notifications for the authenticated user (for dropdown).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);

        $notifications = Auth::user()
            ->notifications()
            ->unread()
            ->latest()
            ->paginate($perPage);

        return response()->json($notifications);
    }

    /**
     * Get the count of unread notifications (for badge).
     */
    public function count(): int
    {
        return Auth::user()
            ->notifications()
            ->unread()
            ->count();
    }

    /**
     * Mark a specific notification as read.
     */
    public function read(Notification $notification): JsonResponse
    {
        if (Auth::id() !== $notification->user_id) {
            abort(403, 'Unauthorized');
        }

        $notification->markAsRead();

        // Dispatch real-time event
        NotificationRead::dispatch($notification->id, $notification->user_id);

        return response()->json([
            'success' => true,
            'notification' => $notification->fresh()
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll(): JsonResponse
    {
        Auth::user()->notifications()->unread()->get()->each->markAsRead();

        return response()->json(['success' => true]);
    }
}

