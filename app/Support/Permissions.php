<?php

namespace App\Support;

class Permissions
{
    public static function roleHasPermission(string $role, string $permission): bool
    {
        $permissions = config("emadrasah.permissions.{$role}", []);

        foreach ($permissions as $allowed) {
            if ($allowed === '*' || $allowed === $permission) {
                return true;
            }

            if (str_ends_with($allowed, '.*') && str_starts_with($permission, substr($allowed, 0, -1))) {
                return true;
            }
        }

        return false;
    }

    public static function rolePermissions(string $role): array
    {
        return config("emadrasah.permissions.{$role}", []);
    }
}
