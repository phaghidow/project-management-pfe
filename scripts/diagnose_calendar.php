<?php
$projectRoot = realpath(__DIR__ . '/..');
require $projectRoot . '/vendor/autoload.php';
$app = require $projectRoot . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate authentication
$user = \App\Models\User::first();

if (!$user) {
    echo "❌ Aucun utilisateur trouvé dans la base de données\n";
    return;
}

echo "=== DIAGNOSTIC CALENDRIER ===\n";
echo "Utilisateur: {$user->name} (ID: {$user->id})\n";
echo "Rôle: {$user->role}\n";
echo "Structure: {$user->structure_id}\n";
echo "\n";

// Total tasks
$allTasks = \App\Models\Task::count();
$visibleTasks = \App\Models\Task::visibleFor($user)->count();
echo "Tâches totales: $allTasks\n";
echo "Tâches visibles pour cet utilisateur: $visibleTasks\n";

if ($visibleTasks > 0) {
    $sample = \App\Models\Task::visibleFor($user)->first();
    echo "  → Premier exemple: {$sample->name}\n";
    echo "    Dates: {$sample->start_date} → {$sample->end_date}\n";
    echo "    Assigné à: " . $sample->users()->count() . " utilisateur(s)\n";
}
echo "\n";

// Total milestones
$allMilestones = \App\Models\Milestone::count();
$visibleMilestones = \App\Models\Milestone::visibleFor($user)->count();
echo "Jalons totaux: $allMilestones\n";
echo "Jalons visibles pour cet utilisateur: $visibleMilestones\n";

if ($visibleMilestones > 0) {
    $sample = \App\Models\Milestone::visibleFor($user)->first();
    echo "  → Premier exemple: {$sample->name}\n";
    echo "    Due date: {$sample->due_date}\n";
}
echo "\n";

// Total projects
$allProjects = \App\Models\Project::count();
$visibleProjects = \App\Models\Project::visibleFor($user)->count();
echo "Projets totaux: $allProjects\n";
echo "Projets visibles pour cet utilisateur: $visibleProjects\n";

if ($visibleProjects > 0) {
    $sample = \App\Models\Project::visibleFor($user)->first();
    echo "  → Premier exemple: {$sample->name}\n";
}
echo "\n";

// User roles check
echo "Vérification des rôles:\n";
echo "  isAdmin(): " . ($user->isAdmin() ? "OUI" : "NON") . "\n";
echo "  isChefProjet(): " . ($user->isChefProjet() ? "OUI" : "NON") . "\n";
echo "  isChefDepartement(): " . ($user->isChefDepartement() ? "OUI" : "NON") . "\n";
echo "\n";

// User assignments
$projectMemberships = $user->projects()->count();
$taskAssignments = $user->tasks()->count();
echo "Données de l'utilisateur:\n";
echo "  Projets dont membre: $projectMemberships\n";
echo "  Tâches assignées: $taskAssignments\n";
echo "\n";

// Sample tasks assigned
if ($taskAssignments > 0) {
    echo "Tâches assignées à cet utilisateur:\n";
    $userTasks = $user->tasks()->limit(3)->get();
    foreach ($userTasks as $t) {
        echo "  - {$t->name} ({$t->status})\n";
        echo "    Dates: {$t->start_date} → {$t->end_date}\n";
    }
}

return 0;
