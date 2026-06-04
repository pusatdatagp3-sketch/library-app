# Blueprint: PMDG Internal Population & Residency System (Dukcapil Pesantren Terpusat)

A centralized registry system built on **Yii3** and **Cycle ORM** to manage primary residency data for individuals across Pondok Modern Darussalam Gontor (PMDG) campuses.

---

## 1. Architectural Overview

The application follows the domain-driven directory structure utilized by existing PMDG Yii3 projects (e.g., `teqic-yii3`). It decouples domain concerns by keeping Controllers, Cycle ORM Entities, and Views grouped together under logical modules in `src/Web`.

### Directory Structure
```text
src/
├── Shared/
│   ├── TenantContext.php         # Multi-campus isolation layer (GP1, GP2, Gontor Putri, etc.)
│   └── ApplicationParams.php
├── Web/
│   ├── Resident/
│   │   ├── Controller/
│   │   │   └── ResidentController.php  # Handles Resident CRUD, validations, and verification dashboard
│   │   ├── Model/
│   │   │   ├── Resident.php            # Cycle ORM Entity for resident records
│   │   │   ├── ResidentRepository.php  # Select & campus filtering logic
│   │   │   └── ResidentCategory.php    # Value objects/enum for categories (Santri, Guru, etc.)
│   │   └── View/
│   │       ├── index.php               # Grid table view (search, filter, verified badges)
│   │       ├── create.php              # Multi-tab data entry form
│   │       ├── update.php              # Edit resident form
│   │       └── view.php                # Detailed dossier profile + uploaded documents gallery
│   ├── Berkas/
│   │   ├── Model/
│   │   │   ├── Berkas.php              # Cycle ORM Entity for files (KTP, Passport, KK)
│   │   │   └── BerkasRepository.php
│   └── Wilayah/                        # Supporting geographical module (Kemendagri standard)
│       └── Model/
│           ├── Village.php             # Cycle ORM Entity for villages/kelurahan
│           └── VillageRepository.php
```

---

## 2. Database Schema & Migration Plan

The database layout is optimized for relational integrity, audit tracking, multi-tenant campus isolation, and document attachments. We define standard SQL schemas compatible with MariaDB/MySQL and Cycle Migrations.

### Table: `villages` (Kelurahan/Desa)
Stores standard administrative region codes (matching Kemendagri codes for integration).
```sql
CREATE TABLE `villages` (
    `id` VARCHAR(10) NOT NULL,          -- Code format e.g., "32.73.01.1001"
    `name` VARCHAR(100) NOT NULL,
    `district_name` VARCHAR(100),       -- Kecamatan
    `city_name` VARCHAR(100),           -- Kabupaten/Kota
    `province_name` VARCHAR(100),       -- Provinsi
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `residents`
Contains primary resident records. Note the `is_maried` column matching your legacy naming.
```sql
CREATE TABLE `residents` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `nama` VARCHAR(255) NOT NULL,
    `nik_paspor` VARCHAR(64) NULL,                 -- Composite identity input
    `family_card_no` VARCHAR(64) NULL,             -- Kartu Keluarga number
    `gender` CHAR(1) NOT NULL,                     -- 'L' = Laki-laki, 'P' = Perempuan
    `tempat_lahir` VARCHAR(100) NULL,
    `tanggal_lahir` DATE NULL,
    `village_id` VARCHAR(10) NULL,                 -- FK to villages
    `is_life` TINYINT(1) DEFAULT 1 NOT NULL,       -- 1 = Hidup, 0 = Meninggal
    `is_maried` TINYINT(1) DEFAULT 0 NOT NULL,     -- 1 = Menikah, 0 = Belum Menikah
    `is_valid` TINYINT(1) DEFAULT 0 NOT NULL,      -- 1 = Validated/Verified, 0 = Draft
    `pendidikan_terakhir` VARCHAR(50) NULL,        -- SD, SMP, SMA, S1, S2, etc.
    `category` VARCHAR(50) NOT NULL,               -- SANTRI, GURU, ALUMNI, etc.
    `kode_kampus` VARCHAR(10) NOT NULL,            -- Tenant context isolation
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `created_by` INT NULL,
    `updated_by` INT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_residents_village` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL,
    INDEX `idx_residents_nik_paspor` (`nik_paspor`),
    INDEX `idx_residents_campus` (`kode_kampus`),
    INDEX `idx_residents_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `berkas` (Attached Documents)
