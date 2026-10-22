<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules =
            [
                [
                    'name' => 'Dashboard',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Guideline',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Awareness',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Spot',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Hotel',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'show rating',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'HotelType',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'HotelCategory',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Restaurant',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'show rating',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'RestaurantCategory',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Transport',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Event',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Campaign',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],

                [
                    'name' => 'Info',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Locker',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Bank',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Atm',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'DangerZone',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'ZoneCategory',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'TouristBus',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'KitkotChair',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Fare',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Complain',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'ComplainCategory',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Emergency',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'DATourismCell',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Police',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Doctor',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Guard',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Volunteer',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'TourismCorporation',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Announcement',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'News',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'TouristList',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Settings',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'User',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Notice',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'LandingPage',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],

                [
                    'name' => 'Top Spot',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Spot Rating',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Tour Guide',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'show rating',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Tour Operator',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'show rating',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
                [
                    'name' => 'Association',
                    'permissions' => [
                        [
                            'name' => 'show menu',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'create',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'edit',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'update',
                            'guard_name' => 'web',
                        ],
                        [
                            'name' => 'delete',
                            'guard_name' => 'web',
                        ]
                    ],
                ],
            ];
        DB::beginTransaction();
        try {
            foreach ($modules as $module) {
                $module_id = Module::firstOrCreate([
                    'name' => $module['name']
                ])->id;

                if (count($module['permissions']) > 0) {
                    foreach ($module['permissions'] as $permission) {
                        $permission['module_id'] = $module_id;
                        $permission['name'] = strtolower($module['name']) . '-' . strtolower($permission['name']);
                        Permission::firstOrCreate($permission);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }
    }
}
