<?php

declare(strict_types=1);

namespace App\Web\Shared\Service;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class FileCompressionService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Compress and save the uploaded file.
     * 
     * @param UploadedFileInterface $file
     * @param string $uploadDir Physical upload directory path (e.g. /var/www/.../public/uploads/...)
     * @param string $relativeDir Relative directory path for database storage (e.g. uploads/...)
     * @param string $prefix Prefix for the filename (e.g. notulen_, dokumentasi_)
     * @return string The relative path of the saved file.
     */
    public function compressAndSave(UploadedFileInterface $file, string $uploadDir, string $relativeDir, string $prefix): string
    {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $clientFilename = $file->getClientFilename();
        $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
        $uniqid = uniqid($prefix);

        // Define temp file path to move the uploaded file first
        $tempFile = $uploadDir . '/' . $uniqid . '_tmp.' . $extension;
        $file->moveTo($tempFile);

        try {
            // 1. Handle Images (jpg, jpeg, png, gif, webp) -> convert to webp
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $targetFilename = $uniqid . '.webp';
                $targetFile = $uploadDir . '/' . $targetFilename;

                if ($this->convertToWebp($tempFile, $targetFile)) {
                    @unlink($tempFile);
                    return $relativeDir . '/' . $targetFilename;
                }
            }

            // 2. Handle PDF -> compress via Ghostscript if available
            if ($extension === 'pdf') {
                $targetFilename = $uniqid . '.pdf';
                $targetFile = $uploadDir . '/' . $targetFilename;

                if ($this->compressPdf($tempFile, $targetFile)) {
                    @unlink($tempFile);
                    return $relativeDir . '/' . $targetFilename;
                }
            }

            // 3. Fallback: Keep original extension and file
            $targetFilename = $uniqid . '.' . $extension;
            $targetFile = $uploadDir . '/' . $targetFilename;
            rename($tempFile, $targetFile);
            return $relativeDir . '/' . $targetFilename;
        } catch (\Throwable $e) {
            $this->logger->error('Error during file compression: ' . $e->getMessage());
            // If anything goes wrong, try to keep the original file
            $targetFilename = $uniqid . '.' . $extension;
            $targetFile = $uploadDir . '/' . $targetFilename;
            if (file_exists($tempFile)) {
                rename($tempFile, $targetFile);
                return $relativeDir . '/' . $targetFilename;
            }
            throw $e;
        }
    }

    private function convertToWebp(string $sourcePath, string $targetPath, int $quality = 75): bool
    {
        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return false;
        }

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                if ($image !== false) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if ($image === false) {
            return false;
        }

        $result = @imagewebp($image, $targetPath, $quality);
        @imagedestroy($image);
        return $result;
    }

    private function compressPdf(string $sourcePath, string $targetPath): bool
    {
        // Check if gs is available
        $gsPath = '/usr/bin/gs';
        if (!file_exists($gsPath)) {
            return false;
        }

        // Compress PDF using Ghostscript (screen level compression)
        $command = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
            escapeshellarg($gsPath),
            escapeshellarg($targetPath),
            escapeshellarg($sourcePath)
        );

        $output = [];
        $returnCode = 0;
        @exec($command, $output, $returnCode);

        // If compression succeeded and generated a file, verify it's not empty
        if ($returnCode === 0 && file_exists($targetPath) && filesize($targetPath) > 0) {
            // Only use the compressed file if it's actually smaller than the original
            if (filesize($targetPath) < filesize($sourcePath)) {
                return true;
            }
            // If compressed is larger or equal, delete it and let fallback handle it
            @unlink($targetPath);
        }

        return false;
    }
}