Stores references to uploaded documents (e.g. scans of KTP, Passport, KK).
```sql
CREATE TABLE `berkas` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `resident_id` INT NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,              -- 'ktp', 'paspor', 'kk', 'ijazah', 'foto'
    `file_path` VARCHAR(255) NOT NULL,             -- Path to storage directory
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_berkas_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
    INDEX `idx_berkas_resident` (`resident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Cycle ORM Entities & Repositories

Entity mapping utilizes PHP 8 attributes conforming to Cycle ORM schema structure.

### `Resident.php`
```php
<?php

declare(strict_types=1);

namespace App\Web\Resident\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\HasMany;
use App\Web\Wilayah\Model\Village;
use App\Web\Berkas\Model\Berkas;

#[Entity(role: 'resident', table: 'residents', repository: ResidentRepository::class)]
class Resident
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(255)')]
    public string $nama = '';

    #[Column(type: 'string(64)', name: 'nik_paspor', nullable: true)]
    public ?string $nikPaspor = null;

    #[Column(type: 'string(64)', name: 'family_card_no', nullable: true)]
    public ?string $familyCardNo = null;

    #[Column(type: 'string(1)')]
    public string $gender = 'L'; // L or P

    #[Column(type: 'string(100)', name: 'tempat_lahir', nullable: true)]
    public ?string $tempatLahir = null;

    #[Column(type: 'date', name: 'tanggal_lahir', nullable: true)]
    public ?\DateTimeImmutable $tanggalLahir = null;

    #[Column(type: 'string(10)', name: 'village_id', nullable: true)]
    public ?string $villageId = null;

    #[BelongsTo(target: Village::class, innerKey: 'villageId', nullable: true, load: 'lazy')]
    public ?Village $village = null;

    #[Column(type: 'boolean', name: 'is_life')]
    public bool $isLife = true;

    #[Column(type: 'boolean', name: 'is_maried')]
    public bool $isMaried = false;

    #[Column(type: 'boolean', name: 'is_valid')]
    public bool $isValid = false;

    #[Column(type: 'string(50)', name: 'pendidikan_terakhir', nullable: true)]
    public ?string $pendidikanTerakhir = null;

    #[Column(type: 'string(50)')]
    public string $category = ''; // e.g. 'SANTRI', 'GURU'

    #[Column(type: 'string(10)', name: 'kode_kampus')]
    public string $kodeKampus = '';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[Column(type: 'integer', name: 'created_by', nullable: true)]
    public ?int $createdBy = null;

    #[Column(type: 'integer', name: 'updated_by', nullable: true)]
    public ?int $updatedBy = null;

    /**
     * @var Berkas[]
     */
    #[HasMany(target: Berkas::class, outerKey: 'residentId', load: 'eager')]
    public array $berkas = [];

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->nama = trim((string)($data['nama'] ?? $this->nama));
        $this->nikPaspor = isset($data['nik_paspor']) ? trim((string)$data['nik_paspor']) : $this->nikPaspor;
        $this->familyCardNo = isset($data['family_card_no']) ? trim((string)$data['family_card_no']) : $this->familyCardNo;
        $this->gender = trim((string)($data['gender'] ?? $this->gender));
        $this->tempatLahir = isset($data['tempat_lahir']) ? trim((string)$data['tempat_lahir']) : $this->tempatLahir;
        
        if (!empty($data['tanggal_lahir'])) {
            $this->tanggalLahir = new \DateTimeImmutable($data['tanggal_lahir']);
        }
        
        $this->villageId = isset($data['village_id']) && $data['village_id'] !== '' ? (string)$data['village_id'] : null;
        $this->isLife = isset($data['is_life']) ? (bool)$data['is_life'] : $this->isLife;
        $this->isMaried = isset($data['is_maried']) ? (bool)$data['is_maried'] : $this->isMaried;
        $this->isValid = isset($data['is_valid']) ? (bool)$data['is_valid'] : $this->isValid;
        $this->pendidikanTerakhir = isset($data['pendidikan_terakhir']) ? trim((string)$data['pendidikan_terakhir']) : $this->pendidikanTerakhir;
        $this->category = trim((string)($data['category'] ?? $this->category));
        
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];

        if (empty($this->nama)) {
            $this->errors['nama'] = 'Nama lengkap wajib diisi.';
        }

        if (!in_array($this->gender, ['L', 'P'], true)) {
            $this->errors['gender'] = 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).';
        }

        if (empty($this->category)) {
            $this->errors['category'] = 'Kategori penduduk wajib dipilih.';
        }

        // Composite ID Validation (NIK / Passport status checking)
        if (empty($this->nikPaspor)) {
            $this->errors['nik_paspor'] = 'NIK atau Nomor Paspor wajib diisi.';
        } else {
            // If it is numeric, check if it fits the Indonesian NIK requirements (16 digits)
            if (is_numeric($this->nikPaspor)) {
                if (strlen($this->nikPaspor) !== 16) {
                    $this->errors['nik_paspor'] = 'NIK Indonesia harus terdiri dari tepat 16 digit angka.';
                }
            } else {
                // If it is non-numeric, treat as Passport and validate general passport length rules
                if (strlen($this->nikPaspor) < 6 || strlen($this->nikPaspor) > 15) {
                    $this->errors['nik_paspor'] = 'Format nomor Paspor tidak valid (harus 6-15 karakter alfanumerik).';
                }
            }
        }

        if (!empty($this->familyCardNo) && strlen($this->familyCardNo) !== 16) {
            $this->errors['family_card_no'] = 'Nomor Kartu Keluarga harus terdiri dari tepat 16 digit.';
        }

        return empty($this->errors);
    }
}
```

