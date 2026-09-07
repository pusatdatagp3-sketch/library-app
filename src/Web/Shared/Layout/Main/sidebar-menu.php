<?php

return [

    [
        'group' => 'Utama',
        'items' => [
            [
                'label' => 'Dashboard',
                'icon'  => 'ri-dashboard-3-line',
                'route' => 'home',
            ],
        ],
    ],

    [
        'group' => 'Perpustakaan',
        'items' => [
            [
                'label' => 'Scan Kunjungan',
                'icon'  => 'ri-barcode-box-line',
                'route' => 'perpustakaan/scan',
            ],
        ],
    ],

    [
        'group' => 'Sistem',
        'items' => [

            [
                'label'    => 'Generator',
                'icon'     => 'ri-code-box',

                'children' => [
                    [
                        'label'      => 'Gii Generator',
                        'icon'       => 'ri-magic',
                        'route'      => 'gii/index',
                        'permission' => 'manage_gii',
                    ],
                ],
            ],

            [
                'label'    => 'RBAC',
                'icon'     => 'ri-shield-user',

                'children' => [
                    [
                        'label'      => 'Users',
                        'icon'       => 'ri-user-settings',
                        'route'      => 'users/index',
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Roles',
                        'icon'       => 'ri-shield',
                        'route'      => 'roles/index',
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Permissions',
                        'icon'       => 'ri-key-2',
                        'route'      => 'permissions/index',
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Routes',
                        'icon'       => 'ri-compass-3',
                        'route'      => 'routes/index',
                        'permission' => 'manage_rbac',
                    ],
                ],
            ],
        ],
    ],

];