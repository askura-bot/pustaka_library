# **PRODUCT REQUIREMENTS DOCUMENT (PRD): AI-POWERED RESEARCH COMMAND CENTER**

## **1. PENDAHULUAN & TUJUAN (OVERVIEW & GOALS)**

* **Nama Proyek:** AI-Powered Research Command Center (Personal Research Library).
* **Tujuan:** Membantu mahasiswa (khususnya Gen Z) mengelola, memahami, dan menganalisis tumpukan Karya Tulis Ilmiah (KTI) secara instan menggunakan kekuatan Gemini 3 Flash.
* **Masalah yang Diselesaikan:** *Information overload* saat riset, kesulitan memahami jurnal akademik yang kompleks, dan proses pembuatan sitasi/rangkuman yang memakan waktu.
* **Target Market:** Mahasiswa Universitas (Gen Z).

---

## **2. PENGGUNAAN TEKNOLOGI (TECH STACK)**

* **Backend:** Laravel 13 (Latest).
* **Frontend:** Tailwind CSS v4 & Livewire 4 (Reaktivitas tinggi tanpa reload).
* **UI Components:** Flux UI v2 (Neubrutalism-ready component library).
* **Database:** PostgreSQL (Wajib menggunakan ekstensi `pgvector` untuk pencarian semantik di fase lanjutan).
* **AI Engine:** Gemini 3 Flash-preview via API Key (Free Tier).
* **Autentikasi:** Laravel Fortify (Standard Auth + 2FA) & Laravel Socialite (Google Sign-In).
* **Background Jobs:** Laravel Queues (Database driver) dengan *Exponential Backoff* dan timeout 300 detik.
* **Storage:** Laravel Private Disk (Local) untuk keamanan file PDF/DOCX.
* **Testing:** Pest v4 + PHPUnit v12.

---

## **3. GAYA UI/UX (VISUAL IDENTITY)**

* **Konsep:** **Neubrutalism**.
* **Karakteristik:**
    * **Borders:** Hitam tebal (`border-4 border-black`) pada semua elemen interaktif.
    * **Shadows:** *Hard shadow* hitam padat (`shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]`) — disingkat `shadow-neo`.
    * **Warna:** Warna vibran (Kuning Lemon `neo-yellow`, Ungu Elektrik `neo-purple`, Hijau Neon `neo-green`) dipadukan dengan latar belakang putih atau abu-abu muda.
    * **Tipografi:** Font Sans-serif tebal dan berani (Instrument Sans).
    * **Interaksi:** Tombol bergeser saat hover (`translate-x-[2px] translate-y-[2px]`) dan shadow mengecil.
* **Vibe:** Energik, berani, dan tidak membosankan (Gen Z friendly).

---

## **4. STRUKTUR HALAMAN & FITUR DETAIL**

### **Halaman 1: Landing Page (Home)**

* **Fitur:**
    * **Hero Section:** Slogan provokatif dan tombol CTA "Mulai Riset Sekarang".
    * **Feature Grid:** Penjelasan visual 3 fitur utama (AI Analysis, Semantic Search, Custom Template).
    * **Footer:** Copyright dan branding.
* **Akses:** Terbuka untuk publik (Unauthenticated).

### **Halaman 2: Login & Register**

* **Fitur:**
    * **Google Sign-In:** Login cepat satu klik via Laravel Socialite.
    * **Standard Login:** Form email & password bergaya Neubrutalism.
    * **Two-Factor Authentication:** Dukungan TOTP via Laravel Fortify.
    * **Auth Middleware:** Mencegah akses ke library sebelum login.

### **Halaman 3: Template Manager (Pengaturan Jenis KTI)**

* **Fitur:**
    * **Default Template "Article":** Otomatis tersedia untuk setiap user baru saat registrasi.
    * **Mandatory Columns (Article):** 5 kolom wajib yang tidak bisa dihapus: `Judul`, `Penulis`, `Jurnal Publikasi`, `Seri Jurnal`, `Link DOI`.
    * **UI Restriction:** Kolom wajib ditampilkan sebagai `readonly` dengan ikon gembok. Tombol hapus template "Article" dinonaktifkan.
    * **CRUD Jenis KTI Custom:** User bisa menambah kategori lain (misal: Skripsi, Jurnal Ekonomi).
    * **Dynamic Column Builder:** User menentukan judul kolom tabel analisis. Untuk template "Article", user hanya boleh menambah kolom baru di atas 5 kolom wajib.

### **Halaman 4: Library Pustaka (Dashboard)**

* **Fitur:**
    * **Article Grid:** Daftar KTI dengan kartu Neubrutalist berwarna-warni.
    * **Upload Modal:** Dropdown pilih Jenis KTI + Drag & drop file PDF/DOCX (maks 10MB).
    * **Status Indicator:** Badge status per artikel (Proses/Selesai/Gagal) dengan polling otomatis.
    * **Delete File:** Hapus dokumen (sekaligus menghapus file fisik dari storage).
    * **Search Bar:** Pencarian teks (Fase lanjutan: Semantic Search via pgvector).

### **Halaman 5: Detail Artikel (Research Lab)**

