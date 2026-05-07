<?php

namespace Tests\Feature;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\Structure;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCloseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_chef_departement_can_close_completed_project_in_own_structure(): void
    {
        $structure = Structure::factory()->create();

        $chefDepartement = User::factory()->create([
            'role' => User::ROLE_CHEF_DEPARTEMENT,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $structure->id,
        ]);

        $projectManager = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $structure->id,
        ]);

        $project = Project::factory()->create([
            'user_id' => $projectManager->id,
            'status' => 'in_progress',
        ]);

        $milestone = Milestone::factory()->create([
            'project_id' => $project->id,
        ]);

        Task::factory()->create([
            'milestone_id' => $milestone->id,
            'status' => 'validated',
        ]);

        $this->actingAs($chefDepartement)
            ->post(route('projects.close', $project))
            ->assertRedirect();

        $this->assertSame('completed', $project->fresh()->status);
    }

    public function test_chef_departement_cannot_close_project_outside_own_structure(): void
    {
        $ownStructure = Structure::factory()->create();
        $otherStructure = Structure::factory()->create();

        $chefDepartement = User::factory()->create([
            'role' => User::ROLE_CHEF_DEPARTEMENT,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $ownStructure->id,
        ]);

        $projectManager = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $otherStructure->id,
        ]);

        $project = Project::factory()->create([
            'user_id' => $projectManager->id,
            'status' => 'in_progress',
        ]);

        $milestone = Milestone::factory()->create([
            'project_id' => $project->id,
        ]);

        Task::factory()->create([
            'milestone_id' => $milestone->id,
            'status' => 'validated',
        ]);

        $this->actingAs($chefDepartement)
            ->post(route('projects.close', $project))
            ->assertForbidden();

        $this->assertSame('in_progress', $project->fresh()->status);
    }
}
