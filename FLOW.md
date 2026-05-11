# **FLOW IMPLEMENTASI: AI-POWERED RESEARCH COMMAND CENTER**

---

## Fase 1: Fondasi UI & Autentikasi ✅

**Setup Theme Neubrutalism:**
- Konfigurasi Tailwind CSS v4 dengan `@theme` directive untuk custom colors (`neo-yellow`, `neo-purple`, `neo-green`) dan `shadow-neo`.
- Buat utility classes: `neo-border`, `neo-shadow`, `neo-input`, `neo-btn` beserta varian warnanya.
- Buat Layout utama (App Shell) dengan navbar sticky dan navigasi.

**Halaman Home (Landing Page):**
- Implementasikan desain Landing Page dengan Hero Section, Feature Grid (3 kartu), dan Footer.
- Gaya Neubrutalism: border tebal, hard shadow, warna neon, tipografi bold.

**Sistem Autentikasi:**
- Pasang Laravel Fortify untuk standard auth (login, register, password reset, 2FA).
- Pasang Laravel Socialite untuk Google Sign-In.
- Pastikan Auth Middleware (`auth`, `verified`) memproteksi halaman Library.

---

## Fase 2: Master Data & Template Dinamis ✅

**Template Manager (KTI Types):**
- Buat fitur CRUD untuk Jenis KTI menggunakan Livewire class-based component.
- Implementasikan Dynamic Column Builder (JSONB) — user menentukan kolom analisis.

**Default Template "Article":**
- Setiap user baru otomatis mendapat template "Article" saat registrasi (via Model event `User::created`).
- Migrasi data: Seed template "Article" untuk semua user yang sudah ada.
- 5 kolom wajib: `Judul`, `Penulis`, `Jurnal Publikasi`, `Seri Jurnal`, `Link DOI`.

**UI Restriction:**
- Kolom wajib ditampilkan `readonly` dengan ikon gembok (🔒) di form edit.
- Tombol hapus template "Article" dinonaktifkan (diganti ikon gembok).
- User hanya boleh menambah kolom baru, tidak boleh menghapus 5 kolom wajib.
- Backend validation: `removeColumn()` menolak penghapusan kolom protected, `save()` memastikan kolom wajib selalu ada.

---

## Fase 3: Core Library & File Ingest ✅

**Dashboard Library:**
- Buat tampilan daftar artikel (Grid/Card) menggunakan Livewire dengan warna cycling.
- Polling otomatis setiap 5 detik untuk update status analisis.

**Fitur Upload:**
- Implementasikan Drag & Drop upload dengan progress bar.
- Validasi: hanya PDF/DOCX, maksimal 10MB.
- Simpan file ke Private Storage (`storage/app/private/articles/{user_id}/`).
- Buat record di database dengan status `pending`.
- Dispatch `AnalyzeArticleJob` setelah upload berhasil.

**Fitur Hapus:**
- Hapus file fisik dari storage + record database secara sinkron.
- Konfirmasi modal sebelum hapus.

---

## Fase 4: Integrasi AI Gemini — Tahap 1 (Document Analysis) ✅

**GeminiService:**
- Method `uploadFile()`: Upload file ke Gemini File API.
- Method `analyzeDocument()`: Kirim prompt + file URI ke Gemini 3 Flash-preview.
- Method `deleteFile()`: Cleanup file dari server Google setelah analisis.
- Method `generateReference()`: Kirim JSON data ke Gemini untuk formatting sitasi (Tahap 2).

**Prompt Engineering:**
- Jika KTI type = "Article": Prompt spesifik untuk metadata + 5 kolom template + 3 universal fields.
- Jika KTI type lainnya: Prompt generik untuk metadata + n kolom template + 3 universal fields.
- Universal fields (wajib semua tipe): `abstract`, `so_what`, `conclusion`.
- Metadata (wajib semua tipe): `title`, `author`, `year`.

**AnalyzeArticleJob:**
- Timeout: 300 detik (`$timeout = 300`).
- Retry: 3 kali dengan exponential backoff (60s, 120s).
- Flow: Upload → Sleep 10s → Analyze → Extract metadata → Save → Cleanup.
- Data mapping: `title`, `author`, `year` disimpan ke kolom dedicated + `analysis_results` JSONB.
- Fallback keys: Cari `author`/`Author`/`penulis`/`Penulis` dll untuk fleksibilitas output AI.

