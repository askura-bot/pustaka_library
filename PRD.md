# **PRODUCT REQUIREMENTS DOCUMENT (PRD): AI-POWERED RESEARCH COMMAND CENTER**

## **1. PENDAHULUAN & TUJUAN (OVERVIEW & GOALS)**

* **Nama Proyek:** AI-Powered Research Command Center (Personal Research Library).
* **Tujuan:** Membantu mahasiswa (khususnya Gen Z) mengelola, memahami, dan menganalisis tumpukan Karya Tulis Ilmiah (KTI) secara instan menggunakan kekuatan Gemini AI.
* **Masalah yang Diselesaikan:** *Information overload* saat riset, kesulitan memahami jurnal akademik yang kompleks, dan proses pembuatan sitasi/rangkuman yang memakan waktu.
* **Target Market:** Mahasiswa Universitas (Gen Z).

---

## **2. PENGGUNAAN TEKNOLOGI (TECH STACK)**

* **Backend:** Laravel 13 (Latest).
* **Frontend:** Tailwind CSS v4 & Livewire 4 (Reaktivitas tinggi tanpa reload).
* **UI Components:** Flux UI v2 (Neubrutalism-ready component library).
* **Database:** PostgreSQL.
* **AI Engine:** Dikonfigurasi via `.env` — model ID bisa diganti tanpa ubah kode:
    * `GEMINI_MODEL_ANALYSIS` — Analisis dokumen (default: `gemini-3-flash-preview`)
    * `GEMINI_MODEL_REFERENCE` — Generate citation/bibliography (default: `gemini-3-flash-preview`)
    * `GEMINI_MODEL_CHAT` — Chat per-artikel (default: `gemini-2.5-flash`)
    * `GEMINI_MODEL_GLOBAL_CHAT` — Global chat (default: `gemini-2.5-flash`)
* **Pencarian:** Keyword-Based Smart Search (JSONB ILIKE query dengan ranking).
* **Autentikasi:** Laravel Fortify (Standard Auth + 2FA) & Laravel Socialite (Google Sign-In).
* **Background Jobs:** Laravel Queues (Database driver) dengan *Exponential Backoff* dan timeout 300 detik.
* **Storage:** Laravel Private Disk (Local) untuk keamanan file PDF/DOCX.
* **Testing:** Pest v4 + PHPUnit v12.

---

## **3. GAYA UI/UX (VISUAL IDENTITY)**

* **Konsep:** **Neubrutalism** dengan **Dark Mode** penuh.
* **Karakteristik:**
    * **Borders:** Hitam tebal (`border-4 border-black`, dark: `dark:border-white/80`) pada semua elemen interaktif.
    * **Shadows:** *Hard shadow* hitam padat (`shadow-neo`). Di dark mode: `rgba(255,255,255,0.12)`.
    * **Warna:** Warna vibran (Kuning Lemon `neo-yellow`, Ungu Elektrik `neo-purple`, Hijau Neon `neo-green`). Di dark mode saturasi sedikit diturunkan agar tidak menyakitkan mata.
    * **Tipografi:** Font Sans-serif tebal dan berani (Instrument Sans).
    * **Interaksi:** Tombol bergeser saat hover (`translate-x-[2px] translate-y-[2px]`) dan shadow mengecil. Active: `translate-x-[4px] translate-y-[4px] shadow-none`.
* **Dark Mode:**
    * Light: Background putih/zinc-50, teks hitam, border hitam, shadow hitam.
    * Dark: Background zinc-950/zinc-900, teks putih/zinc-200, border white/80, shadow white/12.
    * Preferensi disimpan di `localStorage` (key: `appearance`). Mendukung: `light`, `dark`, `system`.
    * Script inline di `<head>` mencegah flash saat load.
* **Navigasi:** Top Navigation (sticky header) — bukan sidebar. Mobile: hamburger menu slide-down.
* **Vibe:** Energik, berani, dan tidak membosankan (Gen Z friendly).

---

## **4. STRUKTUR HALAMAN & FITUR DETAIL**

### **Halaman 1: Landing Page (Home)**

* **Fitur:**
    * **Hero Section:** Slogan provokatif dan tombol CTA "Mulai Riset Sekarang".
    * **Feature Grid:** Penjelasan visual 3 fitur utama (AI Analysis, Smart Search, Custom Template).
    * **Footer:** Copyright dan branding.
* **Akses:** Terbuka untuk publik (Unauthenticated).

### **Halaman 2: Login & Register**

