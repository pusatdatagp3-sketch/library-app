<?php

declare(strict_types=1);

use App\Web\Perpustakaan\Model\KunjunganEntity;
use Yiisoft\Html\Html;

/**
 * @var string $mode 'input' | 'kelas' | 'rayon' | 'konsulat'
 * @var array<KunjunganEntity|array<string, mixed>> $data
 * @var string $rentangWaktu
 * @var string $labelRentang
 * @var string $periodeDetail
 * @var int $grandTotalAgregasi
 */

$totalItems = count($data);

$modeLabels = [
    'input'    => 'Data Detail Per-Input',
    'kelas'    => 'Rekapitulasi Per-Kelas',
    'rayon'    => 'Rekapitulasi Per-Rayon',
    'konsulat' => 'Rekapitulasi Per-Konsulat',
];

$titleMode = $modeLabels[$mode] ?? 'Rekapitulasi Kunjungan';
$tanggalCetak = date('d F Y, H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Kunjungan - <?= Html::encode($titleMode) ?></title>
    <style>
        /* CSS Reset & Dasar Cetak */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            font-size: 11pt;
            line-height: 1.45;
            padding: 20mm 15mm;
        }

        /* Tombol Aksi Non-Cetak di Layar */
        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            z-index: 9999;
            font-size: 13px;
        }

        .no-print-bar button {
            cursor: pointer;
            padding: 6px 14px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .btn-print {
            background-color: #8b5cf6;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #7c3aed;
        }

        .btn-close {
            background-color: #475569;
            color: #ffffff;
        }
        .btn-close:hover {
            background-color: #334155;
        }

        /* Kop Surat Institusi */
        .kop-surat {
            text-align: center;
            margin-bottom: 20px;
        }

        .kop-instansi {
            font-size: 14pt;
            font-weight: 800;
            letter-spacing: 1px;
            color: #4338ca;
            text-transform: uppercase;
        }

        .kop-sub {
            font-size: 12pt;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }

        .kop-alamat {
            font-size: 9.5pt;
            color: #64748b;
            margin-top: 3px;
        }

        .kop-divider-double {
            border: none;
            border-top: 3px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            height: 4px;
            margin: 10px 0 20px 0;
        }

        /* Header Laporan */
        .report-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .report-title {
            font-size: 13pt;
            font-weight: 700;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .report-meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10pt;
            border-collapse: collapse;
        }

        .report-meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        /* Tabel Data Cetak */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 25px;
        }

        table.data-table th, 
        table.data-table td {
            border: 1px solid #475569;
            padding: 6px 8px;
            vertical-align: middle;
        }

        table.data-table thead th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
            font-size: 9.5pt;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.data-table tfoot th {
            background-color: #f1f5f9;
            font-weight: 700;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 220px;
            text-align: center;
            font-size: 10pt;
        }

        .signature-space {
            height: 65px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
        }

        /* Khusus Mode Media Print */
        @media print {
            body {
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
            table.data-table {
                page-break-inside: auto;
            }
            table.data-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi Layar (Akan otomatis disembunyikan saat dicetak) -->
    <div class="no-print-bar">
        <div>
            <strong>Mode Cetak Dokumen Rekap Kunjungan</strong> &bull; KUTUBIA
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-print" onclick="window.print()">
                🖨️ Cetak / Simpan PDF
            </button>
            <button class="btn-close" onclick="window.close()">
                ✕ Tutup Jendela
            </button>
        </div>
    </div>

    <!-- ── KOP SURAT RESMI ──────────────────────────────────────────────── -->
    <div class="kop-surat">
        <div class="kop-instansi">KUTUBIA &bull; PERPUSTAKAAN PESANTREN</div>
        <div class="kop-sub">SISTEM INFORMASI &amp; MANAJEMEN PRESENSI KUNJUNGAN SANTRI</div>
        <div class="kop-alamat">Pondok Modern Darussalam &bull; Terintegrasi Database Santri &amp; Barcode Scanner</div>
        <div class="kop-divider-double"></div>
    </div>

    <!-- ── HEADER LAPORAN ───────────────────────────────────────────────── -->
    <div class="report-header">
        <h1 class="report-title">LAPORAN REKAPITULASI KUNJUNGAN SANTRI</h1>
    </div>

    <!-- Metadata Informasi Laporan -->
    <table class="report-meta-table">
        <tr>
            <td style="width: 150px;"><strong>Kategori / Mode</strong></td>
            <td style="width: 10px;">:</td>
            <td><?= Html::encode($titleMode) ?></td>
            <td style="width: 130px;"><strong>Tanggal Cetak</strong></td>
            <td style="width: 10px;">:</td>
            <td style="width: 180px;"><?= Html::encode($tanggalCetak) ?> WIB</td>
        </tr>
        <tr>
            <td><strong>Filter Rentang Waktu</strong></td>
            <td>:</td>
            <td><?= Html::encode($labelRentang) ?> (<?= Html::encode($periodeDetail) ?>)</td>
            <td><strong>Total Rekaman</strong></td>
            <td>:</td>
            <td><strong><?= number_format($totalItems) ?></strong> <?= $mode === 'input' ? 'Transaksi' : 'Kelompok' ?></td>
        </tr>
    </table>

    <!-- ── TABEL DATA UTAMA ─────────────────────────────────────────────── -->
    <?php if ($mode === 'input'): ?>
        <!-- Tabel Detail Per-Input -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th style="width: 130px;">Waktu Kunjungan</th>
                    <th style="width: 85px;">Stambuk</th>
                    <th>Nama Lengkap Santri</th>
                    <th style="width: 75px;">Kelas</th>
                    <th style="width: 120px;">Rayon</th>
                    <th style="width: 110px;">Konsulat</th>
                    <th style="width: 130px;">Petugas Piket</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 25px; color: #64748b;">
                            Tidak ada rekaman data kunjungan untuk filter waktu yang dipilih.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $index => $item): ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td class="text-center font-mono" style="font-size: 8.5pt;">
                                <?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('d/m/Y H:i') : '-' ?>
                            </td>
                            <td class="text-center font-mono" style="font-weight: 600;">
                                <?= Html::encode((string)$item->stambuk) ?>
                            </td>
                            <td><?= Html::encode((string)($item->nama_santri ?? '-')) ?></td>
                            <td class="text-center"><?= Html::encode((string)($item->kelas ?? '-')) ?></td>
                            <td><?= Html::encode((string)($item->rayon ?? '-')) ?></td>
                            <td><?= Html::encode((string)($item->konsulat ?? '-')) ?></td>
                            <td><?= Html::encode((string)($item->penginput ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- Tabel Agregasi (Kelas / Rayon / Konsulat) -->
        <?php
        $colNama = match ($mode) {
            'kelas'    => 'NAMA KELAS',
            'rayon'    => 'NAMA RAYON',
            'konsulat' => 'NAMA KONSULAT',
            default    => 'KATEGORI',
        };
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">NO</th>
                    <th class="text-start"><?= $colNama ?></th>
                    <th style="width: 180px;" class="text-end">JUMLAH KUNJUNGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="3" class="text-center" style="padding: 25px; color: #64748b;">
                            Tidak ada rekaman data rekapitulasi untuk filter waktu yang dipilih.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $index => $row): ?>
                        <?php
                        $nama = (string)($row['nama'] ?? $row[$mode] ?? 'Tidak Terdata');
                        $total = (int)($row['total'] ?? 0);
                        ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td class="text-start" style="font-weight: 600;"><?= Html::encode($nama) ?></td>
                            <td class="text-end font-mono" style="font-weight: 700;"><?= number_format($total) ?> Santri</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($data)): ?>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end" style="padding-right: 15px;">TOTAL KESELURUHAN KUNJUNGAN:</th>
                        <th class="text-end font-mono" style="font-size: 10.5pt; color: #1e293b;"><?= number_format($grandTotalAgregasi) ?> Santri</th>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    <?php endif; ?>

    <!-- ── TANDA TANGAN / PENGESAHAN DOKUMEN ────────────────────────────── -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div>Kepala Bagian Perpustakaan</div>
            <div class="signature-space"></div>
            <div class="signature-name">( ............................................ )</div>
            <div style="font-size: 8.5pt; color: #64748b; margin-top: 2px;">NIP / Staf Pengasuhan</div>
        </div>

        <div class="signature-box">
            <div>Petugas Piket Jaga</div>
            <div>Perpustakaan KUTUBIA</div>
            <div class="signature-space"></div>
            <div class="signature-name">( ............................................ )</div>
            <div style="font-size: 8.5pt; color: #64748b; margin-top: 2px;">Petugas Piket Harian</div>
        </div>
    </div>

    <!-- ── AUTO TRIGGER PRINT JAVASCRIPT ────────────────────────────────── -->
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 400);
        });
    </script>
</body>
</html>
