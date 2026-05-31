# 📘 Spesifikasi Implementasi TQC KMI Berbasis Database & Kanban (Yii3)

Dokumen ini digunakan sebagai panduan implementasi fitur **Sistem Total Quality Control (TQC) KMI** berbasis database relasional dan **Kanban Board** di lingkungan **Yii3**.

---

# 🟦 1. DASHBOARD

## 🎯 Fungsi

Menampilkan data real-time sebagai bahan monitoring dan pengambilan keputusan.

## 📊 Sumber Data

Ambil dari tabel:

* `entitas`
* `program`
* `tasks`
* `kendala_program`
* `nilai_akademik`
* `santri`, `guru` (existing system di `teqic_yii3`)

## 🔧 Fitur

### 1. Statistik Santri & Guru

* Query count dari tabel `santri`, `guru`
* Visualisasi: Chart (Line / Bar)

---

### 2. Ringkasan Akademik

* Rata-rata nilai:

```sql
SELECT AVG(nilai) FROM nilai_akademik;
```

* Ranking kelas:
  Gunakan agregasi per kelas berdasarkan data `nilai_akademik` dan `santri` (join `nilai_akademik.santri_id` ke `santri.kds`).

---

### 3. Status Program

Ambil dari:

* `program.status`
* Progress agregasi dari `tasks.progress`

```sql
SELECT program_id, AVG(progress) FROM tasks GROUP BY program_id;
```

---

### 4. Tren Pelanggaran

(Sesuaikan dengan tabel `pelanggaran` yang sudah ada di database target)

---

### 5. Notifikasi

Ambil dari:

* `task_progress_logs`
* `kendala_program`
* `notulensi`

---

# 🟦 2. FUNGSIONARIS

## 🎯 Fungsi

Mengelola struktur organisasi berbasis **entitas**

## 🧩 Mapping Database

| Fitur               | Tabel                          |
| ------------------- | ------------------------------ |
| Struktur organisasi | `entitas`                      |
| Anggota             | `anggota_entitas`              |
| Program             | `program`                      |
| Progres             | `tasks` + `task_progress_logs` |
| Kendala             | `kendala_program`              |
| Notulensi           | `notulensi`                    |
| Dokumentasi         | `dokumentasi`                  |

---

## 🔧 Fitur Implementasi

### 1. Struktur Fungsionaris

* Tampilkan `entitas` yang terhubung ke `modul` dengan `nama = 'fungsionaris'` (menggunakan `modul_id`).
* Relasi:

```sql
entitas → anggota_entitas
```

---

### 2. Input Progres (KANBAN)

Gunakan tabel:

* `kanban_columns`
* `tasks`

#### Struktur Kanban:

* To Do
* On Progress
* Done

#### Query:

```sql
SELECT * FROM tasks WHERE program_id = ?
ORDER BY kanban_column_id, urutan;
```

#### Aksi:

* Drag card:
  * update `kanban_column_id`
* Sort:
  * update `urutan`

---

### 3. Jenis Progres (Waktu)

Tambahkan field di `program`:

```sql
periode ENUM('mingguan','bulanan','semesteran','tahunan')
```

---

### 4. Kendala

Input ke:

* `kendala_program`

Tambahan:

```sql
jenis ENUM('terbuka','tertutup')
```

---

### 5. Notulensi & Dokumentasi

* Upload ke:
  * `notulensi`
  * `dokumentasi`

---

# 🟦 3. KEPANITIAAN & PROGRAM KMI

## 🎯 Fungsi

Manajemen program formal berbasis struktur yang sama

## 🔁 Menggunakan Struktur yang Sama

Tidak perlu tabel baru ❗
Gunakan entitas yang terhubung ke `modul` dengan `nama = 'kepanitiaan'` melalui `modul_id`.

---

## 🔧 Fitur

### 1. Daftar Program

```sql
SELECT * FROM program WHERE entitas_id = ?
```

---

### 2. Penanggung Jawab

Relasi:

```sql
program.penanggung_jawab_id → anggota_entitas.id
```

---

### 3. Tupoksi

Tambahkan di `program`:

```sql
tupoksi TEXT
```

---

### 4. Monitoring (KANBAN)

Sama seperti Fungsionaris:

* Kolom → `kanban_columns`
* Card → `tasks`

---

### 5. Kendala, Notulensi, Dokumentasi

Reuse tabel:

* `kendala_program`
* `notulensi`
* `dokumentasi`

---

# 🟦 4. EMPOWERING KMI

## 🎯 Fungsi

Mengelola aktivitas non-formal

---

## 🔁 Struktur Sama (REUSE)

Gunakan entitas yang terhubung ke `modul` dengan `nama = 'empowering'` melalui `modul_id`.

