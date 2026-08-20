<?php

namespace App\Shared\Traits;

use Illuminate\Support\Str;

/**
 * Adds a public, non-enumerable UUID to a model, used as the route-model
 * binding key so internal auto-increment IDs are never exposed in URLs.
 * Requires a `uuid` CHAR(36) UNIQUE column.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
