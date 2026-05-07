<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes, HasAuditLogs, HasStatusHistory;

    protected $fillable = [
        'name',
        'milestone_id',
        'start_date',
        'end_date',
        'due_date',
        'status',
        'validated_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'validated_at' => 'datetime',
    ];

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    // utilisateurs assignés
    public function users()
    {
        return $this->belongsToMany(User::class);
    }



    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    // dépendances (tâches dont dépend cette tâche)
    public function dependencies()
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'depends_on_task_id'
        );
    }

    // tâches dépendantes de celle-ci
    public function dependents()
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'depends_on_task_id',
            'task_id'
        );
    }

    // vérifier si toutes les dépendances sont validées
    public function canBeValidated()
    {
        return $this->dependencies()
        ->where('status', '!=', 'validated')
        ->count() === 0;
    }


    // vérifier si tout le projet est terminé
    public function checkProjectCompletion()
    {
        $project = $this->milestone->project;

        $remaining = $project->tasks()
            ->where('status', '!=', 'validated')
            ->count();

        if ($remaining === 0) {
            $project->status = 'completed';
            $project->save();
        }
    }

    public function validateTask($userId)
    {
        if ($this->status === 'validated') {
            throw new \Exception("Tâche déjà validée.");
        }

        if (!$this->users()->where('user_id', $userId)->exists()) {
            throw new \Exception("Non autorisé");
        }

        if (!$this->canBeValidated()) {
            throw new \Exception("Dépendances non terminées.");
        }

        $this->status = 'validated';
        $this->validated_at = now();
        $this->save();

        $this->updateProjectProgress();
        // Note: Notification is now sent via TaskStatusChanged event listener
    }

    public function updateProjectProgress()
    {
        $project = $this->milestone->project;

        $total = $project->tasks()->count();
        $done = $project->tasks()->where('status', 'validated')->count();

        $progress = $total > 0 ? ($done / $total) * 100 : 0;

        $project->progress = $progress;
        $latestEndDate = $project->tasks()->max('end_date');
        if ($latestEndDate) {
            $project->end_date = $latestEndDate;
        }
        $project->save();

        // Si tout est validé
        if ($progress == 100) {
            $project->status = 'completed';
            $project->save();

            // Notification au responsable du projet
\App\Services\NotificationService::projectReadyForReview(
                $project,
                $project->user_id
            );
        }
    }


    public function checkDeadline()
    {
        if (!$this->due_date || $this->status === 'validated') {
            return;
        }

        $daysLeft = $this->due_date->diffInDays(now(), false);
        $type = null;

        if ($daysLeft < 0) {
            $type = 'task_overdue';
        } elseif ($daysLeft <= 2 && $daysLeft > 0) {
            $type = 'task_due_soon';
        }

        if ($type) {
            foreach ($this->users as $user) {
                \App\Services\NotificationService::sendDeadlineAlert($this, $user, $type);
            }
        }
    }

    public function updateProjectEnd(): void
    {
        $project = $this->milestone->project;
        $latestEndDate = $project->tasks()->max('end_date');
        if ($latestEndDate) {
            $project->end_date = $latestEndDate;
            $project->save();
        }
    }

    public function addDependency(Task $dep): void
    {
        if ($this->is($dep)) {
            throw new \Exception('Cannot depend on self.');
        }

        if ($this->hasCycleWith($dep)) {
            throw new \Exception('Would create cycle.');
        }

        if ($dep->end_date && $this->start_date && $dep->end_date > $this->start_date) {
            throw new \Exception('Dependency end date must be before or equal to this task start date.');
        }

        $this->dependencies()->attach($dep);
        $this->updateProjectEnd();
    }

    public function removeDependency(Task $dep): void
    {
        $this->dependencies()->detach($dep);
        $this->updateProjectEnd();
    }

    private function hasCycleWith(Task $dep): bool
    {
        $visited = [];
        return $this->detectReachability($dep, $visited);
    }

    private function detectReachability(Task $target, array &$visited): bool
    {
        if (isset($visited[$this->id])) {
            return false;
        }

        $visited[$this->id] = true;

        if ($this->id === $target->id) {
            return true;
        }

        foreach ($this->dependents as $successor) {
            if ($successor->detectReachability($target, $visited)) {
                return true;
            }
        }

        return false;
    }

    public static function visibleFor(User $user): Builder
    {
        if ($user->isAdmin()) {
            return self::query();
        }

        if ($user->isChefProjet()) {
            return self::whereHas('milestone.project', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);

            return self::whereHas('milestone.project.user', function ($query) use ($structureIds) {
                $query->whereIn('structure_id', $structureIds);
            });
        }

        return self::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $task) {
            if ($task->milestone?->project) {
                $task->updateProjectEnd();
            }
        });

        static::updated(function (self $task) {
            if ($task->milestone?->project) {
                $task->updateProjectEnd();
            }
        });

        static::deleted(function (self $task) {
            if ($task->milestone?->project) {
                $task->updateProjectEnd();
            }
        });
    }

}
