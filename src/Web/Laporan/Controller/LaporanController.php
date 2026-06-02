<?php

declare(strict_types=1);

namespace App\Web\Laporan\Controller;

use App\Web\Auth\Model\UserSession;
use App\Web\Entitas\Model\Entitas;
use App\Web\Modul\Model\Modul;
use App\Web\Program\Model\Program;
use App\Web\Task\Model\Task;
use App\Web\Laporan\Service\PdfExportService;
use Cycle\ORM\ORMInterface;
use Cycle\Database\DatabaseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\View\WebView;

final class LaporanController
{
    private $modulRepository;
    private $entitasRepository;
    private $programRepository;
    private $taskRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private ORMInterface $orm,
        private UserSession $userSession,
        private DatabaseInterface $db
    ) {
        $this->modulRepository = $orm->getRepository(Modul::class);
        $this->entitasRepository = $orm->getRepository(Entitas::class);
        $this->programRepository = $orm->getRepository(Program::class);
        $this->taskRepository = $orm->getRepository(Task::class);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        
        // Default date: Wednesday to Friday of current week
        $today = new \DateTime();
        $currentDay = (int)$today->format('w'); // 0=Sunday, 1=Monday, ..., 6=Saturday
        
        if ($currentDay == 0) {
            $daysToWednesday = 3;
        } else {
            $daysToWednesday = 3 - $currentDay;
        }
        
        $wednesday = clone $today;
        $wednesday->modify("{$daysToWednesday} days");
        
        $friday = clone $wednesday;
        $friday->modify('+2 days');
        
        $dateFrom = isset($queryParams['date_from']) 
            ? new \DateTime($queryParams['date_from'])
            : $wednesday;
        $dateTo = isset($queryParams['date_to']) 
            ? new \DateTime($queryParams['date_to'])
            : $friday;

        $reportData = $this->buildReportData();

        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'reportData' => $reportData,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
        ]);
    }

    public function exportPdf(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $parsedBody  = $request->getParsedBody();
        
        $today = new \DateTime();
        $currentDay = (int)$today->format('w');
        
        if ($currentDay == 0) {
            $daysToWednesday = 3;
        } else {
            $daysToWednesday = 3 - $currentDay;
        }
        
        $wednesday = clone $today;
        $wednesday->modify("{$daysToWednesday} days");
        
        $friday = clone $wednesday;
        $friday->modify('+2 days');
        
        $dateFromVal = $parsedBody['date_from'] ?? $queryParams['date_from'] ?? null;
        $dateToVal   = $parsedBody['date_to']   ?? $queryParams['date_to']   ?? null;

        $dateFrom = !empty($dateFromVal) && is_string($dateFromVal)
            ? new \DateTime($dateFromVal)
            : $wednesday;
        $dateTo = !empty($dateToVal) && is_string($dateToVal)
            ? new \DateTime($dateToVal)
            : $friday;

        $reportData = $this->buildReportData();

        $html = $this->generateHtmlReport($reportData, $dateFrom, $dateTo);

        $pdfService = new PdfExportService();
        $pdf = $pdfService->generatePdf($html);

        $response = $this->responseFactory->createResponse();
        $response = $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="laporan-program-kerja-' . date('Y-m-d') . '.pdf"')
            ->withHeader('Content-Length', (string)strlen($pdf));
        
        $response->getBody()->write($pdf);
        
        return $response;
    }

    /**
     * Build hierarchical report data: kampus → modul → entitas → task buckets.
     * Only campuses allowed by the current user session are included.
     */
    private function buildReportData(): array
    {
        // Resolve kampus list: use allowed campuses from session
        $allowedCampuses = $this->userSession->getAllowedCampuses();
        if (empty($allowedCampuses)) {
            return [];
        }

        // Load kampus names from list_kampus
        $campusNames = [];
        $rows = $this->db->select('kode', 'nama')
            ->from('list_kampus')
            ->where('kode', 'in', $allowedCampuses)
            ->fetchAll();
        foreach ($rows as $row) {
            $campusNames[$row['kode']] = $row['nama'];
        }

        // Load moduls
        $moduls = $this->modulRepository->select()->orderBy('id', 'ASC')->fetchAll();

        // Load entitas with their campus code
        $allEntitas = $this->entitasRepository->select()->fetchAll();

        // Load tasks with relations
        $tasks = $this->taskRepository->select()
            ->load('kanbanColumn')
            ->load('program')
            ->load('program.entitas')
            ->load('program.entitas.modul')
            ->load('assignedUser')
            ->fetchAll();

        // Build skeleton: kampus → modul → entitas
        $reportData = [];

        foreach ($allowedCampuses as $kode) {
            $reportData[$kode] = [
                'kode'  => $kode,
                'nama'  => $campusNames[$kode] ?? $kode,
                'modul' => [],
            ];
            foreach ($moduls as $modul) {
                $reportData[$kode]['modul'][$modul->id] = [
                    'modul'   => $modul,
                    'entitas' => [],
                ];
            }
        }

        // Group entitas into the skeleton (filtered by allowed campuses)
        foreach ($allEntitas as $entitas) {
            $kode    = $entitas->kodeKampus;
            $modulId = $entitas->modulId;
            if ($kode === null || !isset($reportData[$kode])) {
                continue;
            }
            if ($modulId === null || !isset($reportData[$kode]['modul'][$modulId])) {
                continue;
            }
            $reportData[$kode]['modul'][$modulId]['entitas'][$entitas->id] = [
                'entitas'  => $entitas,
                'todo'     => [],
                'done'     => [],
                'pending'  => [],
                'rejected' => [],
            ];
        }

        // Categorise tasks
        foreach ($tasks as $task) {
            if ($task->kanbanColumn === null || $task->program === null) {
                continue;
            }
            $program = $task->program;
            if ($program->entitas === null || $program->entitas->modulId === null) {
                continue;
            }

            $kode      = $program->entitas->kodeKampus;
            $modulId   = $program->entitas->modulId;
            $entitasId = $program->entitasId;

            if ($kode === null || !isset($reportData[$kode]['modul'][$modulId]['entitas'][$entitasId])) {
                continue;
            }

            $colNameLower = strtolower($task->kanbanColumn->nama);

            $isTodo     = str_contains($colNameLower, 'todo')     || str_contains($colNameLower, 'to do')   || str_contains($colNameLower, 'rencana');
            $isDone     = str_contains($colNameLower, 'done')     || str_contains($colNameLower, 'selesai');
            $isPending  = str_contains($colNameLower, 'pending')  || str_contains($colNameLower, 'tunda');
            $isRejected = str_contains($colNameLower, 'rejected') || str_contains($colNameLower, 'tolak');

            if (!$isTodo && !$isDone && !$isPending && !$isRejected) {
                continue;
            }

            $bucket = &$reportData[$kode]['modul'][$modulId]['entitas'][$entitasId];
            if ($isDone)         { $bucket['done'][]     = $task; }
            elseif ($isRejected) { $bucket['rejected'][] = $task; }
            elseif ($isPending)  { $bucket['pending'][]  = $task; }
            elseif ($isTodo)     { $bucket['todo'][]     = $task; }
            unset($bucket);
        }

        // Prune empty moduls, then empty campuses
        foreach ($reportData as $kode => &$campusData) {
            foreach ($campusData['modul'] as $modulId => &$modulData) {
                if (empty($modulData['entitas'])) {
                    unset($campusData['modul'][$modulId]);
                }
            }
            unset($modulData);
            if (empty($campusData['modul'])) {
                unset($reportData[$kode]);
            }
        }
        unset($campusData);

        return $reportData;
    }

    private function generateHtmlReport(array $reportData, \DateTime $dateFrom, \DateTime $dateTo): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Laporan Program Kerja</title></head><body>';
        
        $kopPath = '';
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $path = dirname(__DIR__, 4) . '/public/kop-surat.' . $ext;
            if (is_file($path)) {
                $kopPath = $path;
                break;
            }
        }
        
        if ($kopPath !== '') {
            $ext       = pathinfo($kopPath, PATHINFO_EXTENSION);
            $mimeType  = strtolower($ext) === 'png' ? 'image/png' : 'image/jpeg';
            $imageData = base64_encode(file_get_contents($kopPath));
            
            $html .= '<div style="margin: -1cm -1cm 0cm -1cm; width: 21cm; height: 4cm; overflow: hidden;">';
            $html .= '    <img src="data:' . $mimeType . ';base64,' . $imageData . '" style="width: 21cm; height: 4.5cm; object-fit: fill;" />';
            $html .= '</div>';
        }
        
        $html .= '<div style="margin: 0; padding: 0; font-family: \'Book Antiqua\', Georgia, serif; line-height: 1.5;">';
        $html .= '<h1 style="text-align: center; font-size: 16pt; font-weight: bold; margin: 0 0 12pt 0;">Laporan Program Kerja</h1>';
        $html .= '<p style="text-align: center; font-size: 11pt; margin: 0 0 18pt 0; line-height: 1.5;">';
        $html .= 'Periode: <strong>' . $dateFrom->format('d-m-Y') . '</strong> s/d <strong>' . $dateTo->format('d-m-Y') . '</strong><br>';
        $html .= 'Tanggal Generate: ' . date('d-m-Y H:i:s');
        $html .= '</p>';
        
        if (empty($reportData)) {
            $html .= '<p style="text-align: center; color: #666; margin: 12pt 0;">Tidak ada data untuk periode yang dipilih.</p>';
        } else {
            $campusCounter = 0;
            foreach ($reportData as $kode => $campusData) {
                $campusCounter++;
                $campusLetter = chr(64 + $campusCounter); // A, B, C …

                // ── Campus heading ──────────────────────────────────────────────
                $html .= '<div style="margin: 0 0 24pt 0;">';
                $html .= '<h2 style="font-size: 15pt; font-weight: bold; background: #333; color: #fff; padding: 6pt 10pt; margin: 0 0 14pt 0;">';
                $html .= $campusLetter . '. ' . strtoupper(htmlspecialchars($campusData['nama']));
                $html .= ' <span style="font-size: 9pt; font-weight: normal; color: #ccc;">(' . htmlspecialchars($kode) . ')</span>';
                $html .= '</h2>';

                $modulCounter = 0;
                foreach ($campusData['modul'] as $modulId => $modulData) {
                    $modulCounter++;
                    $modulLetter = chr(64 + $modulCounter); // A, B, C …

                    // ── Modul heading ────────────────────────────────────────────
                    $html .= '<div style="margin: 0 0 16pt 12pt;">';
                    $html .= '<h3 style="font-size: 13pt; font-weight: bold; border-bottom: 1pt solid #555; padding: 0 0 4pt 0; margin: 0 0 10pt 0;">';
                    $html .= $campusLetter . $modulLetter . '. ' . strtoupper(htmlspecialchars($modulData['modul']->nama ?? 'Unknown'));
                    $html .= '</h3>';

                    $entitasCounter = 0;
                    foreach ($modulData['entitas'] as $entitasId => $entitasData) {
                        $entitasCounter++;

                        // ── Entitas heading ──────────────────────────────────────
                        $html .= '<div style="margin: 0 0 12pt 14pt;">';
                        $html .= '<h4 style="font-size: 12pt; font-weight: bold; border-left: 2pt solid #555; padding: 0 0 0 10pt; margin: 0 0 8pt 0; line-height: 1.5;">';
                        $html .= $entitasCounter . '. ' . htmlspecialchars($entitasData['entitas']->nama);
                        $html .= '</h4>';
                        $html .= '<div style="margin-left: 20pt;">';

                        // Hasil Usaha (Done)
                        if (!empty($entitasData['done'])) {
                            $html .= '<div style="margin: 0 0 10pt 0;">';
                            $html .= '<h5 style="font-size: 11pt; font-weight: bold; margin: 0 0 5pt 0;">Hasil Usaha</h5>';
                            $html .= '<div style="margin-left: 10pt;">';
                            foreach ($entitasData['done'] as $index => $task) {
                                $line2Parts = [];
                                if ($task->deskripsi) {
                                    $line2Parts[] = htmlspecialchars($task->deskripsi);
                                }
                                if ($task->assignedUser) {
                                    $line2Parts[] = 'PIC: ' . htmlspecialchars($task->assignedUser->namaAnggota ?? 'N/A');
                                }
                                $html .= '<p style="margin: 0 0 5pt 0; font-size: 11pt; line-height: 1.5;">';
                                $html .= '<strong>' . ($index + 1) . '. ' . htmlspecialchars($task->judul) . '</strong>';
                                if (!empty($line2Parts)) {
                                    $html .= '<br>' . implode(' | ', $line2Parts);
                                }
                                $html .= '</p>';
                            }
                            $html .= '</div></div>';
                        }

                        // Kendala (Rejected & Pending)
                        if (!empty($entitasData['rejected']) || !empty($entitasData['pending'])) {
                            $html .= '<div style="margin: 0 0 10pt 0;">';
                            $html .= '<h5 style="font-size: 11pt; font-weight: bold; margin: 0 0 5pt 0;">Kendala</h5>';
                            $html .= '<div style="margin-left: 10pt;">';
                            $kendalaIndex = 1;
                            foreach ($entitasData['rejected'] as $task) {
                                $html .= '<p style="margin: 0 0 5pt 0; font-size: 11pt; line-height: 1.5;">';
                                $html .= '<strong>' . ($kendalaIndex++) . '. ' . htmlspecialchars($task->judul) . '</strong> ';
                                $html .= '<span style="background: #ccc; padding: 1pt 3pt; font-size: 9pt;">DITOLAK</span>';
                                if ($task->deskripsi) {
                                    $html .= '<br>' . nl2br(htmlspecialchars($task->deskripsi));
                                }
                                $html .= '</p>';
                            }
                            foreach ($entitasData['pending'] as $task) {
                                $html .= '<p style="margin: 0 0 5pt 0; font-size: 11pt; line-height: 1.5;">';
                                $html .= '<strong>' . ($kendalaIndex++) . '. ' . htmlspecialchars($task->judul) . '</strong> ';
                                $html .= '<span style="background: #fcc; padding: 1pt 3pt; font-size: 9pt;">TERTUNDA</span>';
                                if ($task->deskripsi) {
                                    $html .= '<br>' . nl2br(htmlspecialchars($task->deskripsi));
                                }
                                $html .= '</p>';
                            }
                            $html .= '</div></div>';
                        }

                        // Program Kerja Mendatang (Todo)
                        if (!empty($entitasData['todo'])) {
                            $html .= '<div style="margin: 0 0 10pt 0;">';
                            $html .= '<h5 style="font-size: 11pt; font-weight: bold; margin: 0 0 5pt 0;">Program Kerja Mendatang</h5>';
                            $html .= '<div style="margin-left: 10pt;">';
                            foreach ($entitasData['todo'] as $index => $task) {
                                $line2Parts = [];
                                if ($task->deskripsi) {
                                    $line2Parts[] = htmlspecialchars($task->deskripsi);
                                }
                                $details = [];
                                if ($task->deadline) {
                                    $details[] = 'Target: ' . $task->deadline->format('d-m-Y');
                                }
                                if ($task->progress > 0) {
                                    $details[] = 'Progress: ' . $task->progress . '%';
                                }
                                if (!empty($details)) {
                                    $line2Parts[] = implode(', ', $details);
                                }
                                $html .= '<p style="margin: 0 0 5pt 0; font-size: 11pt; line-height: 1.5;">';
                                $html .= '<strong>' . ($index + 1) . '. ' . htmlspecialchars($task->judul) . '</strong>';
                                if (!empty($line2Parts)) {
                                    $html .= '<br>' . implode(' | ', $line2Parts);
                                }
                                $html .= '</p>';
                            }
                            $html .= '</div></div>';
                        }

                        $html .= '</div></div>'; // close entitas body + wrapper
                    }

                    $html .= '</div>'; // close modul wrapper
                }

                $html .= '</div>'; // close campus wrapper
            }
        }
        
        $html .= '</div></body></html>';
        
        return $html;
    }
}
