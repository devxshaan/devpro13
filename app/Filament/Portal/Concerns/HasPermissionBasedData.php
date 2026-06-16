<?php

namespace App\Filament\Portal\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPermissionBasedData
{
    // ❌ Properties yahan se hatao — class mein define hongi
    // protected string $viewOwnPermission = '';
    // protected string $viewAnyPermission = '';

    protected function scopeQuery(Builder $query): Builder
    {
        $user = auth()->user();

        $viewAny = $this->viewAnyPermission ?? '';
        $viewOwn = $this->viewOwnPermission ?? '';

        if ($viewAny && $user->can($viewAny)) {
            return $query;
        }

        if ($viewOwn && $user->can($viewOwn)) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        $page = new static();

        $viewAny = $page->viewAnyPermission ?? '';
        $viewOwn = $page->viewOwnPermission ?? '';

        return ($viewOwn && $user?->can($viewOwn))
            || ($viewAny && $user?->can($viewAny));
    }
}