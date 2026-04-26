<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, HasAuditLogs, HasStatusHistory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'start_date',
        'end_date',
        'status'
    ];

    // responsable
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // milestones
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    // tasks via milestones
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, Milestone::class);
    }



    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    public function completedTasks()
    {
        return $this->tasks()->where('status', 'validated');
    }

    public function progressPercentage()
    {
        $total = $this->tasks()->count();
        return $total > 0 ? round(($this->tasks()->where('status', 'validated')->count() / $total) * 100, 1) : 0;
    }

    public static function visibleFor($user): Builder
    {
        // ADMIN → voit tout
        if ($user->isAdmin()) {
            return self::query();
        }

        // CHEF PROJET → ses projets
        if ($user->isChefProjet()) {
            return self::where('user_id', $user->id);
        }

        // CHEF DEPARTEMENT → projets de sa structure
        if ($user->isChefDepartement()) {

            // récupérer structures descendantes
            $structureIds = self::getStructureTreeIds($user->structure_id);

            return self::whereHas('user', function ($q) use ($structureIds) {
                $q->whereIn('structure_id', $structureIds);
            });
        }

        return self::query()->whereRaw('0 = 1'); // sécurité
    }

    public static function getStructureTreeIds($structureId)
    {
        $ids = [$structureId];

        $children = \App\Models\Structure::where('parent_id', $structureId)->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, self::getStructureTreeIds($child->id));
        }

        return $ids;
    }

    public function closeProject(): void
    {
        if ($this->status === 'closed') {
            throw new \Exception('Projet déjà clôturé.');
        }

        $unvalidatedCount = $this->tasks()->where('status', '!=', 'validated')->count();
        if ($unvalidatedCount > 0) {
            throw new \Exception("Impossible de clôturer : {$unvalidatedCount} tâches non validées.");
        }

        $this->status = 'closed';
        $this->save();

        // Notify chef de projet (self user)
        \App\Services\NotificationService::send(
            $this->user_id,
            'Projet clôturé',
            "Le projet '{$this->name}' a été clôturé par le chef de projet.",
            'project_closed',
            'project',
            $this->id
        );
    }
}

