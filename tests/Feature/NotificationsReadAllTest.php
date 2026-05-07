<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_calling_read_all()
    {
        $response = $this->post('/notifications/read-all');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_mark_all_notifications_as_read()
    {
        $user = User::factory()->create();

        // create unread notifications for the user
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Test 1',
            'message' => 'Message 1',
            'type' => 'task_assigned',
            'sent_at' => now(),
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Test 2',
            'message' => 'Message 2',
            'type' => 'task_assigned',
            'sent_at' => now(),
        ]);

        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'read_at' => null]);

        $response = $this->actingAs($user)->post('/notifications/read-all');

        $response->assertJson(['success' => true]);

        // all notifications for the user should now have read_at not null
        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id, 'read_at' => null]);
    }

    public function test_guest_is_redirected_to_login_when_calling_read_single_notification()
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Single Test',
            'message' => 'Message',
            'type' => 'task_assigned',
            'sent_at' => now(),
        ]);

        $response = $this->post('/notifications/' . $notification->id . '/read');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_owner_can_mark_single_notification_as_read()
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Single Test',
            'message' => 'Message',
            'type' => 'task_assigned',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/notifications/' . $notification->id . '/read');

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_authenticated_non_owner_cannot_mark_single_notification_as_read()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Private Notification',
            'message' => 'Message',
            'type' => 'task_assigned',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)->post('/notifications/' . $notification->id . '/read');

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }
}
