<?php

declare(strict_types=1);

namespace App\Console;

use App\Shared\Service\SidasApiClient;
use App\Web\Perpustakaan\Model\SiswaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Yiisoft\Yii\Console\ExitCode;

use function count;
use function sprintf;
use function usleep;

/**
 * SyncSantriCommand
 *
 * Command CLI untuk menyinkronkan seluruh santri aktif dari SIDAS API
 * ke tabel cache lokal `santri_cache` di Kutubia.
 *
 * Contoh penggunaan:
 *   php ./yii santri:sync
 *   php ./yii santri:sync --per-page=100
 */
#[AsCommand(
    name: 'santri:sync',
    description: 'Sinkronisasi master data santri aktif dari SIDAS ke cache lokal Kutubia'
)]
final class SyncSantriCommand extends Command
{
    public function __construct(
        private SidasApiClient $sidasClient,
        private SiswaRepository $siswaRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'per-page',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Jumlah data per halaman request (maksimal 100)',
                100
            )
            ->addOption(
                'delay-ms',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Jeda antar-request dalam milidetik untuk menjaga beban server',
                100
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>══════════════════════════════════════════════════════</info>');
        $output->writeln('<info> [KUTUBIA] Memulai Sinkronisasi Santri dari SIDAS API  </info>');
        $output->writeln('<info>══════════════════════════════════════════════════════</info>');

        $perPage = (int) $input->getOption('per-page');
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 100;
        }

        $delayMs = (int) $input->getOption('delay-ms');
        if ($delayMs < 0) {
            $delayMs = 0;
        }

        $page = 1;
        $totalPages = 1;
        $totalItems = 0;
        $totalSaved = 0;

        try {
            do {
                $output->write(sprintf('Mengambil halaman <comment>%d</comment>... ', $page));

                $res = $this->sidasClient->listActive($page, $perPage);

                if (!($res['success'] ?? false) || !isset($res['data'])) {
                    $output->writeln('<error>Gagal mengambil data dari SIDAS.</error>');
                    break;
                }

                $items = (array) $res['data'];
                $itemCount = count($items);

                if ($page === 1) {
                    $pagination = (array) ($res['pagination'] ?? []);
                    $totalPages = (int) ($pagination['total_pages'] ?? 1);
                    $totalItems = (int) ($pagination['total'] ?? $itemCount);
                    $output->writeln(sprintf('<info>Total %d santri (%d halaman)</info>', $totalItems, $totalPages));
                    $output->write(sprintf('Menyimpan halaman <comment>%d</comment> (%d santri)... ', $page, $itemCount));
                }

                $savedCount = $this->siswaRepository->bulkSaveToCache($items);
                $totalSaved += $savedCount;

                $output->writeln(sprintf('<info>Tersimpan: %d</info>', $savedCount));

                $page++;

                if ($delayMs > 0 && $page <= $totalPages) {
                    usleep($delayMs * 1000);
                }
            } while ($page <= $totalPages);

            $output->writeln('<info>══════════════════════════════════════════════════════</info>');
            $output->writeln(sprintf(
                '<info>✓ Sinkronisasi selesai! Total tersimpan/diperbarui di cache lokal: %d santri.</info>',
                $totalSaved
            ));
            $output->writeln(sprintf(
                'Total keseluruhan data di santri_cache: <comment>%d</comment>',
                $this->siswaRepository->countCache()
            ));
            $output->writeln('<info>══════════════════════════════════════════════════════</info>');

            return ExitCode::OK;
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>Terjadi pengecualian: %s</error>', $e->getMessage()));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
