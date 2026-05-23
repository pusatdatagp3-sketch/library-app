<?php

declare(strict_types=1);

use App\Web;
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
    Group::create('/guru')
        ->routes(
            Route::get('')
                ->action([Web\Guru\GuruController::class, 'index'])
                ->name('guru/index'),
            Route::get('/create')
                ->action([Web\Guru\GuruController::class, 'create'])
                ->name('guru/create'),
            Route::post('/create')
                ->action([Web\Guru\GuruController::class, 'create']),
            Route::get('/update/{kdg:\d+}')
                ->action([Web\Guru\GuruController::class, 'update'])
                ->name('guru/update'),
            Route::post('/update/{kdg:\d+}')
                ->action([Web\Guru\GuruController::class, 'update']),
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
];
