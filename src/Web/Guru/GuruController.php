<?php

declare(strict_types=1);

namespace App\Web\Guru;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class GuruController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private GuruService $guruService,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    public function index(): ResponseInterface
    {
        $guruList = $this->guruService->getAllGuru();
        return $this->viewRenderer->render(__DIR__ . '/views/index', [
            'guruList' => $guruList,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $data = [
            'stambuk' => '',
            'nama' => '',
            'daerah' => '',
            'konsulat' => '',
            'email' => '',
            'no_telp' => '',
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->guruService->createGuru($data, $errors)) {
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/create', [
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $kdg = (int) $this->currentRoute->getArgument('kdg');
        $guru = $this->guruService->getGuruById($kdg);

        if ($guru === null) {
            return $this->responseFactory->createResponse(404);
        }

        $errors = [];
        $data = [
            'stambuk' => $guru->stambuk,
            'nama' => $guru->nama,
            'daerah' => $guru->daerah,
            'konsulat' => $guru->konsulat,
            'email' => $guru->email,
            'no_telp' => $guru->noTelp,
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->guruService->updateGuru($kdg, $data, $errors)) {
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/update', [
            'guru' => $guru,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $kdg = (int) $this->currentRoute->getArgument('kdg');
        $this->guruService->deleteGuru($kdg);

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
    }

    public function downloadTemplate(): ResponseInterface
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Stambuk');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Daerah');
        $sheet->setCellValue('D1', 'Konsulat');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Nomor Telefon');

        // Add sample row
        $sheet->setCellValue('A2', '20260001');
        $sheet->setCellValue('B2', 'Ahmad Fauzi');
        $sheet->setCellValue('C2', 'Jawa Timur');
        $sheet->setCellValue('D2', 'Gontor 1');
        $sheet->setCellValue('E2', 'ahmad@gmail.com');
        $sheet->setCellValue('F2', '081234567890');

        // Format headers bold and adjust column widths
        foreach (range('A', 'F') as $col) {
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Output to a temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        $writer->save($tempFile);

        $stream = $this->responseFactory->createResponse()->getBody();
        $handle = fopen($tempFile, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                $stream->write(fread($handle, 8192));
            }
            fclose($handle);
        }
        unlink($tempFile);

        return $this->responseFactory->createResponse(200)
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment; filename="template_guru.xlsx"')
            ->withHeader('Cache-Control', 'max-age=0');
    }

    public function upload(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['excel_file'] ?? null;

        if ($uploadedFile === null || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $this->flash->set('errors', ['Gagal mengunggah file. Silakan coba lagi.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
        }

        // Validate extension
        $clientFilename = $uploadedFile->getClientFilename();
        $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            $this->flash->set('errors', ['Format file tidak valid. Harap gunakan file Excel (.xlsx, .xls) atau CSV.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_import_');
        try {
            $uploadedFile->moveTo($tempFile);
            $errors = [];
            $successCount = $this->guruService->importExcel($tempFile, $errors);

            if ($successCount > 0) {
                $this->flash->set('success', "Berhasil memproses {$successCount} data guru (Insert/Update).");
            }

            if (!empty($errors)) {
                $this->flash->set('errors', $errors);
            }
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Terjadi kesalahan saat memproses file: ' . $e->getMessage()]);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
    }
}
