<?php

namespace App\Models;

use App\Traits\HasAuditLogs;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}