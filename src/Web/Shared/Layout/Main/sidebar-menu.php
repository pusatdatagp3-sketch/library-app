<?php

return [

    [
        'group' => 'Utama',
        'icon'  => 'ri-compass-3-line',
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
        'icon'  => 'ri-book-open-line',
        'items' => [
            [
                'label' => 'Scan Kunjungan',
                'icon'  => 'ri-qr-scan-2-line',
                'route' => 'perpustakaan/scan',
            ],
            [
                'label' => 'Rekap Kunjungan',
                'icon'  => 'ri-file-chart-line',
                'route' => 'perpustakaan/rekap',
            ],
        ],
    ],

    [
        'group' => 'Sistem',
        'icon'  => 'ri-settings-4-line',
        'items' => [

            [
                'label' => 'Manajemen Staf',
                'icon'  => 'ri-user-star-line',
                'route' => 'staf/index',
                'role'  => 'super-admin',
            ],

            [
                'label'    => 'Generator',
                'icon'     => 'ri-code-box-line',

                'children' => [
                    [
                        'label'      => 'Gii Generator',
                        'icon'       => 'ri-magic-line',
                        'route'      => 'gii/index',
                        'permission' => 'manage_gii',
                    ],
                ],
            ],

            [
                'label'    => 'RBAC',
                'icon'     => 'ri-shield-user-line',

                'children' => [
                    [
                        'label'      => 'Users',
                        'icon'       => 'ri-user-settings-line',
                        'route'      => 'users/index',
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Roles',
                        'icon'       => 'ri-shield-keyhole-line',
                        'route'      => 'roles/index',
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Permissions',
                        'icon'       => 'ri-key-2-line',
                        'route'      => 'permissions/index',
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Routes',
                        'icon'       => 'ri-route-line',
                        'route'      => 'routes/index',
                        'permission' => 'manage_rbac',
                    ],
                ],
            ],
        ],
    ],

];