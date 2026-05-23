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

