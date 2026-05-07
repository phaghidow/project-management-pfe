<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestCalendarEndpoint extends Command
{
    protected $signature = 'test:calendar-endpoint {--user-id=1}';
    protected $description = 'Test calendar endpoint for a specific user';

    public function handle()
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ Utilisateur $userId non trouvé");
            return;
        }

        $this->line("=== TEST CALENDAR ENDPOINT ===");
        $this->line("Utilisateur: {$user->name} (ID: $userId)");
        $this->newLine();

        // Set the user as authenticated
        auth()->login($user);

        // Simulate calendar params (current month)
        $start = Carbon::now()->startOfMonth()->toIso8601String();
        $end = Carbon::now()->endOfMonth()->toIso8601String();

        $this->line("Plage testée: $start → $end");
        $this->newLine();

        // Test what visibleFor returns
        $visibleTasks = Task::visibleFor($user)->get();
        $visibleMilestones = Milestone::visibleFor($user)->get();
        $visibleProjects = Project::visibleFor($user)->get();

        $this->line("Tâches visibles (sans filtrage dates): " . $visibleTasks->count());
        $this->line("Jalons visibles (sans filtrage dates): " . $visibleMilestones->count());
        $this->line("Projets visibles (sans filtrage dates): " . $visibleProjects->count());
        $this->newLine();

        // Now test with date filtering (like the controller does)
        $rangeStart = Carbon::parse($start);
        $rangeEnd = Carbon::parse($end);

        $tasksWithFilter = Task::visibleFor($user)->where(function ($q) use ($rangeStart, $rangeEnd) {
            $q->where(function ($q2) use ($rangeStart, $rangeEnd) {
                $q2->whereNotNull('start_date')->whereNotNull('end_date')
                    ->where('start_date', '<=', $rangeEnd)
                    ->where('end_date', '>=', $rangeStart);
            })->orWhere(function ($q3) use ($rangeStart, $rangeEnd) {
                $q3->whereNotNull('due_date')
                    ->whereBetween('due_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
            });
        })->get();

        $this->line("Tâches AVEC filtrage dates: " . $tasksWithFilter->count());

        if ($tasksWithFilter->count() > 0) {
            $this->line("\nPremière tâche:");
            $t = $tasksWithFilter->first();
            $this->line("  Nom: {$t->name}");
            $this->line("  Start: {$t->start_date} End: {$t->end_date}");
        }

        $milestonesWithFilter = Milestone::visibleFor($user)->whereBetween('due_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])->get();
        $this->line("\nJalons AVEC filtrage dates: " . $milestonesWithFilter->count());

        if ($milestonesWithFilter->count() > 0) {
            $this->line("\nPremier jalon:");
            $m = $milestonesWithFilter->first();
            $this->line("  Nom: {$m->name}");
            $this->line("  Due: {$m->due_date}");
        }

        // Count events that would be returned
        $eventCount = $tasksWithFilter->count() + $milestonesWithFilter->count() + $visibleProjects->count();
        $this->line("\n📊 Total d'événements: $eventCount");

        // Also check what dates we actually have in the database
        $this->newLine();
        $this->line("=== DATES DANS LA BASE ===");
        $allTaskDates = Task::visibleFor($user)->whereNotNull('start_date')->get(['start_date', 'end_date'])->take(5);
        $this->line("Exemple de dates de tâches (les 5 premières):");
        $allTaskDates->each(function ($t) {
            $this->line("  {$t->start_date} → {$t->end_date}");
        });

        $this->newLine();
        $allMilestoneDates = Milestone::visibleFor($user)->whereNotNull('due_date')->get(['due_date'])->take(5);
        $this->line("Exemple de dates de jalons (les 5 premières):");
        $allMilestoneDates->each(function ($m) {
            $this->line("  {$m->due_date}");
        });
    }
}
