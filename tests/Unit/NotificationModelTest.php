<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_unread_scope_only_returns_notifications_with_null_read_at(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Unread',
            'message' => 'Unread message',
            'type' => 'task_assigned',
            'sent_at' => now(),
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Read',
            'message' => 'Read message',
            'type' => 'task_assigned',
            'sent_at' => now(),
            'read_at' => now(),
            'acknowledged_at' => null,
        ]);

        $count = Notification::query()->unread()->count();

        $this->assertSame(1, $count);
    }

    public function test_mark_as_read_sets_read_and_acknowledged_flags(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Mark me',
            'message' => 'Message',
            'type' => 'task_assigned',
            'sent_at' => now(),
            'read_at' => null,
            'acknowledged_at' => null,
            'is_read' => false,
        ]);

        $notification->markAsRead();
        $notification->refresh();

        $this->assertTrue((bool) $notification->is_read);
        $this->assertNotNull($notification->read_at);
        $this->assertNotNull($notification->acknowledged_at);
    }
}
