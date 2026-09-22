<?php

declare(strict_types=1);

/**
 * Konfigurasi Menu Sidebar KUTUBIA
 * 
 * Setiap menu item memiliki konfigurasi:
 * - 'label'  : Label tampilan menu
 * - 'icon'   : Ikon RemixIcon (otomatis disesuaikan dengan -line / -fill)
 * - 'route'  : Rute URL yang dituju
 * - 'active' : Rute strict yang membuat menu ini aktif (atau closure pembanding rute)
 * - 'role' / 'permission' : Pembatasan hak akses
 * - 'children' : Submenu jika berupa dropdown
 */

return [

    [
        'group' => 'Utama',
        'icon'  => 'ri-compass-3-line',
        'items' => [
            [
                'label'  => 'Dashboard',
                'icon'   => 'ri-dashboard-3-line',
                'route'  => 'home',
                'active' => 'home',
            ],
        ],
    ],

    [
        'group' => 'Perpustakaan',
        'icon'  => 'ri-book-open-line',
        'items' => [
            [
                'label'  => 'Scan Kunjungan',
                'icon'   => 'ri-qr-scan-2-line',
                'route'  => 'perpustakaan/scan',
                'active' => 'perpustakaan/scan',
            ],
            [
                'label'  => 'Rekap Kunjungan',
                'icon'   => 'ri-file-chart-line',
                'route'  => 'perpustakaan/rekap',
                'active' => 'perpustakaan/rekap',
            ],
        ],
    ],

    [
        'group' => 'Sistem',
        'icon'  => 'ri-settings-4-line',
        'items' => [

            [
                'label'  => 'Manajemen Staf',
                'icon'   => 'ri-user-star-line',
                'route'  => 'staf/index',
                'active' => static fn(string $r): bool => str_starts_with($r, 'staf/'),
                'role'   => 'super-admin',
            ],

            [
                'label'    => 'Generator',
                'icon'     => 'ri-code-box-line',

                'children' => [
                    [
                        'label'      => 'Gii Generator',
                        'icon'       => 'ri-magic-line',
                        'route'      => 'gii/index',
                        'active'     => static fn(string $r): bool => str_starts_with($r, 'gii/'),
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
                        'active'     => static fn(string $r): bool => str_starts_with($r, 'users/'),
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Roles',
                        'icon'       => 'ri-shield-keyhole-line',
                        'route'      => 'roles/index',
                        'active'     => static fn(string $r): bool => str_starts_with($r, 'roles/'),
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Permissions',
                        'icon'       => 'ri-key-2-line',
                        'route'      => 'permissions/index',
                        'active'     => static fn(string $r): bool => str_starts_with($r, 'permissions/'),
                        'permission' => 'manage_rbac',
                    ],
                    [
                        'label'      => 'Routes',
                        'icon'       => 'ri-route-line',
                        'route'      => 'routes/index',
                        'active'     => 'routes/index',
                        'permission' => 'manage_rbac',
                    ],
                ],
            ],
        ],
    ],

];