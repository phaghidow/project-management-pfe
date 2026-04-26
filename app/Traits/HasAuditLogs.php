<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLogs
{
    protected static function bootHasAuditLogs(): void
    {
        static::saving(function (Model $model) {
            if ($model->exists) {
                // Detect specific action type before generic update
                $action = static::getActionType($model);
                static::logChange($model, $action ?: 'update');
            }
        });

        static::saved(function (Model $model) {
            if (!$model->wasRecentlyCreated && !$model->exists || !isset(static::$ignoreAudit)) {
                static::logChange($model, 'create');
            }
        });

        static::deleting(function (Model $model) {
            static::logChange($model, 'delete');
        });
    }

    /**
     * Detect specific action types like status_change, validation
     */
    protected static function getActionType(Model $model): ?string
    {
        $old = $model->getRawOriginal();
        $new = $model->getAttributes();

        // Status change
        if (isset($old['status']) && isset($new['status']) && $old['status'] !== $new['status']) {
            return 'status_change';
        }

        // Task validation (validated_at set)
        if (isset($new['validated_at']) && $new['validated_at'] && (!isset($old['validated_at']) || !$old['validated_at'])) {
            return 'validation';
        }

        // Project close/status change
        if (isset($new['status']) && $new['status'] === 'closed') {
            return 'project_closed';
        }

        return null;
    }

    public static function logAction(Model $model, string $action, array $context = []): void
    {
        AuditLog::create([
            'entity_type' => $model->getTable(),
            'entity_id' => $model->getKey(),
            'action' => $action,
            'actor_id' => Auth::id(),
            'old_data' => request()->has('old_data') ? request('old_data') : null,
            'new_data' => request()->has('new_data') ? request('new_data') : null,
            'technical_context' => [
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::url(),
                'method' => Request::method(),
                ...$context,
            ],
        ]);
    }

    protected static function logChange(Model $model, string $action): void
    {
        $oldData = $model->getRawOriginal();
        $newData = $model->getAttributes();

        // Clean data
        $cleanKeys = ['updated_at', 'created_at', $model->getKeyName()];
        foreach ($cleanKeys as $key) {
            unset($oldData[$key], $newData[$key]);
        }

        // Add human-readable summary to context for timeline
        $context = [
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::url(),
            'summary' => static::generateChangeSummary($oldData, $newData, $action),
        ];

        AuditLog::create([
            'entity_type' => $model->getTable(),
            'entity_id' => $model->getKey(),
            'action' => $action,
            'actor_id' => Auth::id(),
            'old_data' => !empty($oldData) ? $oldData : null,
            'new_data' => $newData,
            'technical_context' => $context,
        ]);
    }

    /**
     * Generate human-readable change summary for timeline display
     */
    protected static function generateChangeSummary(array $old, array $new, string $action): string
    {
        $changes = [];

        $importantFields = ['name', 'status', 'description', 'start_date', 'end_date', 'due_date', 'progress'];

        foreach ($importantFields as $field) {
            if (isset($old[$field], $new[$field]) && $old[$field] !== $new[$field]) {
                $oldVal = is_string($old[$field]) && strlen($old[$field]) > 50 ? substr($old[$field], 0, 50) . '...' : $old[$field];
                $newVal = is_string($new[$field]) && strlen($new[$field]) > 50 ? substr($new[$field], 0, 50) . '...' : $new[$field];
                $label = match($field) {
                    'name' => 'nom',
                    'status' => 'statut',
                    'description' => 'description',
                    'start_date', 'end_date', 'due_date' => 'date',
                    'progress' => 'progression',
                    default => $field
                };
                $changes[] = ucfirst($label) . ': "' . $oldVal . '" → "' . $newVal . '"';
            }
        }

        if (empty($changes)) {
            $changes[] = match($action) {
                'status_change' => 'statut modifié',
                'validation' => 'élément validé',
                'project_closed' => 'projet clôturé',
                default => 'changements effectués'
            };
        }

        return implode('; ', $changes);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'entity');
    }
}

