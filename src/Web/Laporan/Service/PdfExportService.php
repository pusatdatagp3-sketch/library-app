<?php

declare(strict_types=1);

namespace App\Web\Laporan\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

final class PdfExportService
{
    public function generatePdf(
        string $htmlContent,
        string $filename = 'laporan.pdf',
        array $options = []
    ): string {
        // Setup DOMPDF options
        $pdfOptions = new Options();
        $pdfOptions->setDefaultFont('Book Antiqua');
        $pdfOptions->setIsRemoteEnabled(false);
        $pdfOptions->setIsHtml5ParserEnabled(true);

        $dompdf = new Dompdf($pdfOptions);

        // Add CSS for formal formatting
        $styledHtml = $this->addPdfStyling($htmlContent);

        // Load HTML
        $dompdf->loadHtml($styledHtml);

        // Set page size
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Get PDF content
        $pdf = $dompdf->output();

        return $pdf;
    }

    /**
     * Remove SVG icons from HTML (dompdf doesn't support SVG well)
     */
    private function removeSvgIcons(string $html): string
    {
        // Remove icon tags like <i class="ri-*"></i>
        $html = preg_replace('/<i\s+class="ri-[^"]*"[^>]*><\/i>\s*/i', '', $html);
        
        return $html;
    }

    /**
     * Add formal styling to HTML for PDF (Book Antiqua, font 12)
     */
    private function addPdfStyling(string $html): string
    {
        // Remove SVG icons first
        $html = $this->removeSvgIcons($html);

        $css = <<<'CSS'
        <style>
            @page {
                margin: 1cm;
                padding: 0;
            }
            
            * {
                font-family: 'Book Antiqua', Georgia, serif;
                line-height: 1.6;
                color: #000;
            }
            
            body {
                font-size: 12pt;
                font-family: 'Book Antiqua', Georgia, serif;
                margin: 0;
                padding: 0;
            }
            
            .crud-header { display: none !important; }
            .card:first-of-type { display: none !important; }
            button, form, .btn { display: none !important; }
            
            h1 {
                font-size: 18pt;
                font-weight: bold;
                border-bottom: 2pt solid #000;
                padding-bottom: 10pt;
                margin: 20pt 0 10pt 0;
            }
            
            h2 {
                font-size: 16pt;
                font-weight: bold;
                border-bottom: 1pt solid #000;
                padding-bottom: 8pt;
                margin: 15pt 0 10pt 0;
            }
            
            h3 {
                font-size: 14pt;
                font-weight: bold;
                border-left: 3pt solid #333;
                padding-left: 10pt;
                margin: 12pt 0 8pt 0;
            }
            
            h4, h5 {
                font-size: 12pt;
                font-weight: bold;
                margin: 8pt 0;
            }
            
            p {
                font-size: 11pt;
                margin: 10pt 0;
                text-align: justify;
                line-height: 1.5;
            }
            
            .card {
                border: 1pt solid #999;
                page-break-inside: avoid;
                margin-bottom: 10pt;
                padding: 10pt;
            }
            
            div[style*="border-left"] {
                border-left: 3pt solid #333 !important;
                padding-left: 10pt !important;
                margin-bottom: 5pt;
                page-break-inside: avoid;
            }
            
            .badge {
                background: #ccc;
                color: #000;
                padding: 2pt 4pt;
                border-radius: 2pt;
                font-size: 10pt;
                font-weight: bold;
                display: inline-block;
            }
            
            a { color: #000; text-decoration: none; }
            
            small { font-size: 10pt; }
        </style>
        CSS;

        // Insert CSS into HTML
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $css . '</head>', $html);
        } else {
            $html = $css . $html;
        }

        return $html;
    }
}
