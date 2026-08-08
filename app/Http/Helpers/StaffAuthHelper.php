<?php

namespace App\Http\Helpers;

use App\Models\User\Staff;
use Illuminate\Support\Facades\Auth;

class StaffAuthHelper
{
    public static function isStaff(): bool
    {
        return Auth::guard('staff')->check();
    }

    public static function staff(): ?Staff
    {
        $staff = Auth::guard('staff')->user();

        if ($staff instanceof Staff) {
            $staff->loadMissing('roleInfo');
        }

        return $staff;
    }

    public static function owner()
    {
        return Auth::guard('web')->user();
    }

    public static function permissions(): array
    {
        if (!self::isStaff()) {
            return [];
        }

        $permissions = self::staff()?->roleInfo?->permissions;

        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }

        return is_array($permissions) ? $permissions : [];
    }

    public static function hasPermission(string $permission): bool
    {
        if (!self::isStaff()) {
            return true;
        }

        return in_array($permission, self::permissions(), true);
    }

    public static function hasAnyPermission(array $permissions): bool
    {
        if (!self::isStaff()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function displayName(): string
    {
        if (self::isStaff()) {
            return self::staff()?->name ?? 'Staff';
        }

        $owner = self::owner();

        return trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
    }

    public static function displayUsername(): string
    {
        if (self::isStaff()) {
            return self::staff()?->username ?? '';
        }

        return self::owner()?->username ?? '';
    }

    public static function displayEmail(): string
    {
        if (self::isStaff()) {
            return self::staff()?->email ?? '';
        }

        return self::owner()?->email ?? '';
    }
}
