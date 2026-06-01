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
        $this->columnRepository = $orm->getRepository(KanbanColumn::class);
        $this->taskRepository = $orm->getRepository(Task::class);
        $this->logRepository = $orm->getRepository(TaskProgressLog::class);
        $this->anggotaRepository = $orm->getRepository(AnggotaEntitas::class);
        $this->entitasRepository = $orm->getRepository(Entitas::class);
    }

    public function board(ServerRequestInterface $request): ResponseInterface
    {
        $programId = (int)$this->currentRoute->getArgument('program_id');
        $program = $this->programRepository->findByPK($programId);

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
                ['nama' => 'To Do', 'progress' => 0],
                ['nama' => 'Pending', 'progress' => 20],
                ['nama' => 'On Progress', 'progress' => 50],
                ['nama' => 'Rejected', 'progress' => 0],
                ['nama' => 'Done', 'progress' => 100],
            ];
            foreach ($defaultCols as $i => $item) {
                $col = new KanbanColumn();
                $col->entitasId = $program->entitasId;
                $col->nama = $item['nama'];
                $col->progress = $item['progress'];
                $col->urutan = $i + 1;
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

        return $this->viewRenderer->render(__DIR__ . '/../View/board', [
            'program' => $program,
            'entitas' => $entitas,
            'columns' => $columns,
            'tasks' => $tasks,
            'members' => $members,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function addTask(ServerRequestInterface $request): ResponseInterface
    {
        $programId = (int)$this->currentRoute->getArgument('program_id');
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array)$request->getParsedBody();
        $task = new Task();
        $task->programId = $programId;
        $task->load($data);

        // Find the first column as default
        $firstCol = $this->columnRepository->select()
            ->where(['entitas_id' => $this->programRepository->findByPK($programId)->entitasId])
            ->orderBy('urutan', 'ASC')
            ->fetchOne();

        if ($firstCol !== null) {
            $task->kanbanColumnId = $firstCol->id;
        }

        if ($task->validate()) {
            $successMsg = "Tugas \"{$task->judul}\" berhasil ditambahkan.";
            $this->entityManager->persist($task)->run();

            // Create initial progress log
            $log = new TaskProgressLog();
            $log->taskId = $task->id;
            $log->progressSebelumnya = 0;
            $log->progressBaru = 0;
            $log->keterangan = "Tugas dibuat.";
            $log->createdAt = new \DateTimeImmutable();
            $this->entityManager->persist($log)->run();

            $this->flash->set('success', $successMsg);
        } else {
            $this->flash->set('errors', array_values($task->errors));
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('kanban/board', ['program_id' => $programId]));
    }

    public function editTask(ServerRequestInterface $request): ResponseInterface
    {
        $programId = (int)$this->currentRoute->getArgument('program_id');
        $taskId = (int)$this->currentRoute->getArgument('id');

        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $task = $this->taskRepository->findByPK($taskId);
        if ($task === null) {
            return $this->responseFactory->createResponse(404);
        }

        $oldProgress = $task->progress;

        $data = (array)$request->getParsedBody();
        $task->load($data);

        if ($task->validate()) {
            $successMsg = "Tugas berhasil diperbarui.";
            $this->entityManager->persist($task)->run();

            // Create progress log if progress changed
            if ($oldProgress !== $task->progress) {
                $log = new TaskProgressLog();
                $log->taskId = $task->id;
                $log->progressSebelumnya = $oldProgress;
                $log->progressBaru = $task->progress;
                $log->keterangan = "Perubahan progress manual.";
                $log->createdAt = new \DateTimeImmutable();
                $this->entityManager->persist($log)->run();
            }

            $this->flash->set('success', $successMsg);
        } else {
            $this->flash->set('errors', array_values($task->errors));
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('kanban/board', ['program_id' => $programId]));
    }

    public function deleteTask(): ResponseInterface
    {
        $programId = (int)$this->currentRoute->getArgument('program_id');
        $taskId = (int)$this->currentRoute->getArgument('id');

        $task = $this->taskRepository->findByPK($taskId);
        if ($task !== null) {
            $successMsg = "Tugas berhasil dihapus.";
            $this->entityManager->delete($task)->run();
            $this->flash->set('success', $successMsg);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('kanban/board', ['program_id' => $programId]));
    }

    public function moveTask(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $body = (array)$request->getParsedBody();
        $taskId = isset($body['taskId']) ? (int)$body['taskId'] : null;
        $columnId = isset($body['columnId']) ? (int)$body['columnId'] : null;
        
        $taskIdsRaw = $body['taskIds'] ?? [];
        $taskIds = [];
        if (is_array($taskIdsRaw)) {
            $taskIds = $taskIdsRaw;
        } elseif (is_string($taskIdsRaw) && $taskIdsRaw !== '') {
            $taskIds = explode(',', $taskIdsRaw);
        }

        if ($taskId === null || $columnId === null) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write(json_encode(['error' => 'Missing arguments']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $task = $this->taskRepository->findByPK($taskId);
        if ($task === null) {
            $response = $this->responseFactory->createResponse(404);
            $response->getBody()->write(json_encode(['error' => 'Task not found']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $column = $this->columnRepository->findByPK($columnId);
        if ($column === null) {
            $response = $this->responseFactory->createResponse(404);
            $response->getBody()->write(json_encode(['error' => 'Column not found']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Apply progress based on dynamic column-configured progress value
        $oldProgress = $task->progress;
        $newProgress = $column->progress;

        $task->kanbanColumnId = $columnId;
        $task->kanbanColumn = $column; // Crucial: sync the relation object so Cycle ORM doesn't overwrite kanbanColumnId with the old relation's ID
        $task->progress = $newProgress;
        $this->entityManager->persist($task)->run();

        // Update all tasks order in the column
        foreach ($taskIds as $idx => $tId) {
            if (empty($tId) || strtolower((string)$tId) === 'null') {
                continue;
            }
            $t = $this->taskRepository->findByPK((int)$tId);
            if ($t !== null) {
                $t->urutan = $idx + 1;
                $this->entityManager->persist($t)->run();
            }
        }

        // Log the change
        $log = new TaskProgressLog();
        $log->taskId = $task->id;
        $log->progressSebelumnya = $oldProgress;
        $log->progressBaru = $newProgress;
        $log->keterangan = "Dipindahkan ke kolom '{$column->nama}' via Kanban Board.";
        $log->createdAt = new \DateTimeImmutable();
        $this->entityManager->persist($log)->run();

        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
