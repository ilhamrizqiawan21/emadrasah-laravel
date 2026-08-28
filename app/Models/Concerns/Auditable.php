<?php

namespace App\Models\Concerns;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    protected array $auditOriginalValues = [];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->writeAuditLog('created', null, $model->auditAttributes($model->getAttributes()));
        });

        static::updating(function (Model $model) {
            $dirty = $model->auditAttributes($model->getDirty());
            $model->auditOriginalValues = $model->onlyAuditKeys($model->getOriginal(), array_keys($dirty));
        });

        static::updated(function (Model $model) {
            $changes = $model->auditAttributes($model->getChanges());

            if ($changes === []) {
                return;
            }

            $model->writeAuditLog('updated', $model->auditOriginalValues, $changes);
        });

        static::deleted(function (Model $model) {
            $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                ? 'force_deleted'
                : 'deleted';

            $model->writeAuditLog($event, $model->auditAttributes($model->getOriginal()), null);
        });

        static::restored(function (Model $model) {
            $model->writeAuditLog('restored', null, $model->auditAttributes($model->getAttributes()));
        });
    }

    protected function writeAuditLog(string $event, ?array $oldValues, ?array $newValues): void
    {
        AuditLogger::logModelChange($this, $event, $oldValues, $newValues);
    }

    protected function auditAttributes(array $attributes): array
    {
        return AuditLogger::sanitizeModelAttributes($this, $attributes);
    }

    protected function onlyAuditKeys(array $attributes, array $keys): array
    {
        return AuditLogger::onlySanitizedKeys($this, $attributes, $keys);
    }
}