* **Fitur:**
    * **Google Sign-In:** Login cepat satu klik via Laravel Socialite. Password tidak disimpan (null) — user Google tidak perlu password lokal.
    * **Standard Login:** Form email & password bergaya Neubrutalism.
    * **Two-Factor Authentication:** Dukungan TOTP via Laravel Fortify.
    * **Auth Middleware:** Mencegah akses ke library sebelum login.
    * **OAuth Password Bypass:** User Google otomatis bypass password confirmation, bisa set password baru tanpa "current password", dan bisa delete akun tanpa password.

### **Halaman 3: Template Manager (Pengaturan Jenis KTI)**

* **Fitur:**
    * **Default Template "Article":** Otomatis tersedia untuk setiap user baru saat registrasi.
    * **Mandatory Columns (Article):** 5 kolom wajib yang tidak bisa dihapus: `Judul`, `Penulis`, `Jurnal Publikasi`, `Seri Jurnal`, `Link DOI`.
    * **UI Restriction:** Kolom wajib ditampilkan sebagai `readonly` dengan ikon gembok. Tombol hapus template "Article" dinonaktifkan.
    * **CRUD Jenis KTI Custom:** User bisa menambah kategori lain (misal: Skripsi, Jurnal Ekonomi).
    * **Dynamic Column Builder:** User menentukan judul kolom tabel analisis. Untuk template "Article", user hanya boleh menambah kolom baru di atas 5 kolom wajib.

### **Halaman 4: Library Pustaka (Dashboard)**

* **Fitur:**
    * **Top Navigation:** Header sticky dengan logo, nav links (Dashboard, Template, Ask AI), user profile + logout.
    * **Article Grid:** Daftar KTI dengan kartu Neubrutalist berwarna-warni.
    * **Upload Modal:** Dropdown pilih Jenis KTI + Drag & drop file PDF/DOCX (maks 10MB).
    * **Status Indicator:** Badge status per artikel (Proses/Selesai/Gagal) dengan polling otomatis.
    * **Smart Search:** Input field + tombol ikon search. Pencarian keyword-based yang mencari di kolom `title`, `author`, `keywords` (JSONB), dan `analysis_results` (JSONB). Artikel yang cocok di keywords muncul paling atas. Live debounce 300ms.
    * **Keyword Tags:** 3 kata kunci pertama ditampilkan di setiap kartu artikel.
    * **Delete File:** Hapus dokumen (sekaligus menghapus file fisik dari storage).
    * **Global Ask AI (Sticky FAB):** Tombol floating 🤖 di pojok kanan bawah. Tersembunyi di halaman detail artikel dan halaman Ask AI.

### **Halaman 5: Detail Artikel (Research Lab)**

* **Fitur:**
    * **Split Screen Layout:** Kiri (PDF Viewer via iframe) | Kanan (AI Analysis Hub).
    * **PDF Viewer:** File PDF ditampilkan langsung dari private storage via signed route. File DOCX menampilkan fallback download.
    * **Analysis Hub (Sisi Kanan) — Urutan Tampilan:**
        1. **Abstrak:** Kartu putih/dark Neubrutalism.
        2. **Tabel Analisis:** Tabel dengan header `neo-yellow`, border hitam tebal. Menampilkan kolom dari template KTI (untuk Article: Judul, Penulis, Jurnal Publikasi, Seri Jurnal, Link DOI). Field reserved (abstract, so_what, conclusion, keywords) tidak muncul di tabel.
        3. **Kata Kunci:** 5 badge warna-warni (neo-green/neo-purple/neo-yellow bergantian).
        4. **So What?:** Kartu `bg-neo-purple` dengan teks putih.
        5. **Kesimpulan:** Kartu `bg-neo-green` dengan teks hitam.
    * **Citation & Bibliography Generator (On-Demand):**
        * Dropdown style (APA/MLA/IEEE/Harvard) + tombol "Generate Reference".
        * Mengirim JSON ke Gemini, hasilnya disimpan permanen di database.
        * **Conditional Rendering:** Jika belum ada output → tombol "⚡ Generate". Jika sudah ada → tampilkan hasil + tombol "🔄 Regenerate".
    * **Per-Article Chat:** Panel chat di bawah citation. User bisa bertanya tentang isi dokumen. AI menjawab berdasarkan `analysis_results` sebagai konteks. Riwayat chat tersimpan permanen di database.
    * **Loading States:** Skeleton loader saat analisis berjalan, spinner animasi saat generate reference/chat.
    * **Case-Insensitive Mapping:** Kunci JSON dari AI di-mapping ke kolom tabel secara case-insensitive. Mendukung format nested (legacy) maupun flat (baru) via `flattenResults()`.

### **Halaman 6: Ask AI — Global Research Assistant**

