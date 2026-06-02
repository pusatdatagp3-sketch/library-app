<?php

declare(strict_types=1);

namespace App\Web\Program\Controller;

use App\Web\Program\Model\Program;
use App\Web\Program\Model\KendalaProgram;
use App\Web\Program\Model\Notulensi;
use App\Web\Program\Model\Dokumentasi;
use App\Web\Entitas\Model\Entitas;
use App\Web\Entitas\Model\AnggotaEntitas;
use App\Web\Task\Model\Task;
use App\Web\Task\Model\TaskProgressLog;
use App\Shared\TenantContext;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class ProgramController
{
    private $programRepository;
    private $kendalaRepository;
    private $notulensiRepository;
    private $dokumentasiRepository;
    private $entitasRepository;
    private $anggotaRepository;
    private $taskRepository;
    private $logRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private TenantContext $tenantContext
    ) {
        $this->programRepository    = $orm->getRepository(Program::class);
        $this->kendalaRepository    = $orm->getRepository(KendalaProgram::class);
        $this->notulensiRepository  = $orm->getRepository(Notulensi::class);
        $this->dokumentasiRepository = $orm->getRepository(Dokumentasi::class);
        $this->entitasRepository    = $orm->getRepository(Entitas::class);
        $this->anggotaRepository    = $orm->getRepository(AnggotaEntitas::class);
        $this->taskRepository       = $orm->getRepository(Task::class);
        $this->logRepository        = $orm->getRepository(TaskProgressLog::class);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $entitasId = isset($queryParams['entitas_id']) ? (int)$queryParams['entitas_id'] : null;

        $model = new Program();
        $model->kodeKampus = $this->tenantContext->getActiveCampusCode();
        if ($entitasId !== null) {
            $model->entitasId = $entitasId;
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $successMsg = "Program \"{$model->namaProgram}\" berhasil dibuat.";
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', $successMsg);
                
                // Get modul prefix for redirection
                $entitas = $this->entitasRepository->findByPK($model->entitasId);
                $redirectUrl = $this->getRedirectUrlForEntitas($entitas);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $redirectUrl);
            }
        }

        $entitasList = $this->entitasRepository->select()->orderBy('nama', 'ASC')->fetchAll();
        
        // Members list for Penanggung Jawab dropdown
        $members = [];
        if ($model->entitasId !== null) {
            $members = $this->anggotaRepository->select()
                ->where(['entitas_id' => $model->entitasId])
                ->orderBy('nama_anggota', 'ASC')
                ->fetchAll();
        } else {
            $members = $this->anggotaRepository->select()->orderBy('nama_anggota', 'ASC')->fetchAll();
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/create', [
            'model' => $model,
            'entitasList' => $entitasList,
            'members' => $members,
            'backUrl' => $entitasId !== null ? $this->getRedirectUrlForEntitas($this->entitasRepository->findByPK($entitasId)) : $this->urlGenerator->generate('home'),
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->programRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $successMsg = "Program \"{$model->namaProgram}\" berhasil diperbarui.";
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', $successMsg);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $model->id]));
            }
        }

        $entitasList = $this->entitasRepository->select()->orderBy('nama', 'ASC')->fetchAll();
        
        $members = $this->anggotaRepository->select()
            ->where(['entitas_id' => $model->entitasId])
            ->orderBy('nama_anggota', 'ASC')
            ->fetchAll();

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'model' => $model,
            'entitasList' => $entitasList,
            'members' => $members,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->programRepository->findByPK($id);

        if ($model !== null) {
            $entitas = $this->entitasRepository->findByPK($model->entitasId);
            $redirectUrl = $this->getRedirectUrlForEntitas($entitas);
            
            $successMsg = "Program berhasil dihapus.";
            $this->entityManager->delete($model)->run();
            $this->flash->set('success', $successMsg);
            return $this->responseFactory->createResponse(302)->withHeader('Location', $redirectUrl);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('home'));
    }

    public function view(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->programRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        $kendalaList = $this->kendalaRepository->select()
            ->where(['program_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        $notulensiList = $this->notulensiRepository->select()
            ->where(['program_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        $dokumentasiList = $this->dokumentasiRepository->select()
            ->where(['program_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        $tasks = $this->taskRepository->select()
            ->where(['program_id' => $id])
            ->load('kanbanColumn')
            ->fetchAll();

        $taskStats = [
            'todo' => 0,
            'pending' => 0,
            'progress' => 0,
            'rejected' => 0,
            'done' => 0,
        ];

        foreach ($tasks as $task) {
            if ($task->kanbanColumn === null) {
                continue;
            }
            $colNameLower = strtolower($task->kanbanColumn->nama);
            if (str_contains($colNameLower, 'todo') || str_contains($colNameLower, 'to do') || str_contains($colNameLower, 'rencana')) {
                $taskStats['todo']++;
            } elseif (str_contains($colNameLower, 'pending') || str_contains($colNameLower, 'tunda')) {
                $taskStats['pending']++;
            } elseif (str_contains($colNameLower, 'progress') || str_contains($colNameLower, 'jalan')) {
                $taskStats['progress']++;
            } elseif (str_contains($colNameLower, 'rejected') || str_contains($colNameLower, 'tolak')) {
                $taskStats['rejected']++;
            } elseif (str_contains($colNameLower, 'done') || str_contains($colNameLower, 'selesai')) {
                $taskStats['done']++;
            }
        }

        $entitas = $this->entitasRepository->findByPK($model->entitasId);
        $backUrl  = $this->getRedirectUrlForEntitas($entitas);

        // Fetch tasks di kolom Pending & Rejected beserta alasan terakhir dari log
        $pendingRejectedTasks = [];
        foreach ($tasks as $task) {
            if ($task->kanbanColumn === null) {
                continue;
            }
            $colName = strtolower($task->kanbanColumn->nama);
            $isPending  = str_contains($colName, 'pending') || str_contains($colName, 'tunda');
            $isRejected = str_contains($colName, 'rejected') || str_contains($colName, 'tolak');
            if (!$isPending && !$isRejected) {
                continue;
            }
            // Ambil log terbaru yang punya alasan
            $latestLog = $this->logRepository->select()
                ->where('task_id', $task->id)
                ->where('alasan', 'IS NOT', null)
                ->orderBy('created_at', 'DESC')
                ->fetchOne();
            $pendingRejectedTasks[] = [
                'task'   => $task,
                'type'   => $isPending ? 'pending' : 'rejected',
                'alasan' => $latestLog?->alasan ?? null,
                'logAt'  => $latestLog?->createdAt ?? null,
            ];
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/view', [
            'model'                => $model,
            'kendalaList'          => $kendalaList,
            'notulensiList'        => $notulensiList,
            'dokumentasiList'      => $dokumentasiList,
            'backUrl'              => $backUrl,
            'entitas'              => $entitas,
            'taskStats'            => $taskStats,
            'pendingRejectedTasks' => $pendingRejectedTasks,
            'successMsg'           => $this->flash->get('success'),
            'errorMsgs'            => $this->flash->get('errors') ?? [],
        ]);
    }





    private function getRedirectUrlForEntitas(?Entitas $entitas): string
    {
        if ($entitas === null) {
            return $this->urlGenerator->generate('home');
        }

        $viewRoute = 'fungsionaris/view';
        if ($entitas->modulId === 2) {
            $viewRoute = 'kepanitiaan/view';
        } elseif ($entitas->modulId === 3) {
            $viewRoute = 'empowering/view';
        } elseif ($entitas->modulId === 4) {
            $viewRoute = 'koordinator/view';
        }

        return $this->urlGenerator->generate($viewRoute, ['id' => $entitas->id]);
    }
}
