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

    // Auth Routes
    Route::get('/login')
        ->action([AuthController::class, 'login'])
        ->name('login'),
    Route::post('/login')
        ->action([AuthController::class, 'login']),
    Route::post('/logout')
        ->action([AuthController::class, 'logout'])
        ->name('logout'),
    Route::post('/select-campus')
        ->action([AuthController::class, 'selectCampus'])
        ->name('select-campus'),

    // SSO Routes
    Route::get('/sso/login')
        ->action([\App\Web\Auth\Controller\SsoController::class, 'redirect'])
        ->name('sso-login'),
    Route::get('/auth/callback')
        ->action([\App\Web\Auth\Controller\SsoController::class, 'callback'])
        ->name('auth-callback'),
    Route::get('/auth/sso/logout')
        ->action([\App\Web\Auth\Controller\SsoController::class, 'logout'])
        ->name('sso-logout'),



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

