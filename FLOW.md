# **FLOW IMPLEMENTASI: AI-POWERED RESEARCH COMMAND CENTER**

---

## Fase 1: Fondasi UI & Autentikasi ✅

**Setup Theme Neubrutalism:**
- Konfigurasi Tailwind CSS v4 dengan `@theme` directive untuk custom colors (`neo-yellow`, `neo-purple`, `neo-green`) dan `shadow-neo`.
- Buat utility classes: `neo-border`, `neo-shadow`, `neo-input`, `neo-btn` beserta varian warnanya.
- Buat Layout utama (App Shell) dengan sidebar navigation (Flux UI).

**Halaman Home (Landing Page):**
- Hero Section, Feature Grid (3 kartu), dan Footer.
- Gaya Neubrutalism: border tebal, hard shadow, warna neon, tipografi bold.

**Sistem Autentikasi:**
- Laravel Fortify untuk standard auth (login, register, password reset, 2FA).
- Laravel Socialite untuk Google Sign-In.
- Auth Middleware (`auth`, `verified`) memproteksi halaman Library.

---

## Fase 2: Master Data & Template Dinamis ✅

**Template Manager (KTI Types):**
- CRUD untuk Jenis KTI menggunakan Livewire class-based component.
- Dynamic Column Builder (JSONB).

**Default Template "Article":**
- Otomatis dibuat saat user registrasi (via Model event `User::created`).
- Migrasi seed untuk user yang sudah ada.
- 5 kolom wajib: `Judul`, `Penulis`, `Jurnal Publikasi`, `Seri Jurnal`, `Link DOI`.

**UI Restriction:**
- Kolom wajib `readonly` dengan ikon gembok.
- Template "Article" tidak bisa dihapus.
- Backend validation memastikan kolom wajib selalu ada.

---

## Fase 3: Core Library & File Ingest ✅

**Dashboard Library:**
- Grid/Card artikel dengan warna cycling dan polling status.

**Fitur Upload:**
- Drag & Drop, validasi PDF/DOCX max 10MB.
- Simpan ke Private Storage, dispatch `AnalyzeArticleJob`.

**Fitur Hapus:**
- Hapus file fisik + record database dengan konfirmasi modal.

---

## Fase 4: Integrasi AI Gemini — Document Analysis ✅

**GeminiService:**
- `uploadFile()`: Upload ke Gemini File API.
- `analyzeDocument()`: Prompt flat tanpa kategori → JSON response.
- `deleteFile()`: Cleanup setelah analisis.
- `generateReference()`: Format citation/bibliography dari JSON.
- `chatWithContext()`: Chat per-artikel dengan analysis context.
- `globalChat()`: Chat global dengan keyword-retrieved context.

**Prompt Engineering (Flat — Tanpa Kategori):**
- Article: `'Judul', 'Penulis', 'Jurnal Publikasi', 'Seri Jurnal', 'Link DOI', 'abstract', 'so_what', 'conclusion', 'keywords'`
- Custom: kolom template + `'abstract', 'so_what', 'conclusion', 'keywords'`
- Tidak ada "METADATA:", "ANALISIS UMUM:", atau kategori lainnya dalam prompt.

**AnalyzeArticleJob:**
- Timeout: 300 detik. Retry: 3x dengan backoff [60s, 120s].
- Flow: Upload → Sleep 10s → Analyze → Extract metadata → Extract keywords → Save → Cleanup.
- Data mapping: `title`, `author`, `year` ke kolom dedicated. `keywords` ke kolom JSONB.
- Fallback keys: case-insensitive lookup untuk fleksibilitas output AI.

---

## Fase 5: Halaman Detail & Research Lab ✅

**Split-Screen UI:**
- Kiri: PDF Viewer via iframe (`ArticleFileController`). Fallback download untuk DOCX.
- Kanan: Analysis Hub dengan urutan:
    1. Abstrak (kartu putih)
    2. Tabel Analisis (header neo-yellow, kolom template, case-insensitive mapping)
    3. Kata Kunci (5 badge warna-warni)
    4. So What? (kartu neo-purple, teks putih)
    5. Kesimpulan (kartu neo-green, teks hitam)

