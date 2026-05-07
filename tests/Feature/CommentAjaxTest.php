<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_comment_returns_422_and_errors()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => User::STATUS_ACTIVE]);
        $task = Task::factory()->create();

        $this->actingAs($user)
            ->postJson('/comments', [
                'task_id' => $task->id,
                'content' => '',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_successful_comment_returns_201_and_flash_header()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => User::STATUS_ACTIVE]);
        $task = Task::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/comments', [
                'task_id' => $task->id,
                'content' => 'Un commentaire de test valide',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'comment']);

        $this->assertTrue($response->headers->has('X-Flash-Success'));
    }
}
