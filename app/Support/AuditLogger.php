<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function logModelChange(
        Model $model,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        AuditLog::create([
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'user_id' => Auth::hasUser() ? Auth::user()->getAuthIdentifier() : null,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
        ]);
    }

    public static function sanitizeModelAttributes(Model $model, array $attributes): array
    {
        return collect($attributes)
            ->except(self::excludedAttributes($model))
            ->all();
    }

    public static function onlySanitizedKeys(Model $model, array $attributes, array $keys): array
    {
        return collect($attributes)
            ->only($keys)
            ->except(self::excludedAttributes($model))
            ->all();
    }

    private static function excludedAttributes(Model $model): array
    {
        return array_values(array_unique(array_merge(
            ['password', 'remember_token', 'created_at', 'updated_at'],
            method_exists($model, 'getHidden') ? $model->getHidden() : [],
            property_exists($model, 'auditExcept') ? $model->auditExcept : [],
        )));
    }
}
