<?php

declare(strict_types=1);

namespace App\Web\Monitor\Controller;

use App\Web\Entitas\Model\Entitas;
use App\Web\Modul\Model\Modul;
use App\Web\Task\Model\Task;
use Cycle\ORM\ORMInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class MonitorController
{
    private $entitasRepository;
    private $taskRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private ORMInterface $orm
    ) {
        $this->entitasRepository = $orm->getRepository(Entitas::class);
        $this->taskRepository = $orm->getRepository(Task::class);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $moduls = $this->orm->getRepository(Modul::class)->select()->orderBy('id', 'ASC')->fetchAll();
        $entitasList = $this->entitasRepository->select()->fetchAll();
        $tasks = $this->taskRepository->select()
            ->load('kanbanColumn')
            ->load('program')
            ->load('program.entitas')
            ->load('assignedUser')
            ->orderBy('urutan', 'ASC')
            ->fetchAll();

        $modulData = [];
        foreach ($moduls as $modul) {
            $modulData[$modul->id] = [
                'modul' => $modul,
                'todoTasks' => [],
                'progressTasks' => [],
                'doneTasks' => [],
                'pendingTasks' => [],
                'rejectedTasks' => []
            ];
        }

        foreach ($tasks as $task) {
            if ($task->kanbanColumn === null) {
                continue;
            }

            $colNameLower = strtolower($task->kanbanColumn->nama);
            $isTodo = str_contains($colNameLower, 'todo') || str_contains($colNameLower, 'to do') || str_contains($colNameLower, 'rencana');
            $isProgress = str_contains($colNameLower, 'progress') || str_contains($colNameLower, 'jalan');
            $isDone = str_contains($colNameLower, 'done') || str_contains($colNameLower, 'selesai');
            $isPending = str_contains($colNameLower, 'pending') || str_contains($colNameLower, 'tunda');
            $isRejected = str_contains($colNameLower, 'rejected') || str_contains($colNameLower, 'tolak');

            if (!$isTodo && !$isProgress && !$isDone && !$isPending && !$isRejected) {
                continue;
            }

            // Find Program and Entitas
            $program = $task->program;
            if ($program === null) {
                continue;
            }

            $entitasId = $program->entitasId;
            $entitas = null;
            foreach ($entitasList as $e) {
                if ($e->id === $entitasId) {
                    $entitas = $e;
                    break;
                }
            }

            if ($entitas === null || $entitas->modulId === null) {
                continue;
            }

            if (isset($modulData[$entitas->modulId])) {
                if ($isTodo) {
                    $modulData[$entitas->modulId]['todoTasks'][] = $task;
                } elseif ($isProgress) {
                    $modulData[$entitas->modulId]['progressTasks'][] = $task;
                } elseif ($isDone) {
                    $modulData[$entitas->modulId]['doneTasks'][] = $task;
                } elseif ($isPending) {
                    $modulData[$entitas->modulId]['pendingTasks'][] = $task;
                } elseif ($isRejected) {
                    $modulData[$entitas->modulId]['rejectedTasks'][] = $task;
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'modulData' => $modulData,
        ]);
    }
}
