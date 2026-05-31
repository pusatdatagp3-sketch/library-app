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
        ],
    ],

    [
        'group' => 'Data Master',
        'items' => [

            [
                'label' => 'Data Guru',
                'icon'  => 'ri-user-star',
                'route' => 'guru/index',
            ],

            [
                'label'      => 'Asrama',
                'icon'       => 'ri-building-4',
                'permission' => 'view_kamar',

                'children'   => [
                    [
                        'label' => 'Data Kamar',
                        'icon'  => 'ri-door-open',
                        'route' => 'kamar/index',
                    ],
                    [
                        'label' => 'Data Rayon',
                        'icon'  => 'ri-community',
                        'route' => 'rayon/index',
                    ],
                ],
            ],

            [
                'label'      => 'Data Konsulat',
                'icon'       => 'ri-global',
                'route'      => 'konsulat/index',
                'permission' => 'view_konsulat',
            ],

            [
                'label'      => 'Data Santri',
                'icon'       => 'ri-user',
                'route'      => 'santri/index',
                'permission' => 'view_santri',
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
                ],
            ],
        ],
    ],

];