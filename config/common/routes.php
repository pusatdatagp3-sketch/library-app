<?php

declare(strict_types=1);

use App\Web;
use App\Web\Auth\Controller\AuthController;
use App\Web\Rbac\Controller\UserController;
use App\Web\Rbac\Controller\RoleController;
use App\Web\Rbac\Controller\PermissionController;
use App\Web\Rbac\Controller\RoutePermissionController;
use App\Web\Middleware\RbacAccessControlMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()
        ->routes(
            Route::get('/')
                ->action(Web\HomePage\Controller\Action::class)
                ->name('home'),
        ),
    Group::create()
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('/hello')
                ->action([Web\Hello\Controller\HelloController::class, 'index'])
                ->name('hello'),
        ),
    // Auth Routes
    Route::get('/login')
        ->action([AuthController::class, 'login'])
        ->name('login'),
    Route::post('/login')
        ->action([AuthController::class, 'login']),
    Route::post('/logout')
        ->action([AuthController::class, 'logout'])
        ->name('logout'),

    // Guru Group protected by RBAC middleware
    Group::create('/guru')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Guru\Controller\GuruController::class, 'index'])
                ->name('guru/index'),
            Route::get('/create')
                ->action([Web\Guru\Controller\GuruController::class, 'create'])
                ->name('guru/create'),
            Route::post('/create')
                ->action([Web\Guru\Controller\GuruController::class, 'create'])
                ->name('guru/create/post'),
            Route::get('/update/{kdg:\d+}')
                ->action([Web\Guru\Controller\GuruController::class, 'update'])
                ->name('guru/update'),
            Route::post('/update/{kdg:\d+}')
                ->action([Web\Guru\Controller\GuruController::class, 'update'])
                ->name('guru/update/post'),
            Route::post('/delete/{kdg:\d+}')
                ->action([Web\Guru\Controller\GuruController::class, 'delete'])
                ->name('guru/delete'),
            Route::get('/download-template')
                ->action([Web\Guru\Controller\GuruController::class, 'downloadTemplate'])
                ->name('guru/download-template'),
            Route::post('/upload')
                ->action([Web\Guru\Controller\GuruController::class, 'upload'])
                ->name('guru/upload'),
        ),

    // Kamar Group protected by RBAC middleware
    Group::create('/kamar')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Kamar\Controller\KamarController::class, 'index'])
                ->name('kamar/index'),
            Route::get('/create')
                ->action([Web\Kamar\Controller\KamarController::class, 'create'])
                ->name('kamar/create'),
            Route::post('/create')
                ->action([Web\Kamar\Controller\KamarController::class, 'create'])
                ->name('kamar/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Kamar\Controller\KamarController::class, 'update'])
                ->name('kamar/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Kamar\Controller\KamarController::class, 'update'])
                ->name('kamar/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Kamar\Controller\KamarController::class, 'delete'])
                ->name('kamar/delete'),
        ),

    // Konsulat Group protected by RBAC middleware
    Group::create('/konsulat')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Konsulat\Controller\KonsulatController::class, 'index'])
                ->name('konsulat/index'),
            Route::get('/create')
                ->action([Web\Konsulat\Controller\KonsulatController::class, 'create'])
                ->name('konsulat/create'),
            Route::post('/create')
                ->action([Web\Konsulat\Controller\KonsulatController::class, 'create'])
                ->name('konsulat/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Konsulat\Controller\KonsulatController::class, 'update'])
                ->name('konsulat/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Konsulat\Controller\KonsulatController::class, 'update'])
                ->name('konsulat/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Konsulat\Controller\KonsulatController::class, 'delete'])
                ->name('konsulat/delete'),
        ),

    // Gii Group protected by RBAC middleware
    Group::create('/gii')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Gii\Controller\GiiController::class, 'index'])
                ->name('gii/index'),
            Route::post('/generate')
                ->action([Web\Gii\Controller\GiiController::class, 'generate'])
                ->name('gii/generate'),
        ),

    // Pelanggaran Group protected by RBAC middleware
    Group::create('/pelanggaran')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Pelanggaran\Controller\PelanggaranController::class, 'index'])
                ->name('pelanggaran/index'),
            Route::get('/create')
                ->action([Web\Pelanggaran\Controller\PelanggaranController::class, 'create'])
                ->name('pelanggaran/create'),
            Route::post('/create')
                ->action([Web\Pelanggaran\Controller\PelanggaranController::class, 'create'])
                ->name('pelanggaran/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Pelanggaran\Controller\PelanggaranController::class, 'update'])
                ->name('pelanggaran/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Pelanggaran\Controller\PelanggaranController::class, 'update'])
                ->name('pelanggaran/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Pelanggaran\Controller\PelanggaranController::class, 'delete'])
                ->name('pelanggaran/delete'),
        ),

    // Santri Group protected by RBAC middleware
    Group::create('/santri')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Santri\Controller\SantriController::class, 'index'])
                ->name('santri/index'),
            Route::get('/create')
                ->action([Web\Santri\Controller\SantriController::class, 'create'])
                ->name('santri/create'),
            Route::post('/create')
                ->action([Web\Santri\Controller\SantriController::class, 'create'])
                ->name('santri/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Santri\Controller\SantriController::class, 'update'])
                ->name('santri/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Santri\Controller\SantriController::class, 'update'])
                ->name('santri/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Santri\Controller\SantriController::class, 'delete'])
                ->name('santri/delete'),
        ),
    // Users Group protected by RBAC middleware
    Group::create('/users')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([UserController::class, 'index'])
                ->name('users/index'),
            Route::get('/create')
                ->action([UserController::class, 'create'])
                ->name('users/create'),
            Route::post('/create')
                ->action([UserController::class, 'create'])
                ->name('users/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([UserController::class, 'update'])
                ->name('users/update'),
            Route::post('/update/{id:\d+}')
                ->action([UserController::class, 'update'])
                ->name('users/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([UserController::class, 'delete'])
                ->name('users/delete'),
        ),

    // Roles Group protected by RBAC middleware
    Group::create('/roles')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([RoleController::class, 'index'])
                ->name('roles/index'),
            Route::get('/create')
                ->action([RoleController::class, 'create'])
                ->name('roles/create'),
            Route::post('/create')
                ->action([RoleController::class, 'create'])
                ->name('roles/create/post'),
            Route::get('/update/{name:[a-zA-Z0-9_\-]+}')
                ->action([RoleController::class, 'update'])
                ->name('roles/update'),
            Route::post('/update/{name:[a-zA-Z0-9_\-]+}')
                ->action([RoleController::class, 'update'])
                ->name('roles/update/post'),
            Route::post('/delete/{name:[a-zA-Z0-9_\-]+}')
                ->action([RoleController::class, 'delete'])
                ->name('roles/delete'),
            Route::post('/save-matrix')
                ->action([RoleController::class, 'saveMatrix'])
                ->name('roles/save-matrix'),
        ),

    // Permissions Group protected by RBAC middleware
    Group::create('/permissions')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([PermissionController::class, 'index'])
                ->name('permissions/index'),
            Route::get('/create')
                ->action([PermissionController::class, 'create'])
                ->name('permissions/create'),
            Route::post('/create')
                ->action([PermissionController::class, 'create'])
                ->name('permissions/create/post'),
            Route::get('/update/{name:[a-zA-Z0-9_\-]+}')
                ->action([PermissionController::class, 'update'])
                ->name('permissions/update'),
            Route::post('/update/{name:[a-zA-Z0-9_\-]+}')
                ->action([PermissionController::class, 'update'])
                ->name('permissions/update/post'),
            Route::post('/delete/{name:[a-zA-Z0-9_\-]+}')
                ->action([PermissionController::class, 'delete'])
                ->name('permissions/delete'),
        ),

    // Routes Protection Group protected by RBAC middleware
    Group::create('/routes')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([RoutePermissionController::class, 'index'])
                ->name('routes/index'),
            Route::post('/save-route-permissions')
                ->action([RoutePermissionController::class, 'saveRoutePermissions'])
                ->name('routes/save-route-permissions'),
        ),
];

