<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes, HasAuditLogs, HasStatusHistory;

    protected $fillable = [
        'name',
        'project_id',
        'due_date'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function progressPercentage()
    {
        $total = $this->tasks()->count();
        return $total > 0 ? round(($this->tasks()->where('status', 'validated')->count() / $total) * 100, 1) : 0;
    }

    public function completedTasks()
    {
        return $this->tasks()->where('status', 'validated');
    }

    public static function visibleFor(User $user): Builder
    {
        if ($user->isAdmin()) {
            return self::query();
        }

        if ($user->isChefProjet()) {
            return self::whereHas('project', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        if ($user->isChefDepartement()) {
            $structureIds = \App\Models\Project::getStructureTreeIds($user->structure_id);

            return self::whereHas('project.user', function ($query) use ($structureIds) {
                $query->whereIn('structure_id', $structureIds);
            });
        }

        // Membres: voir les jalons des projets auxquels ils appartiennent
        return self::whereHas('project', function ($query) use ($user) {
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        });
    }
}