---

## Fase 5: Halaman Detail & Penampil PDF ✅

**Split-Screen UI:**
- Layout dua kolom responsif (`grid-cols-1 lg:grid-cols-2`).
- Kiri: PDF Viewer via `<iframe>` yang mengarah ke signed route (`ArticleFileController`).
- Kanan: Analysis Hub — scrollable area dengan kartu-kartu hasil analisis.

**PDF Viewer:**
- Controller `ArticleFileController@show`: Serve file dari private storage dengan validasi kepemilikan.
- Route: `GET /library/article/{article}/file`.
- Fallback DOCX: Tampilkan tombol download.

**Tabel Analisis Dinamis:**
- Render tabel berdasarkan kolom template dari `kti_types.columns`.
- Desain Neubrutalism: Header `bg-neo-yellow`, border hitam tebal, hover effect.
- Tampilkan array values sebagai bullet list.

**Kartu Informasi (8 Poin untuk Article):**
1. Abstrak — Card putih dengan badge nomor ungu.
2-6. Kolom template — Dalam tabel dinamis.
7. So What? — Card ungu dengan teks putih.
8. Kesimpulan — Card hijau neon.

**Loading & Error States:**
- Skeleton loader dengan animasi pulse saat status `pending`/`processing`.
- Polling setiap 3 detik (`wire:poll.3s`).
- Error state merah dengan tombol "Coba Analisis Lagi".

---

## Fase 5.5: Citation & Bibliography Generator ✅

**Quick Format (Local — Tanpa API):**
- `CitationFormatter` service class dengan method `inText()` dan `bibliography()`.
- Support 4 style: APA, MLA, IEEE, Harvard.
- Multi-author handling: "Santika et al." untuk in-text.
- Placeholder untuk data kosong: `[Nama Penulis]`, `[Tahun]`, `[Judul Artikel]`.
- Tab UI: In-Text / Bibliography dengan tombol Copy masing-masing.

**AI Reference Generator (On-Demand — Tahap 2):**
- `GeminiService::generateReference($jsonData, $style)`: Kirim JSON ke Gemini, minta format sitasi.
- Livewire action `generateReference()`: Panggil service, simpan ke `citation_output` & `bibliography_output`.
- Conditional rendering:
    - Belum ada output → Tombol "⚡ Generate" (ungu, prominent).
    - Sudah ada output → Tampilkan hasil + tombol "🔄 Regenerate" (hitam, subtle).
- Loading state: Spinner + "AI sedang bekerja..." dengan pulse animation.
- Error handling: Tampilkan pesan error jika gagal.
- Hasil disimpan permanen di database — tidak hilang saat refresh.

---

## Fase 6: Chatbot & Fitur Lanjutan (BELUM DIMULAI)

**Persistent Chat (Database):**
- Buat tabel `chat_histories` dengan relasi ke article (per-artikel) atau null (global).
- Sistem chat yang tersimpan di database PostgreSQL.
- UI: Panel chat di halaman detail artikel.

**Semantic Search (pgvector):**
- Implementasikan pencarian berdasarkan makna menggunakan ekstensi pgvector.
- Generate embedding untuk setiap artikel yang dianalisis.
- UI: Search bar di dashboard dengan toggle "Teks" / "Makna".

**Global Chat ("Ask Across Library"):**
- Chatbot untuk bertanya ke seluruh koleksi dokumen user.
- Menggunakan embedding + context retrieval.

**Polishing:**
- Dark Mode support.
- Optimasi performa (lazy loading, caching).
- Responsive design fine-tuning.

---

## Catatan Teknis Penting

| Aspek | Spesifikasi |
|-------|-------------|
| HTTP Timeout | 300 detik (GeminiService) |
| Job Timeout | 300 detik (AnalyzeArticleJob) |
| Retry | 3x dengan backoff [60s, 120s] |
| Model AI | `gemini-3-flash-preview` |
| File Size Limit | 10MB (PDF/DOCX) |
| Queue Driver | Database |
| Storage | Local private disk |
| Route Format | `Route::livewire()` untuk full-page components |
| Code Style | Laravel Pint (auto-format on save) |
| Testing | Pest v4 (feature tests) |
