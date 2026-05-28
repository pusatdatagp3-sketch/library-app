<?php

declare(strict_types=1);

use App\Web;
use App\Web\Auth\AuthController;
use App\Web\Rbac\RbacController;
use App\Web\Middleware\RbacAccessControlMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()
        ->routes(
            Route::get('/')
                ->action(Web\HomePage\Action::class)
                ->name('home'),
        ),
    Group::create()
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('/hello')
                ->action([Web\Hello\HelloController::class, 'index'])
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
                ->action([Web\Guru\GuruController::class, 'index'])
                ->name('guru/index'),
            Route::get('/create')
                ->action([Web\Guru\GuruController::class, 'create'])
                ->name('guru/create'),
            Route::post('/create')
                ->action([Web\Guru\GuruController::class, 'create'])
                ->name('guru/create/post'),
            Route::get('/update/{kdg:\d+}')
                ->action([Web\Guru\GuruController::class, 'update'])
                ->name('guru/update'),
            Route::post('/update/{kdg:\d+}')
                ->action([Web\Guru\GuruController::class, 'update'])
                ->name('guru/update/post'),
            Route::post('/delete/{kdg:\d+}')
                ->action([Web\Guru\GuruController::class, 'delete'])
                ->name('guru/delete'),
            Route::get('/download-template')
                ->action([Web\Guru\GuruController::class, 'downloadTemplate'])
                ->name('guru/download-template'),
            Route::post('/upload')
                ->action([Web\Guru\GuruController::class, 'upload'])
                ->name('guru/upload'),
        ),

    // Kamar Group protected by RBAC middleware
    Group::create('/kamar')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Kamar\KamarController::class, 'index'])
                ->name('kamar/index'),
            Route::get('/create')
                ->action([Web\Kamar\KamarController::class, 'create'])
                ->name('kamar/create'),
            Route::post('/create')
                ->action([Web\Kamar\KamarController::class, 'create'])
                ->name('kamar/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Kamar\KamarController::class, 'update'])
                ->name('kamar/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Kamar\KamarController::class, 'update'])
                ->name('kamar/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Kamar\KamarController::class, 'delete'])
                ->name('kamar/delete'),
        ),

    // Konsulat Group protected by RBAC middleware
    Group::create('/konsulat')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Konsulat\KonsulatController::class, 'index'])
                ->name('konsulat/index'),
            Route::get('/create')
                ->action([Web\Konsulat\KonsulatController::class, 'create'])
                ->name('konsulat/create'),
            Route::post('/create')
                ->action([Web\Konsulat\KonsulatController::class, 'create'])
                ->name('konsulat/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Konsulat\KonsulatController::class, 'update'])
                ->name('konsulat/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Konsulat\KonsulatController::class, 'update'])
                ->name('konsulat/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Konsulat\KonsulatController::class, 'delete'])
                ->name('konsulat/delete'),
        ),

    // Gii Group protected by RBAC middleware
    Group::create('/gii')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Gii\GiiController::class, 'index'])
                ->name('gii/index'),
            Route::post('/generate')
                ->action([Web\Gii\GiiController::class, 'generate'])
                ->name('gii/generate'),
        ),

    // Pelanggaran Group protected by RBAC middleware
    Group::create('/pelanggaran')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Pelanggaran\PelanggaranController::class, 'index'])
                ->name('pelanggaran/index'),
            Route::get('/create')
                ->action([Web\Pelanggaran\PelanggaranController::class, 'create'])
                ->name('pelanggaran/create'),
            Route::post('/create')
                ->action([Web\Pelanggaran\PelanggaranController::class, 'create'])
                ->name('pelanggaran/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Pelanggaran\PelanggaranController::class, 'update'])
                ->name('pelanggaran/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Pelanggaran\PelanggaranController::class, 'update'])
                ->name('pelanggaran/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Pelanggaran\PelanggaranController::class, 'delete'])
                ->name('pelanggaran/delete'),
        ),

    // Santri Group protected by RBAC middleware
    Group::create('/santri')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Santri\SantriController::class, 'index'])
                ->name('santri/index'),
            Route::get('/create')
                ->action([Web\Santri\SantriController::class, 'create'])
                ->name('santri/create'),
            Route::post('/create')
                ->action([Web\Santri\SantriController::class, 'create'])
                ->name('santri/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Santri\SantriController::class, 'update'])
                ->name('santri/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Santri\SantriController::class, 'update'])
                ->name('santri/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Santri\SantriController::class, 'delete'])
                ->name('santri/delete'),
        ),
    // RBAC Group protected by RBAC middleware
    Group::create('/rbac')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([RbacController::class, 'index'])
                ->name('rbac/index'),
            Route::post('/save-matrix')
                ->action([RbacController::class, 'saveMatrix'])
                ->name('rbac/save-matrix'),
            Route::post('/update-user-role')
                ->action([RbacController::class, 'updateUserRole'])
                ->name('rbac/update-user-role'),
            Route::post('/create-user')
                ->action([RbacController::class, 'createUser'])
                ->name('rbac/create-user'),
            Route::post('/save-route-permissions')
                ->action([RbacController::class, 'saveRoutePermissions'])
                ->name('rbac/save-route-permissions'),
        ),
];