### `ResidentRepository.php`
Implements campus isolation scopes dynamically.
```php
<?php

declare(strict_types=1);

namespace App\Web\Resident\Model;

use Cycle\ORM\Select\Repository;
use Cycle\ORM\Select;
use App\Shared\TenantContext;

class ResidentRepository extends Repository
{
    private TenantContext $tenantContext;

    public function __construct(Select $select, TenantContext $tenantContext)
    {
        parent::__construct($select);
        $this->tenantContext = $tenantContext;
    }

    public function select(): Select
    {
        $select = parent::select();
        $activeCampus = $this->tenantContext->getActiveCampusCode();
        
        // Multi-tenant check: filter results based on campus privileges
        if ($activeCampus === 'ALL') {
            $select = $select->where('kode_kampus', 'in', $this->tenantContext->getAllowedCampusCodes());
        } elseif ($activeCampus !== null) {
            $select = $select->where('kode_kampus', $activeCampus);
        }
        return $select;
    }

    public function findByNikPaspor(string $id): ?Resident
    {
        return $this->select()->where(['nik_paspor' => $id])->fetchOne();
    }

    /**
     * @return Resident[]
     */
    public function findByCategory(string $category): array
    {
        return $this->select()
            ->where(['category' => $category])
            ->orderBy('nama', 'ASC')
            ->fetchAll();
    }
}
```

### `Berkas.php`
```php
<?php

declare(strict_types=1);

namespace App\Web\Berkas\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'berkas', table: 'berkas')]
class Berkas
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'resident_id')]
    public int $residentId;

    #[Column(type: 'string(255)')]
    public string $filename = '';

    #[Column(type: 'string(50)', name: 'file_type')]
    public string $fileType = 'ktp'; // 'ktp', 'paspor', 'kk', 'ijazah', etc.

    #[Column(type: 'string(255)', name: 'file_path')]
    public string $filePath = '';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}
```

---

## 4. Resident Categories Map

To organize residents according to PMDG structures, the following enum maps keys to Indonesian descriptions.

