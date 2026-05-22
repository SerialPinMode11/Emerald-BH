<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case LandOwner = 'land_owner';
    case Community = 'community';
    case SuperAdmin = 'super_admin';
    case DevAdmin = 'dev_admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::LandOwner => 'Land Owner',
            self::Community => 'Community Mediator',
            self::SuperAdmin => 'Super Admin',
            self::DevAdmin => 'Dev Admin',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Customer => 'customer.explore',
            self::LandOwner => 'land-owner.dashboard',
            self::Community => 'community.portal',
            self::SuperAdmin => 'super-admin.dashboard',
            self::DevAdmin => 'dev-admin.dashboard',
        };
    }

    /**
     * @return array{primary: string, secondary: string, accent: string}
     */
    public function theme(): array
    {
        return match ($this) {
            self::Customer => ['primary' => '#2C7DA0', 'secondary' => '#E9F5F9', 'accent' => '#F6AE6D'],
            self::LandOwner => ['primary' => '#1A5F4A', 'secondary' => '#E8F3EE', 'accent' => '#D96C3A'],
            self::Community => ['primary' => '#5E4B8C', 'secondary' => '#F2EFF9', 'accent' => '#F4A261'],
            self::SuperAdmin => ['primary' => '#C44536', 'secondary' => '#FDF2F0', 'accent' => '#E9C46A'],
            self::DevAdmin => ['primary' => '#264653', 'secondary' => '#E9ECEF', 'accent' => '#E76F51'],
        };
    }
}
