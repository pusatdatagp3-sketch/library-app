<?php

declare(strict_types=1);

namespace App\Web\Laporan\Controller;

use App\Web\Entitas\Model\Entitas;
use App\Web\Modul\Model\Modul;
use App\Web\Program\Model\Program;
use App\Web\Task\Model\Task;
use App\Web\Laporan\Service\PdfExportService;
use Cycle\ORM\ORMInterface;
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
        private ORMInterface $orm
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
        
        // Calculate Wednesday of current week (or next week if today is Sunday)
        // Sunday (0) -> skip to next Wed
        // Monday-Saturday (1-6) -> find Wed of this week
        
        if ($currentDay == 0) {
            // Today is Sunday: next Wednesday is 3 days away
            $daysToWednesday = 3;
        } else {
            // Mon-Sat: Wednesday is at position 3, so:
            $daysToWednesday = 3 - $currentDay;
        }
        
        $wednesday = clone $today;
        $wednesday->modify("{$daysToWednesday} days");
        
        // Calculate Friday (2 days after Wednesday)
        $friday = clone $wednesday;
        $friday->modify('+2 days');
        
        // Get dates from query params or use defaults
        $dateFrom = isset($queryParams['date_from']) 
            ? new \DateTime($queryParams['date_from'])
            : $wednesday;
        $dateTo = isset($queryParams['date_to']) 
            ? new \DateTime($queryParams['date_to'])
            : $friday;

        // Get all moduls
        $moduls = $this->modulRepository->select()->orderBy('id', 'ASC')->fetchAll();

        // Get all entitas with their modul
        $allEntitas = $this->entitasRepository->select()->fetchAll();
        
        // Get all tasks with related data
        $tasks = $this->taskRepository->select()
            ->load('kanbanColumn')
            ->load('program')
            ->load('program.entitas')
            ->load('program.entitas.modul')
            ->load('assignedUser')
            ->fetchAll();

        // Filter tasks by date range if needed
        // For now, we'll include all tasks and let the view handle filtering
        
        // Build hierarchical data: Modul -> Entitas -> Task Status
        $reportData = [];
        
        foreach ($moduls as $modul) {
            $reportData[$modul->id] = [
                'modul' => $modul,
                'entitas' => []
            ];
        }

        // Group entitas by modul
        foreach ($allEntitas as $entitas) {
            if ($entitas->modulId !== null && isset($reportData[$entitas->modulId])) {
                $reportData[$entitas->modulId]['entitas'][$entitas->id] = [
                    'entitas' => $entitas,
                    'todo' => [],
                    'done' => [],
                    'pending' => [],
                    'rejected' => []
                ];
            }
        }

        // Categorize tasks
        foreach ($tasks as $task) {
            if ($task->kanbanColumn === null || $task->program === null) {
                continue;
            }

            $program = $task->program;
            if ($program->entitas === null || $program->entitas->modulId === null) {
                continue;
            }

            $modulId = $program->entitas->modulId;
            $entitasId = $program->entitasId;

            // Check if entitas exists in report data
            if (!isset($reportData[$modulId]['entitas'][$entitasId])) {
                continue;
            }

            $colNameLower = strtolower($task->kanbanColumn->nama);
            
            // Categorize based on kanban column name
            $isTodo = str_contains($colNameLower, 'todo') || 
                     str_contains($colNameLower, 'to do') || 
                     str_contains($colNameLower, 'rencana');
            $isDone = str_contains($colNameLower, 'done') || 
                     str_contains($colNameLower, 'selesai');
            $isPending = str_contains($colNameLower, 'pending') || 
                        str_contains($colNameLower, 'tunda');
            $isRejected = str_contains($colNameLower, 'rejected') || 
                         str_contains($colNameLower, 'tolak');

            // Skip if none of the categories match
            if (!$isTodo && !$isDone && !$isPending && !$isRejected) {
                continue;
            }

            // Add task to appropriate category
            if ($isDone) {
                $reportData[$modulId]['entitas'][$entitasId]['done'][] = $task;
            } elseif ($isRejected) {
                $reportData[$modulId]['entitas'][$entitasId]['rejected'][] = $task;
            } elseif ($isPending) {
                $reportData[$modulId]['entitas'][$entitasId]['pending'][] = $task;
            } elseif ($isTodo) {
                $reportData[$modulId]['entitas'][$entitasId]['todo'][] = $task;
            }
        }

        // Remove moduls with no entitas
        foreach ($reportData as $modulId => $data) {
            if (empty($data['entitas'])) {
                unset($reportData[$modulId]);
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'reportData' => $reportData,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function exportPdf(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $parsedBody = $request->getParsedBody();
        
        // Get date range from query params or parsed body
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
        $dateToVal = $parsedBody['date_to'] ?? $queryParams['date_to'] ?? null;

        $dateFrom = !empty($dateFromVal) && is_string($dateFromVal)
            ? new \DateTime($dateFromVal)
            : $wednesday;
        $dateTo = !empty($dateToVal) && is_string($dateToVal)
            ? new \DateTime($dateToVal)
            : $friday;

        // Get all moduls, entitas, and tasks (same as index method)
        $moduls = $this->modulRepository->select()->orderBy('id', 'ASC')->fetchAll();
        $allEntitas = $this->entitasRepository->select()->fetchAll();
        
        $tasks = $this->taskRepository->select()
            ->load('kanbanColumn')
            ->load('program')
            ->load('program.entitas')
            ->load('program.entitas.modul')
            ->load('assignedUser')
            ->fetchAll();

        // Build hierarchical data
        $reportData = [];
        
        foreach ($moduls as $modul) {
            $reportData[$modul->id] = [
                'modul' => $modul,
                'entitas' => []
            ];
        }

        foreach ($allEntitas as $entitas) {
            if ($entitas->modulId !== null && isset($reportData[$entitas->modulId])) {
                $reportData[$entitas->modulId]['entitas'][$entitas->id] = [
                    'entitas' => $entitas,
                    'todo' => [],
                    'done' => [],
                    'pending' => [],
                    'rejected' => []
                ];
            }
        }

        // Categorize tasks
        foreach ($tasks as $task) {
            if ($task->kanbanColumn === null || $task->program === null) {
                continue;
            }

            $program = $task->program;
            if ($program->entitas === null || $program->entitas->modulId === null) {
                continue;
            }

            $modulId = $program->entitas->modulId;
            $entitasId = $program->entitasId;

            if (!isset($reportData[$modulId]['entitas'][$entitasId])) {
                continue;
            }

            $colNameLower = strtolower($task->kanbanColumn->nama);
            
            $isTodo = str_contains($colNameLower, 'todo') || 
                     str_contains($colNameLower, 'to do') || 
                     str_contains($colNameLower, 'rencana');
            $isDone = str_contains($colNameLower, 'done') || 
                     str_contains($colNameLower, 'selesai');
            $isPending = str_contains($colNameLower, 'pending') || 
                        str_contains($colNameLower, 'tunda');
            $isRejected = str_contains($colNameLower, 'rejected') || 
                         str_contains($colNameLower, 'tolak');

            if (!$isTodo && !$isDone && !$isPending && !$isRejected) {
                continue;
            }

            if ($isDone) {
                $reportData[$modulId]['entitas'][$entitasId]['done'][] = $task;
            } elseif ($isRejected) {
                $reportData[$modulId]['entitas'][$entitasId]['rejected'][] = $task;
            } elseif ($isPending) {
                $reportData[$modulId]['entitas'][$entitasId]['pending'][] = $task;
            } elseif ($isTodo) {
                $reportData[$modulId]['entitas'][$entitasId]['todo'][] = $task;
            }
        }

        // Remove moduls with no entitas
        foreach ($reportData as $modulId => $data) {
            if (empty($data['entitas'])) {
                unset($reportData[$modulId]);
            }
        }

        // Generate HTML directly (without rendering view to avoid SVG icons)
        $html = $this->generateHtmlReport($reportData, $dateFrom, $dateTo);

        // Generate PDF
        $pdfService = new PdfExportService();
        $pdf = $pdfService->generatePdf($html);

        // Return PDF response
        $response = $this->responseFactory->createResponse();
        $response = $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="laporan-program-kerja-' . date('Y-m-d') . '.pdf"')
            ->withHeader('Content-Length', (string)strlen($pdf));
        
        $response->getBody()->write($pdf);
        
        return $response;
    }

    private function generateHtmlReport(array $reportData, \DateTime $dateFrom, \DateTime $dateTo): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Laporan Program Kerja</title></head><body>';
        
        $kopPath = '';
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $path = dirname(__DIR__, 4) . '/public/assets/kop-surat.' . $ext;
            if (is_file($path)) {
                $kopPath = $path;
                break;
            }
        }
        
        if ($kopPath !== '') {
            $ext = pathinfo($kopPath, PATHINFO_EXTENSION);
            $mimeType = strtolower($ext) === 'png' ? 'image/png' : 'image/jpeg';
            $imageData = base64_encode(file_get_contents($kopPath));
            
            $html .= '<div style="margin: -1cm -1cm 0cm -1cm; width: 21cm; height: 4cm; overflow: hidden;">';
            $html .= '    <img src="data:' . $mimeType . ';base64,' . $imageData . '" style="width: 21cm; height: 4.5cm; object-fit: fill;" />';
            $html .= '</div>';
        }
        
        // No margins
        $html .= '<div style="margin: 0; padding: 0; font-family: \'Book Antiqua\', Georgia, serif; line-height: 1.5;">';
        
        // Title
        $html .= '<h1 style="text-align: center; font-size: 16pt; font-weight: bold; margin: 0 0 12pt 0;">Laporan Program Kerja</h1>';
        
        // Date range
        $html .= '<p style="text-align: center; font-size: 11pt; margin: 0 0 18pt 0; line-height: 1.5;">';
        $html .= 'Periode: <strong>' . $dateFrom->format('d-m-Y') . '</strong> s/d <strong>' . $dateTo->format('d-m-Y') . '</strong><br>';
        $html .= 'Tanggal Generate: ' . date('d-m-Y H:i:s');
        $html .= '</p>';
        
        if (empty($reportData)) {
            $html .= '<p style="text-align: center; color: #666; margin: 12pt 0;">Tidak ada data untuk periode yang dipilih.</p>';
        } else {
            $modulCounter = 0;
            foreach ($reportData as $modulId => $modulData) {
                $modulCounter++;
                $modulLetter = chr(64 + $modulCounter); // A, B, C, D, etc.
                
                $html .= '<div style="margin: 0 0 18pt 0;">';
                
                // H1: Modul dengan format A. B. C.
                $html .= '<h2 style="font-size: 14pt; font-weight: bold; border-bottom: 1pt solid #000; padding: 0 0 6pt 0; margin: 0 0 12pt 0;">';
                $html .= $modulLetter . '. ' . strtoupper(htmlspecialchars($modulData['modul']->nama ?? 'Unknown'));
                $html .= '</h2>';
                
                $entitasCounter = 0;
                foreach ($modulData['entitas'] as $entitasId => $entitasData) {
                    $entitasCounter++;
                    
                    $html .= '<div style="margin: 0 0 12pt 0;">';
                    
                    // H2: Entitas dengan format 1. 2. 3.
                    $html .= '<h3 style="font-size: 12pt; font-weight: bold; border-left: 2pt solid #333; padding: 0 0 0 12pt; margin: 0 0 10pt 0; line-height: 1.5;">';
                    $html .= $entitasCounter . '. ' . htmlspecialchars($entitasData['entitas']->nama);
                    $html .= '</h3>';
                    
                    $html .= '<div style="margin-left: 20pt;">';
                    
                    // Hasil Usaha
                    if (!empty($entitasData['done'])) {
                        $html .= '<div style="margin: 0 0 12pt 0;">';
                        $html .= '<h4 style="font-size: 11pt; font-weight: bold; margin: 0 0 6pt 0;">Hasil Usaha</h4>';
                        $html .= '<div style="margin-left: 12pt;">';
                        foreach ($entitasData['done'] as $index => $task) {
                            $line2Parts = [];
                            if ($task->deskripsi) {
                                $line2Parts[] = htmlspecialchars($task->deskripsi);
                            }
                            if ($task->assignedUser) {
                                $line2Parts[] = 'PIC: ' . htmlspecialchars($task->assignedUser->namaAnggota ?? 'N/A');
                            }
                            
                            $html .= '<p style="margin: 0 0 6pt 0; font-size: 11pt; line-height: 1.5;">';
                            $html .= '<strong>' . ($index + 1) . '. ' . htmlspecialchars($task->judul) . '</strong>';
                            if (!empty($line2Parts)) {
                                $html .= '<br>' . implode(' | ', $line2Parts);
                            }
                            $html .= '</p>';
                        }
                        $html .= '</div></div>';
                    }
                    
                    // Kendala
                    if (!empty($entitasData['rejected']) || !empty($entitasData['pending'])) {
                        $html .= '<div style="margin: 0 0 12pt 0;">';
                        $html .= '<h4 style="font-size: 11pt; font-weight: bold; margin: 0 0 6pt 0;">Kendala</h4>';
                        $html .= '<div style="margin-left: 12pt;">';
                        $kendalaIndex = 1;
                        foreach ($entitasData['rejected'] as $task) {
                            $html .= '<p style="margin: 0 0 6pt 0; font-size: 11pt; line-height: 1.5;">';
                            $html .= '<strong>' . ($kendalaIndex++) . '. ' . htmlspecialchars($task->judul) . '</strong> ';
                            $html .= '<span style="background: #ccc; padding: 1pt 3pt; font-size: 9pt;">DITOLAK</span>';
                            if ($task->deskripsi) {
                                $html .= '<br>' . nl2br(htmlspecialchars($task->deskripsi));
                            }
                            $html .= '</p>';
                        }
                        foreach ($entitasData['pending'] as $task) {
                            $html .= '<p style="margin: 0 0 6pt 0; font-size: 11pt; line-height: 1.5;">';
                            $html .= '<strong>' . ($kendalaIndex++) . '. ' . htmlspecialchars($task->judul) . '</strong> ';
                            $html .= '<span style="background: #fcc; padding: 1pt 3pt; font-size: 9pt;">TERTUNDA</span>';
                            if ($task->deskripsi) {
                                $html .= '<br>' . nl2br(htmlspecialchars($task->deskripsi));
                            }
                            $html .= '</p>';
                        }
                        $html .= '</div></div>';
                    }
                    
                    // Program Kerja Minggu Depan
                    if (!empty($entitasData['todo'])) {
                        $html .= '<div style="margin: 0 0 12pt 0;">';
                        $html .= '<h4 style="font-size: 11pt; font-weight: bold; margin: 0 0 6pt 0;">Program Kerja Minggu Depan</h4>';
                        $html .= '<div style="margin-left: 12pt;">';
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
                            
                            $html .= '<p style="margin: 0 0 6pt 0; font-size: 11pt; line-height: 1.5;">';
                            $html .= '<strong>' . ($index + 1) . '. ' . htmlspecialchars($task->judul) . '</strong>';
                            if (!empty($line2Parts)) {
                                $html .= '<br>' . implode(' | ', $line2Parts);
                            }
                            $html .= '</p>';
                        }
                        $html .= '</div></div>';
                    }
                    
                    $html .= '</div></div>';
                }
                
                $html .= '</div>';
            }
        }
        
        $html .= '</div></body></html>';
        
        return $html;
    }
}

