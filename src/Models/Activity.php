<?php

namespace Spatie\Activitylog\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Activity extends Eloquent
{
    protected $table = 'activity_log';

    public $guarded = [];

    protected $casts = [
        'properties' => 'collection',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the extra properties with the given name.
     *
     * @param string $propertyName
     *
     * @return mixed
     */
    public function getExtraProperty(string $propertyName)
    {
        return array_get($this->properties, $propertyName);
    }

    public function getChangesAttribute(): Collection
    {
        return $this->properties->filter(function ($value, $key) {
            return in_array($key, ['attributes', 'old']);
        });
    }
    
    public function scopeOnLog(Builder $query, ...$logNames): Builder
    {
        if (is_array($logNames[0])) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }
}