* **Route:** `/library/ask-ai`
* **Fitur:**
    * **Fullscreen Chat UI:** Tema `neo-green`, layout chat dengan header + area pesan scrollable + input bar.
    * **Keyword RAG (Retrieval):** Saat user bertanya, sistem memecah pertanyaan menjadi kata-kata, lalu mencari 3 artikel paling relevan berdasarkan kecocokan keywords/title/analysis_results.
    * **Context Injection:** Ringkasan (title, author, so_what, abstract, keywords) dari 3 artikel dikirim ke Gemini sebagai konteks.
    * **Source Attribution:** AI wajib menyebutkan judul artikel yang dijadikan referensi dalam jawaban.
    * **Persistensi:** Semua chat global disimpan di `chat_histories` dengan `article_id = null`.
    * **Empty State:** Contoh pertanyaan sebagai inspirasi user.

### **Halaman 7: Settings**

* **Profile:** Update nama dan email.
* **Security:** Update password (OAuth user: skip current password, label "Set password"). Enable/disable 2FA.
* **Appearance:** Pilih tema (Light/Dark/System). Disimpan di localStorage.
* **Delete Account:** OAuth user tidak perlu password. User biasa perlu konfirmasi password.

---

## **5. STRUKTUR DATABASE (POSTGRESQL)**

* **`users`:** Data autentikasi (name, email, password nullable, google_id, avatar, 2FA columns).
* **`kti_types`:** `id`, `user_id`, `name`, `columns` (JSONB — array nama kolom analisis).
* **`articles`:**
    * `id`, `user_id`, `kti_type_id`
    * `file_path`, `file_name`, `file_type` (pdf/docx)
    * `title` (VARCHAR, nullable — dari hasil AI)
    * `author` (VARCHAR, nullable — dari hasil AI)
    * `year` (VARCHAR(10), nullable — dari hasil AI)
    * `status` (pending/processing/completed/failed)
    * `analysis_results` (JSONB, nullable — hasil lengkap ekstraksi AI)
    * `citation_output` (TEXT, nullable — hasil in-text citation dari AI Pass 2)
    * `bibliography_output` (TEXT, nullable — hasil bibliography dari AI Pass 2)
    * `keywords` (JSONB, nullable — array 5 kata kunci dari AI)
    * `created_at`, `updated_at`
* **`chat_histories`:**
    * `id`, `user_id` (FK), `article_id` (FK nullable — null untuk global chat)
    * `message` (TEXT — pertanyaan user)
    * `response` (TEXT — jawaban AI)
    * `metadata` (JSONB — model, sources, dll)
    * `created_at`, `updated_at`
    * Index composite: `[user_id, article_id]`

---

## **6. ALUR PENGGUNAAN (USER JOURNEY)**

1. **Landing:** User mendarat di Home, melihat fitur, dan klik login.
2. **Auth:** User masuk menggunakan akun Google (tanpa password lokal) atau form standar.
3. **Setup:** User mendapati template "Article" sudah tersedia secara default. User bisa langsung upload atau membuat template custom tambahan.
4. **Upload:** User mengunggah file PDF/DOCX, memilih jenis KTI (misal: "Article").
5. **AI Pass 1 (Otomatis):** Laravel Queue mengirim file ke Gemini. AI mengekstrak data sesuai kolom template + abstract + so_what + conclusion + 5 keywords. Hasil disimpan ke `analysis_results` (JSONB). Kolom `title`, `author`, `year`, `keywords` terisi otomatis.
6. **Review:** User masuk ke halaman detail. Sisi kiri: PDF viewer. Sisi kanan: Abstrak → Tabel Analisis → Keywords → So What → Kesimpulan.
7. **Generate (On-Demand):** User memilih format sitasi (APA/MLA/IEEE/Harvard) dan klik "Generate Reference". AI memformat data JSON menjadi citation & bibliography. Hasilnya disimpan permanen.
8. **Chat Per-Artikel:** User bertanya tentang isi dokumen via chat panel. AI menjawab berdasarkan analysis_results. Riwayat tersimpan.
9. **Smart Search:** Di dashboard, user mencari artikel via input + tombol search. Sistem mencari di title, author, keywords, dan analysis_results dengan ranking (keyword match prioritas tertinggi).
10. **Global Chat:** User klik tombol floating 🤖 → halaman Ask AI. Sistem mencari 3 artikel relevan berdasarkan keyword matching, kirim konteksnya ke AI, AI menjawab dengan menyebutkan sumber.

---

## **7. PENANGANAN STABILITAS & RATE LIMIT**

