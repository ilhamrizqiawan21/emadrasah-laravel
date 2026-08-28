<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
   public function boot(): void
{
    Paginator::useBootstrapFive(); // ← ini yang paling krusial

    foreach ($this->configuredPermissions() as $permission) {
        Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
    }
}

    private function configuredPermissions(): array
    {
        $permissions = collect(config('emadrasah.permissions', []))
            ->flatten()
            ->reject(fn (string $permission) => $permission === '*')
            ->flatMap(function (string $permission) {
                if (!str_ends_with($permission, '.*')) {
                    return [$permission];
                }

                $module = substr($permission, 0, -2);

                return collect(['view', 'create', 'update', 'delete', 'export'])
                    ->map(fn (string $action) => "{$module}.{$action}");
            })
            ->unique()
            ->values()
            ->all();

        return $permissions;
    }
}
