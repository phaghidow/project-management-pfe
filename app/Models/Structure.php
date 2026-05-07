<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Structure extends Model
{
use SoftDeletes, HasFactory, HasAuditLogs, HasStatusHistory;

    protected $fillable = [
        'name',
        'type',
        'code',
        'parent_id',
        'level',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT - Gestion automatique du niveau
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        // Utilisation de saving pour capturer les créations ET les mises à jour
        static::saving(function ($structure) {
            if ($structure->parent_id) {
                $parent = self::find($structure->parent_id);
                $structure->level = $parent ? $parent->level + 1 : 0;
            } else {
                $structure->level = 0;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Relation parent (hiérarchie)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'parent_id');
    }

    /**
     * Relation enfants (avec chargement récursif pour l'arbre)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Structure::class, 'parent_id')
            ->with('children'); // Eager loading récursif
    }

    /**
     * Utilisateurs rattaches a cette structure.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'structure_id');
    }

    /*
    |--------------------------------------------------------------------------
    | TREE METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Récupérer l'arbre complet depuis les racines
     */
    public static function tree()
    {
        return self::with('children')
            ->whereNull('parent_id')
            ->get();
    }

    /**
     * Alias de compatibilite pour les anciens appels du controleur.
     */
    public static function getHierarchyTree()
    {
        return self::tree();
    }

    /*
    |--------------------------------------------------------------------------
    | PATH (UI / Breadcrumb)
    |--------------------------------------------------------------------------
    */

    /**
     * Récupère le chemin sous forme de tableau d'objets
     */
    public function getPathArray()
    {
        $path = [];
        $current = $this;

        while ($current) {
            $path[] = $current;
            $current = $current->parent;
        }

        return array_reverse($path);
    }

    /**
     * Accesseur pour le chemin textuel (ex: DG > DSI > Bureau)
     */
    public function getHierarchyPathAttribute(): string
    {
        return collect($this->getPathArray())
            ->pluck('name')
            ->implode(' > ');
    }

    /**
     * Transforme la structure en tableau hiérarchique pour l'API
     * Format: id, nom, parent_id, enfants[]
     */
    public function toTreeArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->name,
            'parent_id' => $this->parent_id,
            'users' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'function' => $user->function,
                    'role' => $user->role,
                ];
            })->toArray(),
            'enfants' => $this->children->map(function ($child) {
                return $child->toTreeArray();
            })->toArray(),
        ];
    }

    /**
     * Verifie si la structure courante est descendante de la structure fournie.
     */
    public function isDescendantOf($structure): bool
    {
        if (!$structure) {
            return false;
        }

        $targetId = $structure instanceof self
            ? (int) $structure->id
            : (is_numeric($structure) ? (int) $structure : null);

        if (!$targetId) {
            return false;
        }

        $current = $this->parent;

        while ($current) {
            if ((int) $current->id === $targetId) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    /**
     * Retourne toutes les sous-structures de maniere recursive.
     */
    public function getDescendantsAttribute(): Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants);
        }

        return $descendants;
    }
}
