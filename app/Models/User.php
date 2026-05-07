<?php

namespace App\Models;

use App\Models\Structure;
use App\Models\Project;
use App\Models\Task;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasAuditLogs, HasStatusHistory, SoftDeletes;

    const ROLE_ADMIN = 'admin';
    const ROLE_CHEF_DEPARTEMENT = 'chef_departement';
    const ROLE_CHEF_DEPT = self::ROLE_CHEF_DEPARTEMENT;
    const ROLE_CHEF_PROJET = 'chef_projet';
    const ROLE_MEMBRE = 'membre';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CHEF_DEPARTEMENT,
        self::ROLE_CHEF_PROJET,
        self::ROLE_MEMBRE,
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';
    const STATUS_EN_ATTENTE = 'en_attente';

    protected $fillable = [
        'name',
        'function',
        'email',
        'password',
        'username',
        'role',
        'structure_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function setRoleAttribute($value): void
    {
        if ($value === 'chef_dept') {
            $value = self::ROLE_CHEF_DEPARTEMENT;
        }

        $this->attributes['role'] = $value;
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isChefProjet()
    {
        return $this->role === self::ROLE_CHEF_PROJET;
    }

    public function isChefDepartement()
    {
        return $this->role === self::ROLE_CHEF_DEPARTEMENT;
    }

    public function isMembre()
    {
        return $this->role === self::ROLE_MEMBRE;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_CHEF_DEPARTEMENT => 'Chef de Département',
            self::ROLE_CHEF_PROJET => 'Chef de Projet',
            self::ROLE_MEMBRE => 'Membre',
            default => ucfirst($this->role),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_EN_ATTENTE => 'orange',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_DISABLED => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_EN_ATTENTE => 'En attente',
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_DISABLED => 'Désactivé',
            default => ucfirst($this->status),
        };
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->pendingStatusChangeReason = 'Activation manuelle';
        $this->save();
    }

    public function deactivate(): void
    {
        $this->status = self::STATUS_DISABLED;
        $this->pendingStatusChangeReason = 'Désactivation manuelle';
        $this->save();
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function assignedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role_in_project', 'assigned_at')
            ->withTimestamps();
    }

    public function structure()
    {
        return $this->belongsTo(Structure::class);
    }

    public function getParentStructureAttribute()
    {
        return $this->structure?->parent;
    }

    public function getStructureHierarchyAttribute()
    {
        return $this->structure?->hierarchy_path ?? 'Aucune structure';
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function notificationPreferences()
    {
        // Notification preferences feature removed — return empty collection to preserve callers
        return collect();
    }
}

