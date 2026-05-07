<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_send_notification_to_role(): void
    {
        $sender = User::factory()->create([
            'role' => User::ROLE_MEMBRE,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($sender)->postJson('/notifications/role/chef_projet', [
            'title' => 'Info',
            'message' => 'Message',
            'type' => 'role_announcement',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_send_notification_to_role_and_sender_is_excluded(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $recipientA = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
        ]);

        $recipientB = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->postJson('/notifications/role/chef_projet', [
            'title' => 'Coordination',
            'message' => 'Mise a jour de planning',
            'type' => 'role_announcement',
            'metadata' => [
                'priority' => 'high',
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'sent' => 2,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipientA->id,
            'type' => 'role_announcement',
            'title' => 'Coordination',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipientB->id,
            'type' => 'role_announcement',
            'title' => 'Coordination',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $admin->id,
            'type' => 'role_announcement',
            'title' => 'Coordination',
        ]);
    }
}
