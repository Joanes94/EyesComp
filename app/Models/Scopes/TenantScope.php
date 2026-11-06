<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();

            // Si l'utilisateur est un super-admin, il a accès à tout
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return;
            }

            if ($user->tenant_id) {
                $builder->where($model->qualifyColumn('tenant_id'), $user->tenant_id);
            }
        }
    }
}