* **Fitur:**
    * **Split Screen Layout:** Kiri (PDF Viewer via iframe) | Kanan (AI Analysis Hub).
    * **PDF Viewer:** File PDF ditampilkan langsung dari private storage via signed route. File DOCX menampilkan fallback download.
    * **AI Analysis Output (8 Poin untuk Article):**
        1. Abstrak (ringkasan Bahasa Indonesia)
        2. Judul (dari template)
        3. Penulis (dari template)
        4. Jurnal Publikasi (dari template)
        5. Seri Jurnal (dari template)
        6. Link DOI (dari template)
        7. So What? (esensi/relevansi penelitian)
        8. Kesimpulan (conclusion)
    * **Tabel Analisis Dinamis:** Merender kolom template dalam tabel Neubrutalism (header kuning, border hitam tebal).
    * **Citation & Bibliography Generator (Two-Step):**
        * **Quick Format (Local):** Formatter instan tanpa API call — tab In-Text/Bibliography dengan dropdown style.
        * **AI Reference Generator (On-Demand):** Dropdown style + tombol "Generate Reference". Mengirim JSON ke Gemini, hasilnya disimpan permanen di database.
        * **Conditional Rendering:** Jika belum ada output → tombol "⚡ Generate". Jika sudah ada → tampilkan hasil + tombol "🔄 Regenerate".
    * **Loading States:** Skeleton loader saat analisis berjalan, spinner animasi saat generate reference.
    * **Error Handling:** State gagal dengan tombol retry.

---

## **5. STRUKTUR DATABASE (POSTGRESQL)**

* **`users`:** Data autentikasi (name, email, password, google_id, avatar, 2FA columns).
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
    * `created_at`, `updated_at`
* **`chat_histories`** (Fase lanjutan):
    * `id`, `user_id`, `article_id` (null jika global), `message`, `sender` (user/ai).

---

## **6. ALUR PENGGUNAAN (USER JOURNEY)**

1. **Landing:** User mendarat di Home, melihat fitur, dan klik login.
2. **Auth:** User masuk menggunakan akun Google atau form standar.
3. **Setup:** User mendapati template "Article" sudah tersedia secara default. User bisa langsung upload atau membuat template custom tambahan.
4. **Upload:** User mengunggah file PDF/DOCX, memilih jenis KTI (misal: "Article").
5. **AI Pass 1 (Otomatis):** Laravel Queue mengirim file ke Gemini 3 Flash. AI mengekstrak 8 poin informasi (untuk Article) atau 3+n poin (untuk custom). Hasil disimpan ke `analysis_results` (JSONB). Kolom `title`, `author`, `year` terisi otomatis dari hasil ekstraksi.
6. **Review:** User masuk ke halaman detail, membaca dokumen asli di kiri, dan melihat tabel hasil analisis yang sudah terisi di kanan.
7. **Generate (On-Demand):** User memilih format sitasi (APA/MLA/IEEE/Harvard) dan klik "Generate Reference".
8. **AI Pass 2:** Sistem mengirimkan data JSON (bukan file PDF) ke Gemini. AI memformat data menjadi in-text citation dan bibliography. Hasilnya disimpan permanen ke `citation_output` dan `bibliography_output`.
9. **Output:** User menyalin (copy) hasil sitasi/bibliografi untuk tugas mereka. Jika ingin ganti style, klik "Regenerate".

---

## **7. PENANGANAN STABILITAS & RATE LIMIT**

* **HTTP Timeout:** 300 detik (5 menit) untuk `GeminiService::analyzeDocument()` agar dokumen panjang tidak timeout.
* **Job Timeout:** `$timeout = 300` pada `AnalyzeArticleJob` agar sinkron dengan HTTP timeout.
* **Exponential Backoff:** Jika API Gemini mengembalikan error 429 (Too Many Requests), job akan menunggu secara bertahap (60 detik, lalu 120 detik) sebelum retry. Maksimal 3 kali percobaan.
* **Model AI:** Gemini 3 Flash-preview (model ringan dengan limit lebih besar untuk free tier).
* **Visual Feedback:** User melihat status "AI LAGI MEMBEDAH ISI FILE..." dengan skeleton loader, bukan error/crash. Polling setiap 3 detik untuk update status.
* **File Cleanup:** File yang diunggah ke Gemini API dihapus otomatis setelah analisis selesai (di blok `finally`).

---

## **8. TWO-STEP AI PROCESSING (DETAIL TEKNIS)**

### **Tahap 1: Document Analysis (Otomatis saat Upload)**

* **Trigger:** `AnalyzeArticleJob` di-dispatch saat file berhasil diunggah.
* **Proses:**
    1. Upload file ke Gemini File API.
    2. Tunggu 10 detik (file processing di server Google).
    3. Kirim prompt analisis dengan kolom template + universal fields.
    4. Parse JSON response.
    5. Simpan ke `analysis_results`, extract `title`/`author`/`year` ke kolom dedicated.
* **Prompt Logic:**
    * Jika KTI type = "Article": Prompt spesifik untuk 8 poin (5 template + 3 universal).
    * Jika KTI type lainnya: Prompt generik (n template columns + 3 universal).
    * Metadata (`title`, `author`, `year`) selalu diekstrak untuk semua tipe.

### **Tahap 2: Reference Generation (On-Demand saat User Klik)**

* **Trigger:** User klik tombol "Generate Reference" di halaman detail.
* **Proses:**
    1. Ambil `analysis_results` JSON dari database (tidak perlu upload file lagi).
    2. Kirim ke Gemini dengan prompt formatting sesuai style yang dipilih.
    3. Parse response JSON (`{citation, bibliography}`).
    4. Simpan ke `citation_output` dan `bibliography_output`.
* **Keuntungan:** Cepat (tidak perlu upload file), hemat quota API, bisa di-regenerate kapan saja.

---

**Instruksi untuk Pengembang:**

> *"Gunakan PRD ini sebagai panduan tunggal. Implementasikan gaya Neubrutalism secara ketat pada setiap komponen Tailwind CSS. Gunakan database PostgreSQL dengan skema JSONB untuk fleksibilitas template. Two-step AI processing memisahkan analisis dokumen (berat, otomatis) dari formatting sitasi (ringan, on-demand). Pastikan timeout 300 detik dan exponential backoff untuk stabilitas free tier."*
