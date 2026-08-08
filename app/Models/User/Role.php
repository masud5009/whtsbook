<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\User\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public const PERMISSION_GROUPS = [
        'Core Access' => [
            [
                'permission' => 'Dashboard',
                'label' => 'Dashboard',
            ],
            [
                'permission' => 'Credit Recharge History',
                'label' => 'Credit Recharge History',
            ],
            [
                'permission' => 'Membership',
                'label' => 'Membership',
            ],
        ],
        'WhatsApp' => [
            [
                'permission' => 'Connect With Whatsapp',
                'label' => 'Connect With Whatsapp',
            ],
            [
                'permission' => 'Failed Messages',
                'label' => 'Failed Messages',
            ],
        ],
        'Hotel Operations' => [
            [
                'permission' => 'Rooms Management',
                'label' => 'Rooms Management',
            ],
            [
                'permission' => 'Room Bookings',
                'label' => 'Room Bookings',
                'children' => [
                    [
                        'permission' => 'Room Bookings Payment Link',
                        'label' => 'Payment Link Send',
                    ],
                    [
                        'permission' => 'Room Bookings Stay Status',
                        'label' => 'Stay Status Update',
                    ],
                    [
                        'permission' => 'Room Bookings Payment Status',
                        'label' => 'Payment Status Update',
                    ],
                    [
                        'permission' => 'Room Bookings Refund Status',
                        'label' => 'Refund Status Update',
                    ],
                    [
                        'permission' => 'Room Bookings Booking Status',
                        'label' => 'Booking Status Update',
                    ],
                    [
                        'permission' => 'Room Bookings Details',
                        'label' => 'Details',
                    ],
                    [
                        'permission' => 'Room Bookings Edit',
                        'label' => 'Edit',
                    ],
                    [
                        'permission' => 'Room Bookings Send Mail',
                        'label' => 'Send Mail',
                    ],
                    [
                        'permission' => 'Room Bookings WhatsApp Message',
                        'label' => 'Message on WhatsApp',
                    ],
                    [
                        'permission' => 'Room Bookings Delete',
                        'label' => 'Delete',
                    ],
                ],
            ],
        ],
        'Train AI Assistant' => [
            [
                'permission' => 'Train AI Assistant',
                'label' => 'Train AI Assistant',
            ],
        ],
        'Staff Management' => [
            [
                'permission' => 'Roles & Permissions',
                'label' => 'Roles & Permissions',
            ],
            [
                'permission' => 'Staffs',
                'label' => 'Staffs',
            ],
        ],
        'Settings' => [
            [
                'permission' => 'General Settings',
                'label' => 'General Settings',
            ],
            [
                'permission' => 'Payment Gateways',
                'label' => 'Payment Gateways',
            ],
            [
                'permission' => 'Languages',
                'label' => 'Languages',
            ],
            [
                'permission' => 'Email Settings',
                'label' => 'Email Settings',
            ],
            [
                'permission' => 'Profile',
                'label' => 'Profile',
            ],
            [
                'permission' => 'Change Password',
                'label' => 'Change Password',
            ],
        ],
        'Other Modules' => [
            [
                'permission' => 'Support Tickets',
                'label' => 'Support Tickets',
            ],
            [
                'permission' => 'QR Codes',
                'label' => 'QR Codes',
            ],
        ],
    ];

    public static function permissionGroups(): array
    {
        return self::PERMISSION_GROUPS;
    }

    public static function permissionList(): array
    {
        return collect(self::PERMISSION_GROUPS)
            ->flatMap(function (array $items) {
                return self::flattenPermissionItems($items);
            })
            ->unique()
            ->values()
            ->all();
    }

    public static function dependentPermissionParents(): array
    {
        return collect(self::PERMISSION_GROUPS)
            ->flatMap(function (array $items) {
                return self::flattenPermissionDependencies($items);
            })
            ->all();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class, 'role');
    }

    private static function flattenPermissionItems(array $items): array
    {
        $permissions = [];

        foreach ($items as $item) {
            $permissions[] = $item['permission'];

            if (!empty($item['children'])) {
                $permissions = array_merge($permissions, self::flattenPermissionItems($item['children']));
            }
        }

        return $permissions;
    }

    private static function flattenPermissionDependencies(array $items, ?string $parent = null): array
    {
        $dependencies = [];

        foreach ($items as $item) {
            if (!empty($parent)) {
                $dependencies[$item['permission']] = $parent;
            }

            if (!empty($item['children'])) {
                $dependencies += self::flattenPermissionDependencies($item['children'], $item['permission']);
            }
        }

        return $dependencies;
    }
}