**Case-Insensitive & Legacy Support:**
- `flattenResults()`: Meratakan JSON nested (format lama) menjadi flat.
- `findValueCaseInsensitive()`: Cocokkan key tanpa peduli huruf besar/kecil atau underscore.
- Reserved keys (`abstract`, `so_what`, `conclusion`, `keywords`, `title`, `author`, `year`) tidak muncul di tabel.

**Citation & Bibliography:**
- AI Reference Generator (on-demand): Dropdown style + Generate/Regenerate button.
- Hasil disimpan permanen ke `citation_output` dan `bibliography_output`.

**Loading & Error States:**
- Skeleton loader + polling 3 detik saat analisis.
- Error state merah dengan tombol retry.

---

## Fase 6: Chat, Search & Global Assistant ✅

### Fase 6.1: Fondasi Database ✅
- Tabel `chat_histories`: user_id, article_id (nullable), message, response, metadata (JSONB).
- Model `ChatHistory` dengan relasi dan scopes.
- Relasi `chatHistories()` di User dan Article.

### Fase 6.2: Per-Article Chat ✅
- Panel chat di halaman detail (di bawah citation section).
- `chatWithContext()`: Kirim message + analysis_results + 10 chat terakhir ke Gemini 2.5 Flash.
- Persistensi: Semua chat tersimpan di database.
- UI: Balon kuning (user) / ungu (AI), typing indicator, auto-scroll.

### Fase 6.2.1: Keyword System ✅
- Kolom `keywords` (JSONB) di tabel articles.
- Prompt AI menghasilkan 5 kata kunci campuran ID/EN.
- `extractKeywords()` di AnalyzeArticleJob dengan fallback keys.
- Keywords ditampilkan di halaman detail dan kartu dashboard.
- pgvector/embedding dihapus — diganti keyword-based approach.

### Fase 6.3: Smart Keyword Search ✅
- Search di dashboard mencari di: title, author, file_name, keywords (JSONB), analysis_results (JSONB).
- Ranking: keyword match (0) → title (1) → author (2) → others (3).
- UI: Search bar dengan badge "⚡ Smart Search", live debounce 300ms.
- 3 keyword pertama ditampilkan di setiap kartu artikel.

### Fase 6.4: Global Chat — Ask AI ✅
- Halaman `/library/ask-ai` dengan UI chat fullscreen tema neo-green.
- Keyword RAG: Pecah pertanyaan → cari 3 artikel relevan → kirim konteks ke Gemini.
- Source Attribution: AI wajib menyebutkan judul artikel referensi.
- Persistensi: Chat global disimpan dengan `article_id = null`.
- Sticky FAB button 🤖 di pojok kanan bawah (tersembunyi di halaman detail & ask-ai).

---

## Catatan Teknis

| Aspek | Spesifikasi |
|-------|-------------|
| HTTP Timeout (Analisis) | 300 detik |
| HTTP Timeout (Chat/Reference) | 60 detik |
| Job Timeout | 300 detik |
| Retry | 3x dengan backoff [60s, 120s] |
| Model AI (Analisis) | `gemini-3-flash-preview` |
| Model AI (Chat Artikel) | `gemini-2.5-flash` |
| Model AI (Global Chat) | `gemini-2.0-flash` |
| Model AI (Reference) | `gemini-3-flash-preview` |
| File Size Limit | 10MB (PDF/DOCX) |
| Queue Driver | Database |
| Storage | Local private disk |
| Search | Keyword-based (JSONB ILIKE + ranking) |
| Route Format | `Route::livewire()` untuk full-page components |
| Code Style | Laravel Pint (auto-format) |
| Testing | Pest v4 |

---

## Arsitektur Pencarian

```
User Query → Split Words (min 3 chars)
           → Search: keywords::text ILIKE, title LIKE, author LIKE, analysis_results::text ILIKE
           → Rank: keywords(0) > title(1) > author(2) > others(3)
           → Return sorted results
```

## Arsitektur Global Chat (Keyword RAG)

```
User Question → Split into words
             → Find top 3 articles (keywords/title/analysis match)
             → Extract: title, author, so_what, abstract, keywords
             → Inject as context to Gemini
             → AI answers with source attribution
             → Save to chat_histories (article_id = null)
```
