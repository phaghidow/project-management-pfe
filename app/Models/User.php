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
    const ROLE_CHEF_DEPT = 'chef_dept';
    const ROLE_CHEF_DEPARTEMENT = 'chef_departement';
    const ROLE_CHEF_PROJET = 'chef_projet';
    const ROLE_MEMBRE = 'membre';

    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';
    const STATUS_EN_ATTENTE = 'en_attente';

    protected $fillable = [
        'name',
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
        return in_array($this->role, [self::ROLE_CHEF_DEPT, self::ROLE_CHEF_DEPARTEMENT], true);
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
            self::ROLE_CHEF_DEPT, self::ROLE_CHEF_DEPARTEMENT => 'Chef de Département',
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
        $oldStatus = $this->status;
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        $this->logStatusChange($oldStatus, self::STATUS_ACTIVE, 'Activation manuelle');
    }

    public function deactivate(): void
    {
        $oldStatus = $this->status;
        $this->status = self::STATUS_DISABLED;
        $this->save();
        $this->logStatusChange($oldStatus, self::STATUS_DISABLED, 'Désactivation manuelle');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
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
}
