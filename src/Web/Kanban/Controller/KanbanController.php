<?php

declare(strict_types=1);

namespace App\Web\Kanban\Controller;

use App\Web\Program\Model\Program;
use App\Web\Kanban\Model\KanbanColumn;
use App\Web\Task\Model\Task;
use App\Web\Task\Model\TaskProgressLog;
use App\Web\Entitas\Model\AnggotaEntitas;
use App\Web\Entitas\Model\Entitas;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class KanbanController
{
    private $programRepository;
    private $columnRepository;
    private $taskRepository;
    private $logRepository;
    private $anggotaRepository;
    private $entitasRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager
    ) {
        $this->programRepository = $orm->getRepository(Program::class);
        $this->columnRepository  = $orm->getRepository(KanbanColumn::class);
        $this->taskRepository    = $orm->getRepository(Task::class);
        $this->logRepository     = $orm->getRepository(TaskProgressLog::class);
        $this->anggotaRepository = $orm->getRepository(AnggotaEntitas::class);
        $this->entitasRepository = $orm->getRepository(Entitas::class);
    }

    public function board(ServerRequestInterface $request): ResponseInterface
    {
        $programId = (int)$this->currentRoute->getArgument('program_id');
        $program   = $this->programRepository->findByPK($programId);

        if ($program === null) {
            return $this->responseFactory->createResponse(404);
        }

        $entitas = $this->entitasRepository->findByPK($program->entitasId);

        // Fetch columns. If empty, seed default ones.
        $columns = $this->columnRepository->select()
            ->where(['entitas_id' => $program->entitasId])
            ->orderBy('urutan', 'ASC')
            ->fetchAll();

        if (empty($columns)) {
            $defaultCols = [
                ['nama' => 'To Do',       'progress' => 0,   'requires_proof' => false, 'requires_reason' => false],
                ['nama' => 'Pending',     'progress' => 20,  'requires_proof' => false, 'requires_reason' => true],
                ['nama' => 'On Progress', 'progress' => 50,  'requires_proof' => true,  'requires_reason' => false],
                ['nama' => 'Rejected',    'progress' => 0,   'requires_proof' => false, 'requires_reason' => true],
                ['nama' => 'Done',        'progress' => 100, 'requires_proof' => true,  'requires_reason' => false],
            ];
            foreach ($defaultCols as $i => $item) {
                $col = new KanbanColumn();
                $col->entitasId    = $program->entitasId;
                $col->nama         = $item['nama'];
                $col->progress     = $item['progress'];
                $col->requiresProof  = $item['requires_proof'];
                $col->requiresReason = $item['requires_reason'];
                $col->urutan       = $i + 1;
                $this->entityManager->persist($col)->run();
            }
            $columns = $this->columnRepository->select()
                ->where(['entitas_id' => $program->entitasId])
                ->orderBy('urutan', 'ASC')
                ->fetchAll();
        }

        // Fetch all tasks for this program
        $tasks = $this->taskRepository->select()
            ->where(['program_id' => $programId])
            ->orderBy('urutan', 'ASC')
            ->fetchAll();

        // Fetch members of the entitas for assignment dropdown
        $members = $this->anggotaRepository->select()
            ->where(['entitas_id' => $program->entitasId])
            ->orderBy('nama_anggota', 'ASC')
            ->fetchAll();

        // Fetch all progress logs for this program's tasks, grouped by task_id
        $taskIds     = array_map(fn($t) => $t->id, $tasks);
        $taskLogsRaw = [];
        if (!empty($taskIds)) {
            $taskLogsRaw = $this->logRepository->select()
                ->where('task_id', 'IN', $taskIds)
                ->orderBy('created_at', 'DESC')
                ->fetchAll();
        }
        $taskLogs = [];
        foreach ($taskLogsRaw as $log) {
            $taskLogs[$log->taskId][] = $log;
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/board', [
            'program'    => $program,
            'entitas'    => $entitas,
            'columns'    => $columns,
            'tasks'      => $tasks,
            'members'    => $members,
            'taskLogs'   => $taskLogs,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs'  => $this->flash->get('errors') ?? [],
        ]);
    }
}