```php
<?php

declare(strict_types=1);

namespace App\Web\Resident\Model;

class ResidentCategory
{
    public const SANTRI = 'SANTRI';
    public const GURU = 'GURU';
    public const KARYAWAN = 'KARYAWAN';
    public const WALI_SANTRI = 'WALI_SANTRI';
    public const ALUMNI = 'ALUMNI';
    public const KELUARGA_SANTRI_GURU = 'KELUARGA_SANTRI_GURU';
    public const KELUARGA_GURU_SENIOR = 'KELUARGA_GURU_SENIOR';
    public const KADER = 'KADER';
    public const ORANG_TUA_MERTUA_KADER = 'ORANG_TUA_MERTUA_KADER';

    public static function list(): array
    {
        return [
            self::SANTRI => 'Santri',
            self::GURU => 'Guru',
            self::KARYAWAN => 'Karyawan / Staff',
            self::WALI_SANTRI => 'Wali Santri',
            self::ALUMNI => 'Alumni PMDG',
            self::KELUARGA_SANTRI_GURU => 'Keluarga Santri dan Guru',
            self::KELUARGA_GURU_SENIOR => 'Keluarga Guru (Senior)',
            self::KADER => 'Kader Pondok',
            self::ORANG_TUA_MERTUA_KADER => 'Orang Tua & Mertua Kader',
        ];
    }
}
```

---

## 5. Controller Implementation (`ResidentController.php`)

Injects view renderers, ORM managers, flash messages, and handles the logic for resident creation, file uploads, validation checks, and data verification status updates.

```php
<?php

declare(strict_types=1);

namespace App\Web\Resident\Controller;

use App\Web\Resident\Model\Resident;
use App\Web\Resident\Model\ResidentCategory;
use App\Web\Berkas\Model\Berkas;
use App\Shared\TenantContext;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use App\Web\Auth\Model\UserSession;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class ResidentController
{
    private $residentRepository;
    private $berkasRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private TenantContext $tenantContext,
        private UserSession $userSession
    ) {
        $this->residentRepository = $orm->getRepository(Resident::class);
        $this->berkasRepository = $orm->getRepository(Berkas::class);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $search = $queryParams['search'] ?? '';
        $category = $queryParams['category'] ?? '';
        $isValid = $queryParams['is_valid'] ?? '';

        $query = $this->residentRepository->select();

        if ($search !== '') {
            $query = $query->where('nama', 'like', "%{$search}%")
                           ->orWhere('nik_paspor', 'like', "%{$search}%");
        }

        if ($category !== '') {
            $query = $query->where('category', $category);
        }

        if ($isValid !== '') {
            $query = $query->where('is_valid', (bool)$isValid);
        }

        $models = $query->orderBy('id', 'DESC')->fetchAll();

        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'models' => $models,
            'categories' => ResidentCategory::list(),
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentIsValid' => $isValid,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $model = new Resident();
        $activeCampus = $this->tenantContext->getActiveCampusCode();
        if ($activeCampus !== 'ALL') {
            $model->kodeKampus = $activeCampus;
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            
            if ($activeCampus === 'ALL') {
                $model->kodeKampus = $data['kode_kampus'] ?? '';
            } else {
                $model->kodeKampus = $activeCampus;
            }

            $model->createdAt = new \DateTimeImmutable();
            $model->createdBy = $this->userSession->getUserId();

            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                
                // Process File Uploads (KTP/Passport/KK)
                $uploadedFiles = $request->getUploadedFiles();
                $this->handleFileUploads($model->id, $uploadedFiles);

                $this->flash->set('success', "Penduduk \"{$model->nama}\" berhasil ditambahkan.");
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('residents/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/create', [
            'model' => $model,
            'categories' => ResidentCategory::list(),
            'activeCampus' => $activeCampus,
            'campuses' => $this->userSession->getCampusList(),
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->residentRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            $model->updatedAt = new \DateTimeImmutable();
            $model->updatedBy = $this->userSession->getUserId();

            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                
                // Handle new uploads in updates
                $uploadedFiles = $request->getUploadedFiles();
                $this->handleFileUploads($model->id, $uploadedFiles);

                $this->flash->set('success', "Data penduduk \"{$model->nama}\" berhasil diperbarui.");
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('residents/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'model' => $model,
            'categories' => ResidentCategory::list(),
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->residentRepository->findByPK($id);

        if ($model !== null) {
            // Delete associated file attachments physically first
            foreach ($model->berkas as $file) {
                if (file_exists($file->filePath)) {
                    unlink($file->filePath);
                }
            }
            $this->entityManager->delete($model)->run();
            $this->flash->set('success', "Data penduduk berhasil dihapus dari sistem.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('residents/index'));
    }

    public function view(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->residentRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/view', [
            'model' => $model,
            'categories' => ResidentCategory::list(),
        ]);
    }

    public function verify(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->residentRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        // Toggle validity status
        $model->isValid = !$model->isValid;
        $model->updatedAt = new \DateTimeImmutable();
        $model->updatedBy = $this->userSession->getUserId();

        $this->entityManager->persist($model)->run();
        
        $statusStr = $model->isValid ? 'Diverifikasi' : 'Batal Verifikasi';
        $this->flash->set('success', "Status data penduduk \"{$model->nama}\" berhasil diubah menjadi: {$statusStr}.");

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('residents/view', ['id' => $id]));
    }

    private function handleFileUploads(int $residentId, array $uploadedFiles): void
    {
        $uploadDir = __DIR__ . '/../../../../public/uploads/residents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($uploadedFiles as $key => $file) {
            // E.g. $key matches input names like 'berkas_ktp', 'berkas_paspor', 'berkas_kk'
            if ($file->getError() === UPLOAD_ERR_OK) {
                $fileType = str_replace('berkas_', '', $key);
                $originalName = $file->getClientFilename();
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                
                $safeName = sprintf('%d_%s_%s.%s', $residentId, $fileType, uniqid(), $ext);
                $targetPath = $uploadDir . $safeName;

                $file->moveTo($targetPath);

                $berkas = new Berkas();
                $berkas->residentId = $residentId;
                $berkas->filename = $originalName;
                $berkas->fileType = $fileType;
                $berkas->filePath = $targetPath;
                $berkas->createdAt = new \DateTimeImmutable();

                $this->entityManager->persist($berkas);
            }
        }
        $this->entityManager->run();
    }
}
```

