<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Model;

trait HasActivityLog
{
    abstract protected function activityLogName(): string;

    protected static function bootHasActivityLog(): void
    {
        static::created(function (self $model): void {
            $model->logModelActivity('created', $model->attributesToArray());
        });

        static::updated(function (self $model): void {
            $changes = $model->getChanges();

            if ($changes === []) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $changes);

            $model->logModelActivity('updated', $changes, $old);
        });

        static::deleted(function (self $model): void {
            $model->logModelActivity('deleted', $model->attributesToArray());
        });
    }

    protected function logModelActivity(string $eventName, array $attributes, array $old = []): void
    {
        if (! function_exists('activity')) {
            return;
        }

        activity($this->activityLogName())
            ->performedOn($this)
            ->event($eventName)
            ->withChanges([
                'attributes' => $attributes,
                'old' => $old,
            ])
            ->tap(fn (Activity $activity): mixed => $this->tapActivity($activity, $eventName))
            ->log($eventName);
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if (! app()->bound('request')) {
            return;
        }

        $activity->properties = $activity->properties
            ->put('ip', request()->ip())
            ->put('device', request()->userAgent());
    }
}
