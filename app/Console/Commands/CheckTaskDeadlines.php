<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task; // Crucial pour que Task::all() fonctionne

class CheckTaskDeadlines extends Command
{
    /**
     * La signature (le nom de la commande)
     * Utilisation : php artisan check:task-deadlines
     */
    protected $signature = 'check:task-deadlines {--dry-run : Show preview without sending notifications} {--overdue-only : Only check overdue tasks}';

    /**
     * La description de la commande
     */
    protected $description = 'Vérifie les échéances des tâches et envoie des notifications pour les retards (with anti-duplication)';

    /**
     * Logique de la commande
     */
    public function handle()
    {
        $query = Task::whereNotNull('due_date')
            ->where('status', '!=', 'validated');
        
        if ($this->option('overdue-only')) {
            $query->where('due_date', '<', now());
        }

        $tasks = $query->get();
        $sentCount = 0;
        $skippedCount = 0;

        $this->info("Checking {$tasks->count()} tasks...");

        $tasks->each(function ($task) use (&$sentCount, &$skippedCount) {
            $daysLeft = $task->due_date->diffInDays(now(), false);
            $type = null;
            
            if ($daysLeft < 0) {
                $type = 'task_overdue';
            } elseif ($daysLeft <= 2) {
                $type = 'task_due_soon';
            }

            if ($type) {
                foreach ($task->users as $user) {
                    if ($this->option('dry-run')) {
                        $this->warn("DRY-RUN: Would send {$type} to {$user->name} for task '{$task->name}' (due: {$task->due_date->format('Y-m-d')})");
                        $skippedCount++;
                    } else {
                        $result = \App\Services\NotificationService::sendDeadlineAlert($task, $user, $type);
                        if ($result) {
                            $sentCount++;
                            $this->line("Sent {$type} to {$user->name} for '{$task->name}'");
                        } else {
                            $skippedCount++;
                            $this->warn("Skipped duplicate {$type} for {$user->name} / '{$task->name}'");
                        }
                    }
                }
            }
        });

        $this->newLine();
        $this->info("Completed: Sent {$sentCount}, Skipped {$skippedCount} notifications.");
    }
}