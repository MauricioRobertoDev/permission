<?php

namespace MrDev\Permission\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;

class GuardHelper
{
    public static function getPossibleGuards(Model|string $model): Collection
    {
        $class = is_object($model) ? get_class($model) : $model;

        if (is_object($model)) {
            if (method_exists($model, 'guardName')) {
                $guardName = $model->guardName();
            } else {
                $guardName = $model->guard_name ?? null;
            }
        }

        if (! isset($guardName)) {
            $guardName = (new ReflectionClass($class))->getDefaultProperties()['guard_name'] ?? null;
        }

        if ($guardName) {
            return collect($guardName);
        }

        return self::getPossibleGuardsByAuthConfig($class);
    }

    protected static function getPossibleGuardsByAuthConfig(string $class): Collection
    {
        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return null;
                }

                return config("auth.providers.{$guard['provider']}.model");
            })
            ->filter(function ($model) use ($class) {
                return $class === $model;
            })
            ->keys();
    }

    public static function getGuardNameFor(Model|string $model): string
    {
        $default = config('auth.defaults.guard');

        $possible_guards = self::getPossibleGuards($model);

        if ($possible_guards->contains($default)) {
            return $default;
        }

        return $possible_guards->first() ?: $default;
    }

    public static function getModelForGuard(string $guard): string
    {
        return (string) collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return;
                }

                return config("auth.providers.{$guard['provider']}.model");
            })
            ->get($guard);
    }
}