---

## 6. Routing Configuration

Add route paths within `config/common/routes.php` under the RBAC access control middleware group.

```php
// config/common/routes.php

use App\Web\Resident\Controller\ResidentController;
use App\Web\Middleware\RbacAccessControlMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    // ... Other existing groups (Auth, Users, Roles, etc.) ...

    // Population & Residency Group
    Group::create('/residents')
        ->middleware(RbacAccessControlMiddleware::class)
        ->routes(
            Route::get('')
                ->action([ResidentController::class, 'index'])
                ->name('residents/index'),
            Route::get('/create')
                ->action([ResidentController::class, 'create'])
                ->name('residents/create'),
            Route::post('/create')
                ->action([ResidentController::class, 'create'])
                ->name('residents/create/post'),
            Route::get('/update/{id:\d+}')
                ->action([ResidentController::class, 'update'])
                ->name('residents/update'),
            Route::post('/update/{id:\d+}')
                ->action([ResidentController::class, 'update'])
                ->name('residents/update/post'),
            Route::post('/delete/{id:\d+}')
                ->action([ResidentController::class, 'delete'])
                ->name('residents/delete'),
            Route::get('/view/{id:\d+}')
                ->action([ResidentController::class, 'view'])
                ->name('residents/view'),
            Route::post('/verify/{id:\d+}')
                ->action([ResidentController::class, 'verify'])
                ->name('residents/verify'),
        ),
];
```

---

## 7. REST API & Integration Layer

As a centralized Internal Dukcapil, other sub-applications (like TEQIC or DOREH) can verify and fetch resident parameters through token-authenticated REST services.

### API Route Configuration
```php
// API Group
Group::create('/api/v1/residents')
    ->middleware(\App\Web\Middleware\ApiAuthMiddleware::class) // Token authorization
    ->routes(
        Route::get('/search/{nik_paspor}')
            ->action([\App\Web\Resident\Controller\ResidentApiController::class, 'getByNikPaspor'])
            ->name('api/residents/find'),
        Route::get('/validate/{nik_paspor}')
            ->action([\App\Web\Resident\Controller\ResidentApiController::class, 'validateStatus'])
            ->name('api/residents/validate'),
    ),
```

