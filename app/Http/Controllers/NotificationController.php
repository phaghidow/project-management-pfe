<?php

namespace App\Http\Controllers;

use App\Events\NotificationRead;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the latest notifications for the authenticated user (for dropdown).
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $query = Auth::user()->notifications()->latest();

        // If the client expects JSON (AJAX), return paginated JSON (API behavior)
        if ($request->wantsJson() || $request->get('ajax')) {
            $notifications = $query->paginate($perPage);
            
            // Enrich notifications with permission information
            $notifications->getCollection()->transform(function ($notification) {
                return $this->enrichNotificationWithPermissions($notification);
            });
            
            return response()->json($notifications);
        }

        // Otherwise render the full HTML page with counts and initial pagination
        $notifications = $query->paginate($perPage);
        
        // Enrich notifications with permission information
        $notifications->getCollection()->transform(function ($notification) {
            return $this->enrichNotificationWithPermissions($notification);
        });
        
        $unreadCount = Auth::user()->notifications()->unread()->count();
        $totalCount = Auth::user()->notifications()->count();

        return response()->view('notifications.index', compact('notifications', 'unreadCount', 'totalCount'));
    }

    /**
     * Enrich notification with permission information
     */
    private function enrichNotificationWithPermissions(Notification $notification): Notification
    {
        $user = Auth::user();
        $canAccess = false;
        $accessReason = '';

        // Check if the notification is related to a specific resource
        if ($notification->related_type && $notification->related_id) {
            $relatedModel = $this->getRelatedModel($notification->related_type, $notification->related_id);
            
            if ($relatedModel) {
                // Use policies to check if user can view the related model
                if ($user->can('view', $relatedModel)) {
                    $canAccess = true;
                    $accessReason = 'allowed';
                } else {
                    $canAccess = false;
                    $accessReason = match ($notification->related_type) {
                        'task' => 'Vous n\'avez pas accès à cette tâche',
                        'project' => 'Vous n\'avez pas accès à ce projet',
                        'milestone' => 'Vous n\'avez pas accès à ce jalon',
                        default => 'Vous n\'avez pas accès à cette ressource'
                    };
                }
            } else {
                $accessReason = 'Resource not found';
            }
        } else {
            // Notifications without related resources are always accessible
            $canAccess = true;
            $accessReason = 'no_resource';
        }

        // Add permission info to notification
        $notification->setAttribute('can_access', $canAccess);
        $notification->setAttribute('access_reason', $accessReason);

        return $notification;
    }

    /**
     * Get the related model instance
     */
    private function getRelatedModel(string $type, int $id)
    {
        $modelClass = match ($type) {
            'task' => 'App\\Models\\Task',
            'project' => 'App\\Models\\Project',
            'milestone' => 'App\\Models\\Milestone',
            'comment' => 'App\\Models\\Comment',
            default => null
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($id);
    }

    /**
     * Get the count of unread notifications (for badge).
     */
    public function count(): JsonResponse
    {
        $count = Auth::user()
            ->notifications()
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
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

        // Trigger realtime refresh for all opened clients of the same user.
        NotificationRead::dispatch(0, Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * Send notification to all active users of a given role.
     */
    public function sendToRole(Request $request, string $role): JsonResponse
    {
        $sender = Auth::user();

        if (!in_array($sender->role, [User::ROLE_ADMIN, User::ROLE_CHEF_DEPARTEMENT, User::ROLE_CHEF_PROJET], true)) {
            abort(403, 'Unauthorized');
        }

        if (!in_array($role, User::ROLES, true)) {
            return response()->json(['message' => 'Invalid role'], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'max:100'],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:604800'],
            'metadata' => ['nullable', 'array'],
        ]);

        $sent = NotificationService::sendToRole(
            $role,
            $validated['title'],
            $validated['message'],
            $validated['type'] ?? 'role_announcement',
            null,
            null,
            array_merge($validated['metadata'] ?? [], [
                'sender_id' => $sender->id,
                'sender_role' => $sender->role,
                'target_role' => $role,
            ]),
            $sender->id,
            $validated['delay_seconds'] ?? null
        );

        return response()->json([
            'success' => true,
            'sent' => $sent,
        ]);
    }

    /**
     * Send notification to all active users of a structure.
     */
    public function sendToStructure(Request $request, int $structureId): JsonResponse
    {
        $sender = Auth::user();

        if (!in_array($sender->role, [User::ROLE_ADMIN, User::ROLE_CHEF_DEPARTEMENT], true)) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'max:100'],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:604800'],
            'metadata' => ['nullable', 'array'],
        ]);

        $sent = NotificationService::sendToStructure(
            $structureId,
            $validated['title'],
            $validated['message'],
            $validated['type'] ?? 'structure_announcement',
            null,
            null,
            array_merge($validated['metadata'] ?? [], [
                'sender_id' => $sender->id,
                'sender_role' => $sender->role,
                'target_structure_id' => $structureId,
            ]),
            $sender->id,
            $validated['delay_seconds'] ?? null
        );

        return response()->json([
            'success' => true,
            'sent' => $sent,
        ]);
    }
}