---

## 🔧 Fitur

### 1. Data Marakiz / Aktivitas

Masuk ke:

```sql
entitas
```

---

### 2. Program & Progres

Gunakan:

* `program`
* `tasks` (KANBAN)

---

### 3. Penanggung Jawab

Tetap:

```sql
anggota_entitas
```

---

### 4. Monitoring

Gunakan Kanban Board:

* fleksibel
* bisa untuk kolaborasi
* bisa tracking detail

---

# 🟦 5. KANBAN BOARD (CORE FEATURE)

## 🎯 Struktur

```
Program
  ↓
Kanban Columns
  ↓
Tasks (Card)
```

---

## 📌 Kolom Default

Insert awal:

```sql
INSERT INTO kanban_columns (entitas_id, nama, urutan) VALUES
(1, 'To Do', 1),
(1, 'On Progress', 2),
(1, 'Done', 3);
```

---

## 📌 Task (Card)

Field penting:

* `judul`
* `deskripsi`
* `assigned_to` (mengacu pada `anggota_entitas.id`)
* `progress`
* `deadline`

---

## 📌 Drag & Drop Logic

### Pindah kolom:

```sql
UPDATE tasks SET kanban_column_id = ? WHERE id = ?
```

### Ubah posisi:

```sql
UPDATE tasks SET urutan = ? WHERE id = ?
```

---

## 📌 Progress Tracking

Setiap update:

```sql
INSERT INTO task_progress_logs (...)
```

---

# 🟦 6. FLOW SISTEM

## 🧭 Alur Utama

```
Modul
  ↓
Entitas
  ↓
Anggota
  ↓
Program
  ↓
Kanban Board (Tasks)
  ↓
Progress / Kendala / Dokumentasi
```

---

# 🟦 7. CATATAN IMPLEMENTASI (Yii3 + Cycle ORM)

## 📁 Struktur Direktori Domain (`src/Web/`)
Setiap fitur diimplementasikan di bawah direktori domain masing-masing dalam folder `src/Web/{NamaFitur}`:
* `src/Web/Entitas/` (Model/Entitas.php, Controller/, View/)
* `src/Web/Program/` (Model/Program.php, Controller/, View/)
* `src/Web/Task/` (Model/Task.php, Controller/, View/)

## 🧬 Contoh Deklarasi Entity (Cycle ORM)
Implementasi Model menggunakan PHP 8 attributes untuk mencocokkan skema database relasional. Contoh entity untuk `Task`:

```php
namespace App\Web\Task\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'task', table: 'tasks')]
class Task
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'int')]
    public int $program_id;

    #[Column(type: 'int', nullable: true)]
    public ?int $kanban_column_id = null;

    #[Column(type: 'string(255)')]
    public string $judul = '';

    #[Column(type: 'text', nullable: true)]
    public ?string $deskripsi = null;

    #[Column(type: 'int', nullable: true)]
    public ?int $assigned_to = null;

    #[Column(type: 'int', default: 0)]
    public int $progress = 0;

    #[Column(type: 'date', nullable: true)]
    public ?\DateTimeInterface $deadline = null;

    #[Column(type: 'int', default: 0)]
    public int $urutan = 0;
}
```

## 🌐 UI Kanban (Drag & Drop)
* **Frontend:** Implementasikan menggunakan view PHP standar Yii3 yang dikombinasikan dengan library JS ringan client-side (seperti **SortableJS** via Asset Bundle atau CDN).
* **AJAX Endpoint:** Buat action controller khusus (misal `TaskController::updatePosition()`) yang menerima request AJAX untuk menyimpan pembaruan `kanban_column_id` dan `urutan` menggunakan `EntityManager` Cycle ORM.

---

# 🟦 8. KESIMPULAN

Desain ini menghasilkan:

✅ Sistem terstruktur dan modular berbasis framework Yii3.
✅ Semua menu TQC KMI menggunakan pola database relasional yang konsisten.
✅ Integrasi data santri dan guru yang bersih dengan schema `teqic_yii3` yang sudah ada.
✅ Kanban board interaktif sebagai pusat kontrol operasional.

---

# 🚀 NEXT STEP (REKOMENDASI)

1. **Migrasi Database:** Impor tabel-tabel TQC (seperti `entitas`, `program`, `tasks`, dsb) dari database Yii2 `teqic.sql` ke database Yii3 `teqic_yii3`.
2. **Membuat Cycle Entities:** Buat model Entity beranotasi PHP 8 di bawah direktori `src/Web/{Domain}/Model/` untuk masing-masing tabel tersebut.
3. **Membuat Controller & View Yii3:** Bangun CRUD untuk Entitas & Program, serta buat UI Kanban Board interaktif menggunakan SortableJS.