* **HTTP Timeout:** 300 detik (5 menit) untuk analisis dokumen.
* **Job Timeout:** `$timeout = 300` pada `AnalyzeArticleJob`.
* **Chat Timeout:** 60 detik untuk chat dan reference generation.
* **Exponential Backoff:** Jika API Gemini mengembalikan error 429, job menunggu 60 detik lalu 120 detik. Maksimal 3 percobaan.
* **Model AI (dikonfigurasi via `.env`):**
    * `GEMINI_MODEL_ANALYSIS` — Analisis dokumen
    * `GEMINI_MODEL_REFERENCE` — Generate citation/bibliography
    * `GEMINI_MODEL_CHAT` — Chat per-artikel
    * `GEMINI_MODEL_GLOBAL_CHAT` — Global chat
* **Visual Feedback:** Skeleton loader + polling 3 detik saat analisis. Typing indicator saat chat.
* **File Cleanup:** File dihapus dari Gemini API setelah analisis selesai.

---

## **8. TWO-STEP AI PROCESSING (DETAIL TEKNIS)**

### **Tahap 1: Document Analysis (Otomatis saat Upload)**

* **Trigger:** `AnalyzeArticleJob` di-dispatch saat file berhasil diunggah.
* **Proses:**
    1. Upload file ke Gemini File API.
    2. Tunggu 10 detik (file processing di server Google).
    3. Kirim prompt flat (tanpa kategori) — hanya kunci JSON yang diinginkan.
    4. Parse JSON response.
    5. Simpan ke `analysis_results`. Extract `title`/`author`/`year`/`keywords` ke kolom dedicated.
* **Prompt Output (Article):** `{'Judul', 'Penulis', 'Jurnal Publikasi', 'Seri Jurnal', 'Link DOI', 'abstract', 'so_what', 'conclusion', 'keywords'}`
* **Prompt Output (Custom):** `{kolom template..., 'abstract', 'so_what', 'conclusion', 'keywords'}`

### **Tahap 2: Reference Generation (On-Demand)**

* **Trigger:** User klik tombol "Generate Reference".
* **Proses:** Kirim `analysis_results` JSON ke Gemini → format sesuai style → simpan ke `citation_output` dan `bibliography_output`.

---

## **9. SMART SEARCH (KEYWORD-BASED)**

* **Pendekatan:** Keyword-based search menggunakan PostgreSQL JSONB ILIKE queries (bukan vector/pgvector).
* **Kolom yang dicari:** `title`, `author`, `file_name`, `keywords::text`, `analysis_results::text`.
* **Ranking:**
    * Priority 0: Cocok di `keywords`
    * Priority 1: Cocok di `title`
    * Priority 2: Cocok di `author`
    * Priority 3: Cocok di `analysis_results`
* **UI:** Input field + tombol ikon search (Neubrutalism), live debounce 300ms, counter hasil.

---

## **10. DARK MODE IMPLEMENTATION**

* **Mekanisme:** Class-based (`<html class="dark">`).
* **Preferensi:** Disimpan di `localStorage` key `appearance`. Nilai: `light`, `dark`, `system`.
* **Script:** Inline di `<head>` (sebelum paint) untuk mencegah flash.
* **CSS Variables:** Warna neon di-desaturasi di dark mode. Shadow menggunakan `rgba(255,255,255,0.12)`.
* **Flux UI Override:** `[data-flux-control]`, `[data-flux-button]`, `[data-flux-card]` di-override dengan border tebal dan rounded-none.
* **Utility Classes:** `.neo-border`, `.neo-shadow`, `.neo-input`, `.neo-btn` semua mendukung `dark:` variant.

---

## **11. OAUTH & PASSWORD HANDLING**

* **Google Sign-In:** Password disimpan sebagai `null` (bukan random hash).
* **Password Confirmation Bypass:** Middleware `BypassPasswordConfirmForOAuth` auto-confirm untuk user Google.
* **Security Page:** User Google bisa set password baru tanpa "current password". Label tombol: "Set password".
* **Delete Account:** User Google tidak perlu memasukkan password untuk konfirmasi.
* **Deteksi OAuth User:** `$user->google_id && empty($user->password)`.

---

**Instruksi untuk Pengembang:**

> *"Gunakan PRD ini sebagai panduan tunggal. Implementasikan gaya Neubrutalism secara ketat dengan dark mode penuh. Gunakan Top Navigation (bukan sidebar). Model AI dikonfigurasi via .env agar mudah diganti. Gunakan PostgreSQL JSONB untuk fleksibilitas template dan keyword search. Two-step AI processing memisahkan analisis dokumen (berat, otomatis) dari formatting sitasi (ringan, on-demand). Pastikan timeout 300 detik dan exponential backoff untuk stabilitas free tier. Prompt AI harus flat tanpa kategori — hanya kunci JSON yang diinginkan. User Google tidak punya password lokal — handle semua flow yang membutuhkan password dengan bypass/skip."*
