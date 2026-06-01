<?php

return [

    [
        'group' => 'Utama',
        'items' => [
            [
                'label' => 'Home',
                'icon'  => 'ri-home-5',
                'route' => 'home',
            ],
            [
                'label' => 'Monitor',
                'icon'  => 'ri-dashboard',
                'route' => 'monitor/index',
            ],
        ],
    ],



    [
        'group' => 'TQC KMI',
        'items' => [
            [
                'label'      => 'Fungsionaris KMI',
                'icon'       => 'ri-group-3',
                'route'      => 'fungsionaris/index',
                'permission' => 'view_fungsionaris',
            ],
            [
                'label'      => 'Kepanitiaan KMI',
                'icon'       => 'ri-calendar-event',
                'route'      => 'kepanitiaan/index',
                'permission' => 'view_kepanitiaan',
            ],
            [
                'label'      => 'Empowering KMI',
                'icon'       => 'ri-sparkling',
                'route'      => 'empowering/index',
                'permission' => 'view_empowering',
            ],
            [
                'label'      => 'Koordinator KMI',
                'icon'       => 'ri-user-star',
                'route'      => 'koordinator/index',
                'permission' => 'view_koordinator',
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