### API Controller Implementation (`ResidentApiController.php`)
```php
<?php

declare(strict_types=1);

namespace App\Web\Resident\Controller;

use App\Web\Resident\Model\Resident;
use Cycle\ORM\ORMInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class ResidentApiController
{
    private $residentRepository;

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        ORMInterface $orm
    ) {
        $this->residentRepository = $orm->getRepository(Resident::class);
    }

    public function getByNikPaspor(string $nik_paspor): ResponseInterface
    {
        $resident = $this->residentRepository->findByNikPaspor($nik_paspor);
        
        if ($resident === null) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        return $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'id' => $resident->id,
                'nama' => $resident->nama,
                'nik_paspor' => $resident->nikPaspor,
                'family_card_no' => $resident->familyCardNo,
                'gender' => $resident->gender,
                'tempat_lahir' => $resident->tempatLahir,
                'tanggal_lahir' => $resident->tanggalLahir?->format('Y-m-d'),
                'is_life' => $resident->isLife,
                'is_married' => $resident->isMaried,
                'is_valid' => $resident->isValid,
                'category' => $resident->category,
                'kode_kampus' => $resident->kodeKampus,
                'pendidikan_terakhir' => $resident->pendidikanTerakhir,
            ]
        ]);
    }

    public function validateStatus(string $nik_paspor): ResponseInterface
    {
        $resident = $this->residentRepository->findByNikPaspor($nik_paspor);
        
        if ($resident === null) {
            return $this->jsonResponse(['status' => 'error', 'is_valid' => false, 'message' => 'Data tidak terdaftar.'], 404);
        }

        return $this->jsonResponse([
            'status' => 'success',
            'nik_paspor' => $resident->nikPaspor,
            'is_valid' => $resident->isValid, // Validation status flag (Verified by Admin)
            'is_life' => $resident->isLife,
            'category' => $resident->category,
        ]);
    }

    private function jsonResponse(array $payload, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($payload));
        return $response;
    }
}
```

---

## 8. Premium UI/UX & Aesthetics Design

Consistent with premium styling standards, the User Interface will use the custom responsive layouts implemented in the main projects (Outfit typography, dark-mode enhancements, and glassmorphic styling).

### A. Theme Configuration
- **Backgrounds**: Sleek dark backgrounds (`#0B0F19`) or high-end slate light modes.
- **Accent Primary**: Rich indigo gradient (`from-indigo-600 to-violet-600`).
- **Valid Status / Verified**: Emerald (`#10B981`) with glow effect.
- **Pending/Invalid Status**: Soft amber glow (`#F59E0B`).

### B. View Layouts Design Specs

#### 1. Main Grid List (`index.php`)
- **Top Bar**: Search bar with auto-suggest, category badges (interactive pills for *Santri*, *Guru*, *Alumni*, etc.), and a "Tambah Penduduk" button using a subtle hover transition (`scale-102`, CSS duration transition).
- **Cards/Table Grid**: High-end glassmorphic table borders with transparent background (`backdrop-blur-md`). Column layouts:
  - Avatar representation based on Gender (L/P icons).
  - NIK/Passport with copy-to-clipboard actions.
  - Category tag with distinctive thematic colors.
  - Validation badge: Green pill for verified (`is_valid = 1`), yellow pill for draft (`is_valid = 0`).

#### 2. Detailed Profile Dossier (`view.php`)
- **Split Panel Design**:
  - **Left Panel (Personal Dossier)**: Personal particulars styled like a premium identity card. Includes metadata counters (verification progress, completeness score).
  - **Right Panel (Document Gallery)**: A grid showing thumbnail previews of attached PDF/images. Clicking files launches a sleek lightbox.
- **Action Dashboard**: Single-click "Verifikasi Data" button visible only to admins, emitting sound cues or micro-animations on toggle.
