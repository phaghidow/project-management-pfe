<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Console\Command;

class DiagnoseCalendar extends Command
{
    protected $signature = 'diagnose:calendar';
    protected $description = 'Diagnose calendar visibility issues';

    public function handle()
    {
        $user = User::first();

        if (!$user) {
            $this->error('❌ Aucun utilisateur trouvé');
            return;
        }

        $this->line('=== DIAGNOSTIC CALENDRIER ===');
        $this->line("Utilisateur: {$user->name} (ID: {$user->id})");
        $this->line("Rôle: {$user->role}");
        $this->line("Structure: {$user->structure_id}");
        $this->newLine();

        // Totals
        $allTasks = Task::count();
        $visibleTasks = Task::visibleFor($user)->count();
        $this->line("Tâches totales: $allTasks");
        $this->line("Tâches visibles: $visibleTasks");

        if ($visibleTasks > 0) {
            $sample = Task::visibleFor($user)->first();
            $this->line("  → {$sample->name} ({$sample->start_date} → {$sample->end_date})");
        }
        $this->newLine();

        $allMilestones = Milestone::count();
        $visibleMilestones = Milestone::visibleFor($user)->count();
        $this->line("Jalons totaux: $allMilestones");
        $this->line("Jalons visibles: $visibleMilestones");

        if ($visibleMilestones > 0) {
            $sample = Milestone::visibleFor($user)->first();
            $this->line("  → {$sample->name} (due: {$sample->due_date})");
        }
        $this->newLine();

        $allProjects = Project::count();
        $visibleProjects = Project::visibleFor($user)->count();
        $this->line("Projets totaux: $allProjects");
        $this->line("Projets visibles: $visibleProjects");

        if ($visibleProjects > 0) {
            $sample = Project::visibleFor($user)->first();
            $this->line("  → {$sample->name}");
        }
        $this->newLine();

        $this->line("Rôles de l'utilisateur:");
        $this->line("  isAdmin(): " . ($user->isAdmin() ? "OUI" : "NON"));
        $this->line("  isChefProjet(): " . ($user->isChefProjet() ? "OUI" : "NON"));
        $this->line("  isChefDepartement(): " . ($user->isChefDepartement() ? "OUI" : "NON"));
        $this->newLine();

        $projectMemberships = $user->projects()->count();
        $taskAssignments = $user->tasks()->count();
        $this->line("Données de l'utilisateur:");
        $this->line("  Projets dont membre: $projectMemberships");
        $this->line("  Tâches assignées: $taskAssignments");

        if ($taskAssignments > 0) {
            $this->newLine();
            $this->line("Tâches assignées:");
            $user->tasks()->limit(5)->each(function ($t) {
                $this->line("  - {$t->name} ({$t->status}) [{$t->start_date} → {$t->end_date}]");
            });
        }
    }
}
