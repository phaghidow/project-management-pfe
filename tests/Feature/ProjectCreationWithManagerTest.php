<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCreationWithManagerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a chef de département can create a project with a selected chef de projet
     */
    public function test_chef_departement_can_create_project_with_selected_manager()
    {
        // Create structure (department)
        $department = \App\Models\Structure::factory()->create();
        
        // Create chef de département
        $chefDept = User::factory()->create([
            'role' => User::ROLE_CHEF_DEPARTEMENT,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department->id,
        ]);

        // Create chef de projet in the same department
        $chefProjet = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department->id,
        ]);

        $this->actingAs($chefDept);

        $response = $this->post(route('projects.store'), [
            'name' => 'Test Project',
            'description' => 'A test project',
            'user_id' => $chefProjet->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
        ]);

        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'user_id' => $chefProjet->id,
        ]);
    }

    /**
     * Test that a chef de département cannot assign a chef de projet from another department
     */
    public function test_chef_departement_cannot_assign_manager_from_different_department()
    {
        // Create two departments
        $department1 = \App\Models\Structure::factory()->create();
        $department2 = \App\Models\Structure::factory()->create();

        // Create chef de département for department 1
        $chefDept1 = User::factory()->create([
            'role' => User::ROLE_CHEF_DEPARTEMENT,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department1->id,
        ]);

        // Create chef de projet in department 2
        $chefProjet2 = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department2->id,
        ]);

        $this->actingAs($chefDept1);

        $response = $this->post(route('projects.store'), [
            'name' => 'Test Project',
            'description' => 'A test project',
            'user_id' => $chefProjet2->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
        ]);

        // Should be forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that an admin can create a project with any chef de projet
     */
    public function test_admin_can_create_project_with_any_manager()
    {
        // Create two departments
        $department1 = \App\Models\Structure::factory()->create();
        $department2 = \App\Models\Structure::factory()->create();

        // Create admin
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        // Create chef de projet in department 2
        $chefProjet = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department2->id,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('projects.store'), [
            'name' => 'Test Project',
            'description' => 'A test project',
            'user_id' => $chefProjet->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
        ]);

        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'user_id' => $chefProjet->id,
        ]);
    }

    /**
     * Test that project creation requires user_id field
     */
    public function test_project_creation_requires_user_id()
    {
        $chefDept = User::factory()->create([
            'role' => User::ROLE_CHEF_DEPARTEMENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($chefDept);

        $response = $this->post(route('projects.store'), [
            'name' => 'Test Project',
            'description' => 'A test project',
            // Missing user_id
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
        ]);

        $response->assertSessionHasErrors('user_id');
    }

    /**
     * Test that edit form loads available project managers
     */
    public function test_edit_view_shows_available_managers()
    {
        // Create structure
        $department = \App\Models\Structure::factory()->create();

        // Create chef de département
        $chefDept = User::factory()->create([
            'role' => User::ROLE_CHEF_DEPARTEMENT,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department->id,
        ]);

        // Create chef de projet in same department
        $chefProjet = User::factory()->create([
            'role' => User::ROLE_CHEF_PROJET,
            'status' => User::STATUS_ACTIVE,
            'structure_id' => $department->id,
        ]);

        // Create a project
        $project = Project::factory()->create([
            'user_id' => $chefProjet->id,
        ]);

        $this->actingAs($chefDept);

        $response = $this->get(route('projects.edit', $project));

        $response->assertOk();
        $response->assertViewHas('availableProjectManagers');
        $response->assertSee($chefProjet->name);
    }
}
