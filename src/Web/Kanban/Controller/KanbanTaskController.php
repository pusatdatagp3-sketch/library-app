<?php

declare(strict_types=1);

namespace App\Web\Kanban\Controller;

use App\Web\Program\Model\Program;
use App\Web\Kanban\Model\KanbanColumn;
use App\Web\Task\Model\Task;
use App\Web\Task\Model\TaskProgressLog;
use App\Web\Shared\Service\FileCompressionService;
use App\Shared\TenantContext;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class KanbanTaskController
{
    /** @var \Cycle\ORM\RepositoryInterface */
    private $programRepository;
    /** @var \App\Web\Kanban\Model\KanbanColumnRepository */
    private $columnRepository;
    /** @var \App\Web\Task\Model\TaskRepository */
    private $taskRepository;
    /** @var \Cycle\ORM\Select\Repository */
    private $logRepository;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private FileCompressionService $fileCompressionService,
        private TenantContext $tenantContext
    ) {
        $this->programRepository = $orm->getRepository(Program::class);
        $this->columnRepository  = $orm->getRepository(KanbanColumn::class);
        $this->taskRepository    = $orm->getRepository(Task::class);
        $this->logRepository     = $orm->getRepository(TaskProgressLog::class);
    }

    public function addTask(ServerRequestInterface $request): ResponseInterface
    {
        $programId = (int)$this->currentRoute->getArgument('program_id');
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $program = $this->programRepository->findByPK($programId);
        if ($program === null) {
            return $this->responseFactory->createResponse(404);
        }

        $data = (array)$request->getParsedBody();
        $task = new Task();
        $task->programId = $programId;
        $task->kodeKampus = $program->kodeKampus;
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
            $log->taskId             = $task->id;
            $log->progressSebelumnya = 0;
            $log->progressBaru       = 0;
            $log->keterangan         = 'Tugas dibuat.';
            $log->createdAt          = new \DateTimeImmutable();
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
        $taskId    = (int)$this->currentRoute->getArgument('id');

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
            $successMsg = 'Tugas berhasil diperbarui.';
            $this->entityManager->persist($task)->run();

            // Create progress log if progress changed
            if ($oldProgress !== $task->progress) {
                $log = new TaskProgressLog();
                $log->taskId             = $task->id;
                $log->progressSebelumnya = $oldProgress;
                $log->progressBaru       = $task->progress;
                $log->keterangan         = 'Perubahan progress manual.';
                $log->createdAt          = new \DateTimeImmutable();
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
        $taskId    = (int)$this->currentRoute->getArgument('id');

        $task = $this->taskRepository->findByPK($taskId);
        if ($task !== null) {
            $successMsg = 'Tugas berhasil dihapus.';

            // Hapus file fisik foto bukti yang terkait dengan log progres tugas ini
            $logs = $this->logRepository->select()->where(['task_id' => $taskId])->fetchAll();
            foreach ($logs as $log) {
                if (!empty($log->buktiFoto)) {
                    $filePath = dirname(__DIR__, 4) . '/public/' . $log->buktiFoto;
                    if (is_file($filePath)) {
                        unlink($filePath);
                    }
                }
            }

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

        $body     = (array)$request->getParsedBody();
        $taskId   = isset($body['taskId'])   ? (int)$body['taskId']   : null;
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

        // --- Validasi: Bukti Foto ---
        $buktiFotoPath = null;
        if ($column->requiresProof) {
            $uploadedFiles = $request->getUploadedFiles();
            $buktiFile = $uploadedFiles['bukti_foto'] ?? null;

            if ($buktiFile === null || $buktiFile->getClientFilename() === '' || $buktiFile->getError() !== UPLOAD_ERR_OK) {
                $response = $this->responseFactory->createResponse(422);
                $response->getBody()->write(json_encode(['error' => 'Foto bukti wajib diunggah untuk kolom ini.']));
                return $response->withHeader('Content-Type', 'application/json');
            }

            $ext = strtolower(pathinfo($buktiFile->getClientFilename(), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $response = $this->responseFactory->createResponse(422);
                $response->getBody()->write(json_encode(['error' => 'Format foto tidak valid. Gunakan JPG, PNG, atau WEBP.']));
                return $response->withHeader('Content-Type', 'application/json');
            }

            try {
                $uploadDir = dirname(__DIR__, 4) . '/public/uploads/entitas_program_kanban_bukti';
                $buktiFotoPath = $this->fileCompressionService->compressAndSave(
                    $buktiFile,
                    $uploadDir,
                    'uploads/entitas_program_kanban_bukti',
                    'bukti_'
                );
            } catch (\Throwable $e) {
                $response = $this->responseFactory->createResponse(500);
                $response->getBody()->write(json_encode(['error' => 'Gagal memproses foto: ' . $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        // --- Validasi: Alasan ---
        $alasan = null;
        if ($column->requiresReason) {
            $alasan = trim((string)($body['alasan'] ?? ''));
            if ($alasan === '') {
                $response = $this->responseFactory->createResponse(422);
                $response->getBody()->write(json_encode(['error' => 'Alasan wajib diisi untuk kolom ini.']));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } else {
            $alasanInput = trim((string)($body['alasan'] ?? ''));
            if ($alasanInput !== '') {
                $alasan = $alasanInput;
            }
        }

        // Apply progress from column configuration
        $oldProgress = $task->progress;
        $newProgress = $column->progress;

        $task->kanbanColumnId = $columnId;
        $task->kanbanColumn   = $column;
        $task->progress       = $newProgress;
        $this->entityManager->persist($task)->run();

        // Update order of all tasks in the destination column
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
        $log->taskId             = $task->id;
        $log->progressSebelumnya = $oldProgress;
        $log->progressBaru       = $newProgress;
        $log->keterangan         = "Dipindahkan ke kolom '{$column->nama}' via Kanban Board.";
        $log->buktiFoto          = $buktiFotoPath;
        $log->alasan             = $alasan;
        $log->createdAt          = new \DateTimeImmutable();
        $this->entityManager->persist($log)->run();

        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
