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


    // Rayon Group protected by RBAC middleware
    Group::create('/rayon')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Rayon\Controller\RayonController::class, 'index'])
                ->name('rayon/index'),
            Route::get('/create')
                ->action([Web\Rayon\Controller\RayonController::class, 'create'])
                ->name('rayon/create'),
            Route::post('/create')
                ->action([Web\Rayon\Controller\RayonController::class, 'create'])
                ->name('rayon/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Rayon\Controller\RayonController::class, 'update'])
                ->name('rayon/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Rayon\Controller\RayonController::class, 'update'])
                ->name('rayon/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Rayon\Controller\RayonController::class, 'delete'])
                ->name('rayon/delete'),
        ),

    // Perizinan Group protected by RBAC middleware
    Group::create('/perizinan')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Perizinan\Controller\PerizinanController::class, 'index'])
                ->name('perizinan/index'),
            Route::get('/create')
                ->action([Web\Perizinan\Controller\PerizinanController::class, 'create'])
                ->name('perizinan/create'),
            Route::post('/create')
                ->action([Web\Perizinan\Controller\PerizinanController::class, 'create'])
                ->name('perizinan/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Perizinan\Controller\PerizinanController::class, 'update'])
                ->name('perizinan/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Perizinan\Controller\PerizinanController::class, 'update'])
                ->name('perizinan/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Perizinan\Controller\PerizinanController::class, 'delete'])
                ->name('perizinan/delete'),
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

    // Fungsionaris Group
    Group::create('/fungsionaris')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Entitas\Controller\EntitasController::class, 'fungsionarisIndex'])
                ->name('fungsionaris/index'),
            Route::get('/create')
                ->action([Web\Entitas\Controller\EntitasController::class, 'create'])
                ->name('fungsionaris/create'),
            Route::post('/create')
                ->action([Web\Entitas\Controller\EntitasController::class, 'create'])
                ->name('fungsionaris/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'update'])
                ->name('fungsionaris/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'update'])
                ->name('fungsionaris/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'delete'])
                ->name('fungsionaris/delete'),
            Route::get('/view/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'view'])
                ->name('fungsionaris/view'),
            Route::post('/view/{id:\d+}/add-member')
                ->action([Web\Entitas\Controller\EntitasController::class, 'addMember'])
                ->name('fungsionaris/add-member'),
            Route::post('/view/{id:\d+}/delete-member/{memberId:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'deleteMember'])
                ->name('fungsionaris/delete-member'),
        ),

    // Kepanitiaan Group
    Group::create('/kepanitiaan')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Entitas\Controller\EntitasController::class, 'kepanitiaanIndex'])
                ->name('kepanitiaan/index'),
            Route::get('/create')
                ->action([Web\Entitas\Controller\EntitasController::class, 'create'])
                ->name('kepanitiaan/create'),
            Route::post('/create')
                ->action([Web\Entitas\Controller\EntitasController::class, 'create'])
                ->name('kepanitiaan/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'update'])
                ->name('kepanitiaan/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'update'])
                ->name('kepanitiaan/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'delete'])
                ->name('kepanitiaan/delete'),
            Route::get('/view/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'view'])
                ->name('kepanitiaan/view'),
            Route::post('/view/{id:\d+}/add-member')
                ->action([Web\Entitas\Controller\EntitasController::class, 'addMember'])
                ->name('kepanitiaan/add-member'),
            Route::post('/view/{id:\d+}/delete-member/{memberId:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'deleteMember'])
                ->name('kepanitiaan/delete-member'),
        ),

    // Empowering Group
    Group::create('/empowering')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([Web\Entitas\Controller\EntitasController::class, 'empoweringIndex'])
                ->name('empowering/index'),
            Route::get('/create')
                ->action([Web\Entitas\Controller\EntitasController::class, 'create'])
                ->name('empowering/create'),
            Route::post('/create')
                ->action([Web\Entitas\Controller\EntitasController::class, 'create'])
                ->name('empowering/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'update'])
                ->name('empowering/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'update'])
                ->name('empowering/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'delete'])
                ->name('empowering/delete'),
            Route::get('/view/{id:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'view'])
                ->name('empowering/view'),
            Route::post('/view/{id:\d+}/add-member')
                ->action([Web\Entitas\Controller\EntitasController::class, 'addMember'])
                ->name('empowering/add-member'),
            Route::post('/view/{id:\d+}/delete-member/{memberId:\d+}')
                ->action([Web\Entitas\Controller\EntitasController::class, 'deleteMember'])
                ->name('empowering/delete-member'),
        ),

    // Program Group
    Group::create('/program')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('/create')
                ->action([Web\Program\Controller\ProgramController::class, 'create'])
                ->name('program/create'),
            Route::post('/create')
                ->action([Web\Program\Controller\ProgramController::class, 'create'])
                ->name('program/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'update'])
                ->name('program/update'),
            Route::post('/update/{id:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'update'])
                ->name('program/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'delete'])
                ->name('program/delete'),
            Route::get('/view/{id:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'view'])
                ->name('program/view'),
            Route::post('/view/{id:\d+}/add-kendala')
                ->action([Web\Program\Controller\ProgramController::class, 'addKendala'])
                ->name('program/add-kendala'),
            Route::post('/view/{id:\d+}/resolve-kendala/{kendalaId:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'resolveKendala'])
                ->name('program/resolve-kendala'),
            Route::post('/view/{id:\d+}/delete-kendala/{kendalaId:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'deleteKendala'])
                ->name('program/delete-kendala'),
            Route::post('/view/{id:\d+}/add-notulensi')
                ->action([Web\Program\Controller\ProgramController::class, 'addNotulensi'])
                ->name('program/add-notulensi'),
            Route::post('/view/{id:\d+}/delete-notulensi/{notulensiId:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'deleteNotulensi'])
                ->name('program/delete-notulensi'),
            Route::post('/view/{id:\d+}/add-dokumentasi')
                ->action([Web\Program\Controller\ProgramController::class, 'addDokumentasi'])
                ->name('program/add-dokumentasi'),
            Route::post('/view/{id:\d+}/delete-dokumentasi/{dokumentasiId:\d+}')
                ->action([Web\Program\Controller\ProgramController::class, 'deleteDokumentasi'])
                ->name('program/delete-dokumentasi'),
        ),

    // Kanban Group
    Group::create('/kanban')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('/board/{program_id:\d+}')
                ->action([Web\Kanban\Controller\KanbanController::class, 'board'])
                ->name('kanban/board'),
            Route::post('/board/{program_id:\d+}/add-task')
                ->action([Web\Kanban\Controller\KanbanController::class, 'addTask'])
                ->name('kanban/add-task'),
            Route::post('/board/{program_id:\d+}/edit-task/{id:\d+}')
                ->action([Web\Kanban\Controller\KanbanController::class, 'editTask'])
                ->name('kanban/edit-task'),
            Route::post('/board/{program_id:\d+}/delete-task/{id:\d+}')
                ->action([Web\Kanban\Controller\KanbanController::class, 'deleteTask'])
                ->name('kanban/delete-task'),
            Route::post('/move-task')
                ->action([Web\Kanban\Controller\KanbanController::class, 'moveTask'])
                ->name('kanban/move-task'),
        ),
];

