<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Structure;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Task;

class ProjectTestSeeder extends Seeder
{
    public function run(): void
    {
        // Assume admin and chef projet exist
        $chefProjet = User::whereHas('roles', function ($q) {
            $q->where('name', 'chef_projet');
        })->first();
        if (!$chefProjet) {
            $chefProjet = User::factory()->chefProjet()->create();
        }

        $project = Project::create([
            'name' => 'Test Projet Dépendances',
            'description' => 'Projet de test pour features dépendances et clôture',
            'user_id' => $chefProjet->id,
            'status' => 'in_progress',
        ]);

        $milestone = Milestone::create([
            'name' => 'Sprint Test',
            'project_id' => $project->id,
        ]);

        // Tasks for cycle test: A -> B -> C
        $taskA = Task::create([
            'name' => 'Tâche A (no dep)',
            'milestone_id' => $milestone->id,
        ]);

        $taskB = Task::create([
            'name' => 'Tâche B (dep A)',
            'milestone_id' => $milestone->id,
        ]);
        $taskB->dependencies()->attach($taskA);

        $taskC = Task::create([
            'name' => 'Tâche C (dep B)',
            'milestone_id' => $milestone->id,
            'start_date' => '2024-10-20',
            'end_date' => '2024-10-25',
        ]);
        $taskC->dependencies()->attach($taskB);

        $taskD = Task::create([
            'name' => 'Tâche D (dep C, test date)',
            'milestone_id' => $milestone->id,
            'start_date' => '2024-10-26', // After C end
        ]);
        $taskD->dependencies()->attach($taskC);

        // Test bad date would fail if added dep C to D with C end > D start
        // Test cycle if add D dep to A

        echo "Test data created:\n";
        echo "- Project: {$project->name} (ID {$project->id})\n";
        echo "- Tasks: A({$taskA->id}), B({$taskB->id}), C({$taskC->id}), D({$taskD->id})\n";
        echo "Test: Login as chef projet, edit B add dep A OK, add cycle error, date inconsistency error.\n";
        echo "Validate A,B,C → close project OK.\n";
    }
}
?>

