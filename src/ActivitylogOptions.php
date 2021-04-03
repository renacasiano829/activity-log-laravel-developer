<?php

namespace Spatie\Activitylog;

use Closure;

class ActivitylogOptions
{
    public ?string $logName;

    public bool $submitEmptyLogs = true;

    public bool $logFillable = false;

    public bool $logOnlyDirty = false;

    public bool $logUnguarded = false;

    public array $logAttributes = [];

    public array $logExceptAttributes = [];

    public array $dontLogIfAttributesChangedBag = [];

    public ?Closure $descriptionForEvent;

    public static function defaults(): self
    {
        return new static;
    }

    public function logAll(): self
    {
        return $this->logOnly(['*']);
    }

    public function logUnguarded(): self
    {
        $this->logUnguarded = true;

        return $this;
    }

    public function logFillable(): self
    {
        $this->logFillable = true;

        return $this;
    }

    public function dontLogFillable(): self
    {
        $this->logFillable = false;

        return $this;
    }


    public function logOnlyDirty(): self
    {
        $this->logOnlyDirty = true;

        return $this;
    }

    public function logOnly(array $attributes): self
    {
        $this->logAttributes = $attributes;

        return $this;
    }

    public function logExcept(array $attributes): self
    {
        $this->logExceptAttributes = $attributes;

        return $this;
    }

    public function dontLogIfAttributesChangedOnly(array $attributes): self
    {
        $this->dontLogIfAttributesChangedBag = $attributes;

        return $this;
    }


    public function dontSubmitEmptyLogs(): self
    {
        $this->submitEmptyLogs = false;

        return $this;
    }

    public function useLogName(string $logname): self
    {
        $this->logName = $logname;

        return $this;
    }


    public function setDescriptionForEvent(Closure $callback): self
    {
        $this->descriptionForEvent = $callback;

        return $this;
    }
}
