<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use SoftDeletes, HasAuditLogs, HasStatusHistory;

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

        return self::query()->whereRaw('0 = 1'); // sécurité
    }
}

