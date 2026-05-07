<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Console\Command;

class DiagnoseCalendarAllUsers extends Command
{
    protected $signature = 'diagnose:calendar-all {--limit=5}';
    protected $description = 'Diagnose calendar for all users';

    public function handle()
    {
        $limit = $this->option('limit');
        $users = User::limit($limit)->get();

        foreach ($users as $user) {
            $this->line("=== {$user->name} (ID: {$user->id}, Rôle: {$user->role}) ===");

            $visibleTasks = Task::visibleFor($user)->count();
            $visibleMilestones = Milestone::visibleFor($user)->count();
            $visibleProjects = Project::visibleFor($user)->count();

            $this->line("  Tâches visibles: $visibleTasks");
            $this->line("  Jalons visibles: $visibleMilestones");
            $this->line("  Projets visibles: $visibleProjects");
            $this->line("  Rôle method check: " . ($user->isAdmin() ? "Admin" : ($user->isChefProjet() ? "Chef Projet" : ($user->isChefDepartement() ? "Chef Dept" : "Membre"))));
            $this->newLine();
        }

        $this->info("\n✅ Note: Utilisateurs non-admin/chef ne verront de données que s'ils sont assignés à des tâches ou font partie de projets.");
    }
}
