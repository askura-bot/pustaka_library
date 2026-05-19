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
    * `GEMINI_MODEL_FOLDER_CHAT` — Chat folder (default: `gemini-2.5-flash`)
* **Pencarian:** Keyword-Based Smart Search (JSONB ILIKE query dengan ranking).
* **Autentikasi:** Laravel Fortify (Standard Auth) & Laravel Socialite (Google Sign-In).
* **Background Jobs:** Laravel Queues (Database driver) dengan *Exponential Backoff* dan timeout 300 detik.
* **Storage:** Laravel Private Disk (Local) untuk keamanan file PDF/DOCX.
* **Testing:** Pest v4 + PHPUnit v12.
* **Timezone:** Asia/Jakarta (WIB).

---

## **3. GAYA UI/UX (VISUAL IDENTITY)**

* **Konsep:** **Neubrutalism** — Light Mode Only.
* **Palet Warna:**
    * **Background Utama:** Off-White `#F8F7FF` (`neo-offwhite`)
    * **Primary Purple:** `#8B5CF6` (`neo-purple`) — elemen kunci, tombol utama
    * **Secondary Lilac:** `#EDE9FE` (`neo-lilac`) — background kartu, area sekunder
    * **Accent Yellow:** `#FACC15` (`neo-yellow`) — CTA, badge, header tabel
    * **Accent Green:** `#4ADE80` (`neo-green`) — sukses, chat AI, FAB button
    * **Borders & Text:** Hitam pekat `#000000`
* **Karakteristik:**
    * **Borders:** Hitam tebal (`border-4 border-black`) pada semua elemen interaktif.
    * **Shadows:** *Hard shadow* hitam padat (`shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]`) — disingkat `shadow-neo`.
    * **Tipografi:** Font Sans-serif tebal dan berani (Instrument Sans).
    * **Interaksi:** Tombol bergeser saat hover (`translate-x-[2px] translate-y-[2px]`) dan shadow mengecil. Active: `translate-x-[4px] translate-y-[4px] shadow-none`.
    * **Cards:** Variasi warna background (Lilac, Yellow, White) untuk kontras.
* **Navigasi:** Top Navigation (sticky header) — bukan sidebar. Mobile: hamburger menu slide-down.
* **Vibe:** Energik, berani, ceria, dan tidak membosankan (Gen Z friendly).

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
    * **Google Sign-In:** Login cepat satu klik via Laravel Socialite. Password disimpan sebagai `null`.
    * **Set Password Flow:** Setelah login Google pertama kali, user diarahkan ke halaman Security untuk set password lokal.
    * **Standard Login:** Form email & password bergaya Neubrutalism.
    * **Auth Middleware:** Mencegah akses ke library sebelum login.
    * **OAuth Password Handling:** User Google otomatis bypass password confirmation, bisa set password baru tanpa "current password", dan bisa delete akun tanpa password.

### **Halaman 3: Template Manager (Pengaturan Jenis KTI)**

* **Fitur:**
    * **Default Template "Article":** Otomatis tersedia untuk setiap user baru saat registrasi.
    * **Mandatory Columns (Article):** 5 kolom wajib yang tidak bisa dihapus: `Judul`, `Penulis`, `Jurnal Publikasi`, `Seri Jurnal`, `Link DOI`.
    * **UI Restriction:** Kolom wajib ditampilkan sebagai `readonly` dengan ikon gembok. Tombol hapus template "Article" dinonaktifkan.
    * **CRUD Jenis KTI Custom:** User bisa menambah kategori lain (misal: Skripsi, Jurnal Ekonomi).
    * **Dynamic Column Builder:** User menentukan judul kolom tabel analisis. Untuk template "Article", user hanya boleh menambah kolom baru di atas 5 kolom wajib.

### **Halaman 4: Library Pustaka (Dashboard)**

* **Fitur:**
    * **Top Navigation:** Header sticky dengan logo (Vibrant Purple), nav links (Dashboard, Template, Ask AI), user profile + logout.
    * **Folder Section:** Grid kartu folder (`bg-neo-lilac`) dengan badge jumlah artikel, tombol Edit/Delete. Modal create/edit folder.
    * **Article Grid:** Daftar KTI dengan kartu Neubrutalist berwarna-warni.
    * **"Not Linked" Badge:** Artikel yang tidak terhubung ke folder mana pun menampilkan badge abu-abu "📂 not linked folder".
    * **Upload Modal:** Dropdown pilih Jenis KTI + Drag & drop file PDF/DOCX (maks 10MB).
    * **Status Indicator:** Badge status per artikel (Proses/Selesai/Gagal) dengan polling otomatis.
    * **Smart Search:** Input field + tombol ikon search (Neubrutalism purple). Pencarian keyword-based. Live debounce 300ms.
    * **Category Filter:** Dropdown filter kategori (dynamic dari database). Warna aksen kuning saat aktif. Opsi: "Semua Kategori" + daftar kategori unik.
    * **Category Badge:** Setiap kartu artikel menampilkan badge kategori (`bg-neo-lilac`, border hitam 2px). Fallback: "Belum Dikategorikan".
    * **Keyword Tags:** 3 kata kunci pertama ditampilkan di setiap kartu artikel.
    * **Smart Delete Warning:** Modal konfirmasi hapus menampilkan nama folder terkait: "Artikel ini tertaut di Folder: [A], [B]. Menghapus akan menghapusnya dari semua folder."
    * **Global Ask AI (Sticky FAB):** Tombol floating 🤖 di pojok kanan bawah. Tersembunyi di halaman detail artikel, Ask AI, dan folder.

### **Halaman 5: Detail Artikel (Research Lab)**

* **Fitur:**
    * **Split Screen Layout:** Kiri (PDF Viewer via iframe) | Kanan (AI Analysis Hub).
    * **PDF Viewer:** File PDF ditampilkan langsung dari private storage via signed route. File DOCX menampilkan fallback download.
    * **Analysis Hub (Sisi Kanan) — Urutan Tampilan:**
        1. **Abstrak:** Kartu `bg-neo-lilac` Neubrutalism.
        2. **Tabel Analisis:** Tabel dengan header `neo-yellow`, border hitam tebal. Menampilkan kolom dari template KTI. Field reserved (abstract, so_what, conclusion, keywords) tidak muncul di tabel.
        3. **Kata Kunci:** 5 badge warna-warni (neo-green/neo-purple/neo-yellow bergantian).
        4. **So What?:** Kartu `bg-neo-purple` dengan teks putih.
        5. **Kesimpulan:** Kartu `bg-neo-green` dengan teks hitam.
    * **Citation & Bibliography Generator (On-Demand):**
        * Dropdown style (APA/MLA/IEEE/Harvard) + tombol "Generate Reference".
        * Mengirim JSON ke Gemini, hasilnya disimpan permanen di database.
        * **Conditional Rendering:** Jika belum ada output → tombol "⚡ Generate". Jika sudah ada → tampilkan hasil + tombol "🔄 Regenerate".
    * **Per-Article Chat (Wide Chat Arena):** Panel chat full-width di bawah split screen (`mt-8`). Header `bg-[#8B5CF6]` (Vibrant Purple). Area pesan `max-h-[500px] min-h-[300px]`. Balon chat user (kuning) dan AI (ungu) dengan `max-w-[75%]`. Quick Prompt Suggestions (3 tombol `bg-neo-lilac`) muncul saat chat kosong.
    * **Loading States:** Skeleton loader saat analisis berjalan, spinner animasi saat generate reference/chat.
    * **Case-Insensitive Mapping:** Kunci JSON dari AI di-mapping ke kolom tabel secara case-insensitive. Mendukung format nested (legacy) maupun flat (baru) via `flattenResults()`.

### **Halaman 6: Ask AI — Global Research Assistant**

* **Route:** `/library/ask-ai`
* **Fitur:**
    * **Fullscreen Chat UI:** Tema `neo-green`, layout chat dengan header + area pesan scrollable + input bar.
    * **Keyword RAG (Retrieval):** Saat user bertanya, sistem memecah pertanyaan menjadi kata-kata, lalu mencari 3 artikel paling relevan.
    * **Context Injection:** Ringkasan (title, author, so_what, abstract, keywords) dari 3 artikel dikirim ke Gemini sebagai konteks.
    * **Source Attribution:** AI wajib menyebutkan judul artikel yang dijadikan referensi dalam jawaban.
    * **Persistensi:** Semua chat global disimpan di `chat_histories` dengan `article_id = null` dan `folder_id = null`.
    * **Empty State:** Contoh pertanyaan sebagai inspirasi user.

### **Halaman 7: Folder View**

* **Route:** `/library/folders/{folder}`
* **Fitur:**
    * **Folder Info:** Nama folder, deskripsi, jumlah artikel.
    * **Article Grid:** Daftar artikel dalam folder dengan warna cycling.
    * **"Lepaskan" Button:** Setiap artikel punya tombol "🔗‍💥 Lepas" yang hanya menghapus relasi pivot (artikel tetap ada di Dashboard).
    * **Add Existing Article:** Modal untuk menambahkan artikel yang sudah ada ke folder (search + list).
    * **In-Folder Upload:** Tombol "📄 Upload Baru" — upload langsung ke folder. Artikel otomatis masuk ke folder + dispatch AI analysis.
    * **Folder Chatbot (Sticky Button):** Tombol 💬 `bg-neo-purple` di pojok kanan bawah. Membuka panel chat floating (380px). AI menjawab berdasarkan konteks semua artikel dalam folder.
    * **Auto-Sync Context:** Setiap kali artikel ditambah/dilepas, `SyncFolderContextJob` berjalan di background untuk memperbarui `context_cache`.

### **Halaman 8: Settings**

* **Profile:** Update nama dan email. Delete account (Neubrutalism modal — background putih, tombol merah).
* **Security:** Update password (OAuth user: skip current password, label "Set password"). Flash message setelah login Google pertama kali.
* **Delete Account:** OAuth user tidak perlu password. Custom Neubrutalism modal (bukan Flux default hitam).

---

## **5. STRUKTUR DATABASE (POSTGRESQL)**

* **`users`:** Data autentikasi (name, email, password nullable, google_id, avatar).
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
    * `category` (VARCHAR, nullable — klasifikasi bidang ilmu dari AI)
    * `created_at`, `updated_at`
* **`folders`:**
    * `id`, `user_id` (FK cascade)
    * `name` (VARCHAR)
    * `description` (VARCHAR, nullable)
    * `context_cache` (TEXT, nullable — cached AI context string)
    * `created_at`, `updated_at`
* **`article_folder`** (Pivot — Many-to-Many):
    * `id`, `article_id` (FK cascade), `folder_id` (FK cascade)
    * `created_at`, `updated_at`
    * Unique constraint: `[article_id, folder_id]`
* **`chat_histories`:**
    * `id`, `user_id` (FK), `article_id` (FK nullable), `folder_id` (FK nullable — cascade on delete)
    * `message` (TEXT — pertanyaan user)
    * `response` (TEXT — jawaban AI)
    * `metadata` (JSONB — model, sources, dll)
    * `created_at`, `updated_at`
    * Index composite: `[user_id, article_id]`

---

## **6. ALUR PENGGUNAAN (USER JOURNEY)**

1. **Landing:** User mendarat di Home, melihat fitur, dan klik login.
2. **Auth:** User masuk menggunakan akun Google → diarahkan ke Set Password. Atau login via form standar.
3. **Setup:** User mendapati template "Article" sudah tersedia secara default.
4. **Upload:** User mengunggah file PDF/DOCX, memilih jenis KTI (misal: "Article").
5. **AI Pass 1 (Otomatis):** Laravel Queue mengirim file ke Gemini. AI mengekstrak data sesuai kolom template + abstract + so_what + conclusion + 5 keywords. Hasil disimpan ke `analysis_results`. Kolom `title`, `author`, `year`, `keywords` terisi otomatis.
6. **Review:** User masuk ke halaman detail. Sisi kiri: PDF viewer. Sisi kanan: Abstrak (lilac) → Tabel Analisis (yellow header) → Keywords (badges) → So What (purple) → Kesimpulan (green).
7. **Generate (On-Demand):** User memilih format sitasi dan klik "Generate Reference". AI memformat data JSON. Hasilnya disimpan permanen.
8. **Chat Per-Artikel:** User bertanya tentang isi dokumen via chat panel. AI menjawab berdasarkan analysis_results. Riwayat tersimpan.
9. **Folder Management:** User membuat folder, menambahkan artikel (existing atau upload baru). Konteks folder di-sync otomatis.
10. **Folder Chat:** User membuka folder → klik tombol 💬 → chat dengan AI berdasarkan semua artikel dalam folder.
11. **Smart Search:** Di dashboard, user mencari artikel. Sistem mencari di title, author, keywords, dan analysis_results dengan ranking.
12. **Global Chat:** User klik tombol floating 🤖 → halaman Ask AI. Sistem mencari 3 artikel relevan, kirim konteksnya ke AI, AI menjawab dengan menyebutkan sumber.

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
    * `GEMINI_MODEL_FOLDER_CHAT` — Chat folder
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
* **Prompt Output (Article):** `{'Judul', 'Penulis', 'Jurnal Publikasi', 'Seri Jurnal', 'Link DOI', 'abstract', 'so_what', 'conclusion', 'keywords', 'category'}`
* **Prompt Output (Custom):** `{kolom template..., 'abstract', 'so_what', 'conclusion', 'keywords', 'category'}`

### **Tahap 2: Reference Generation (On-Demand)**

* **Trigger:** User klik tombol "Generate Reference".
* **Proses:** Kirim `analysis_results` JSON ke Gemini → format sesuai style → simpan ke `citation_output` dan `bibliography_output`.

---

## **9. SMART SEARCH (KEYWORD-BASED)**

* **Pendekatan:** Keyword-based search menggunakan PostgreSQL JSONB ILIKE queries.
* **Kolom yang dicari:** `title`, `author`, `file_name`, `keywords::text`, `analysis_results::text`.
* **Ranking:**
    * Priority 0: Cocok di `keywords`
    * Priority 1: Cocok di `title`
    * Priority 2: Cocok di `author`
    * Priority 3: Cocok di `analysis_results`
* **UI:** Input field + tombol ikon search (purple Neubrutalism), live debounce 300ms, counter hasil.

---

## **10. FOLDER SYSTEM**

### **Konsep**
Folder adalah pengelompokan logis artikel (many-to-many). Satu artikel bisa masuk ke banyak folder. Folder memiliki chatbot AI sendiri yang menjawab berdasarkan konteks semua artikel di dalamnya.

### **Relasi Database**
* `folders` ↔ `articles` via pivot `article_folder` (many-to-many)
* `folders` → `chat_histories` (one-to-many, cascade on delete)

### **Deletion Logic**
* **Hapus Folder:** Hanya menghapus folder + pivot records + chat histories. Artikel tetap ada di Dashboard.
* **Lepaskan Artikel dari Folder:** Hanya menghapus record pivot. Artikel tetap ada di Dashboard dan folder lain.
* **Hapus Artikel dari Dashboard:** Menghapus artikel + file fisik + semua pivot records (otomatis hilang dari semua folder).

### **Folder Chatbot**
* **Model:** `GEMINI_MODEL_FOLDER_CHAT` dari `.env`
* **Context:** `FolderBrainService` mengumpulkan semua `analysis_results` dari artikel dalam folder → build summary text → cache di `folders.context_cache`
* **Auto-Sync:** `SyncFolderContextJob` dispatch otomatis saat artikel ditambah/dilepas/diupload ke folder
* **UI:** Sticky button 💬 `bg-neo-purple` di pojok kanan bawah (hanya di halaman folder). Panel chat floating 380px.
* **Isolation:** Global FAB tersembunyi di halaman folder. User fokus pada folder chatbot.

### **Dashboard Intelligence**
* **"Not Linked" Badge:** Artikel tanpa folder menampilkan badge abu-abu.
* **Smart Delete Warning:** Modal konfirmasi menampilkan nama folder terkait sebelum hapus.

---

## **11. OAUTH & PASSWORD HANDLING**

* **Google Sign-In:** Password disimpan sebagai `null` (bukan random hash).
* **Set Password Flow:** Setelah login Google pertama kali (password null), user di-redirect ke `/settings/security` dengan flash message untuk set password.
* **Password Confirmation Bypass:** Middleware `BypassPasswordConfirmForOAuth` auto-confirm untuk user Google yang belum set password.
* **Security Page:** User Google bisa set password baru tanpa "current password". Label tombol: "Set password".
* **Delete Account:** User Google tidak perlu memasukkan password untuk konfirmasi. Custom Neubrutalism modal (background putih).
* **Deteksi OAuth User:** `$user->google_id && empty($user->password)`.

---

## **12. FLUX UI OVERRIDES (CSS)**

Flux UI components di-override agar sesuai Neubrutalism:
* **Inputs** (`[data-flux-control]`, `[data-flux-input]`): `border-4! border-black! rounded-none! bg-white! text-black!`
* **Buttons** (`[data-flux-button]`): `border-4! border-black! rounded-none! shadow-neo bg-neo-purple! text-white!`
* **Button danger variant**: `bg-red-500! text-white!`
* **Cards** (`[data-flux-card]`): `border-4 border-black rounded-none! shadow-neo`
* **Labels** (`[data-flux-label]`): `font-bold text-black`
* **Modals** (`[data-flux-modal]`): `bg-white! text-black! border-4! border-black! rounded-none!`
* **Headings** (`[data-flux-heading]`): `font-black! uppercase!`

---

**Instruksi untuk Pengembang:**

> *"Gunakan PRD ini sebagai panduan tunggal. Implementasikan gaya Neubrutalism secara ketat — Light Mode Only dengan palet vibran (Purple, Lilac, Yellow, Green, Off-White). Gunakan Top Navigation (bukan sidebar). Model AI dikonfigurasi via .env (termasuk folder chat). Gunakan PostgreSQL JSONB untuk fleksibilitas template dan keyword search. Two-step AI processing memisahkan analisis dokumen (berat, otomatis) dari formatting sitasi (ringan, on-demand). Folder system menggunakan many-to-many pivot — hapus folder tidak hapus artikel. Folder chatbot menggunakan cached context yang di-sync otomatis. Pastikan timeout 300 detik dan exponential backoff. Prompt AI harus flat tanpa kategori. User Google di-redirect ke Set Password setelah login pertama. Timezone: Asia/Jakarta (WIB)."*

---

## **13. SMART CATEGORY SYSTEM**

### **Konsep**
AI secara otomatis mengklasifikasikan setiap artikel ke dalam salah satu dari 9 kategori bidang ilmu standar (Closed-Set Classification).

### **Daftar Kategori (Fixed)**
1. Sains & Teknologi
2. Kesehatan & Kedokteran
3. Ekonomi, Bisnis & Akuntansi
4. Sosial & Humaniora
5. Hukum & Politik
6. Pendidikan & Bahasa
7. Pertanian, Lingkungan & Logistik
8. Seni, Desain & Media
9. Multidisiplin / Umum

### **Mekanisme**
* **Auto-Classification:** Saat upload, prompt AI menyertakan instruksi untuk memilih 1 kategori dari daftar di atas. Hasilnya disimpan ke kolom `articles.category`.
* **Batch Sync:** Command `php artisan library:classify-articles` untuk mengisi kategori artikel lama yang belum terklasifikasi.
* **Dashboard Filter:** Dropdown filter kategori (dynamic options dari `SELECT DISTINCT category`). Warna aksen kuning saat aktif.
* **Visual Badge:** Setiap kartu artikel menampilkan badge kategori (`bg-neo-lilac`, border hitam 2px). Fallback: "Belum Dikategorikan".

### **Detail Artikel — Layout**
* **Split Screen:** `grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch lg:h-[780px]`
* **Sisi Kiri:** PDF viewer `h-full flex flex-col`, iframe `w-full h-full grow`
* **Sisi Kanan:** `h-full overflow-y-auto pr-2 flex flex-col gap-5` — scrollable sejajar PDF
* **Wide Chat Arena:** Full-width di bawah grid (`mt-8`), header `bg-[#8B5CF6]`, area pesan `max-h-[500px] min-h-[300px]`
* **Quick Prompt Suggestions:** 3 tombol `bg-neo-lilac` muncul saat chat kosong

---

## **14. ENTITY RELATIONSHIP DIAGRAM (ERD)**

### **Diagram Tekstual**

```
┌──────────────┐       ┌──────────────┐       ┌──────────────────┐
│    users     │       │  kti_types   │       │     folders      │
├──────────────┤       ├──────────────┤       ├──────────────────┤
│ id (PK)      │──┐    │ id (PK)      │       │ id (PK)          │
│ name         │  │    │ user_id (FK) │──┐    │ user_id (FK)     │──┐
│ email        │  │    │ name         │  │    │ name             │  │
│ password?    │  │    │ columns      │  │    │ description?     │  │
│ google_id?   │  │    │ created_at   │  │    │ context_cache?   │  │
│ avatar?      │  │    │ updated_at   │  │    │ created_at       │  │
│ created_at   │  │    └──────────────┘  │    │ updated_at       │  │
│ updated_at   │  │                      │    └──────────────────┘  │
└──────────────┘  │                      │                          │
                  │                      │                          │
    ┌─────────────┼──────────────────────┼──────────────────────────┘
    │             │                      │
    │    ┌────────┴──────────────────────┴───────────────┐
    │    │                 articles                       │
    │    ├───────────────────────────────────────────────┤
    │    │ id (PK)                                       │
    │    │ user_id (FK) ─────────────────────────────────│──→ users.id
    │    │ kti_type_id (FK) ─────────────────────────────│──→ kti_types.id
    │    │ file_path, file_name, file_type               │
    │    │ title?, author?, year?                        │
    │    │ status (pending/processing/completed/failed)  │
    │    │ analysis_results (JSONB)?                     │
    │    │ citation_output (TEXT)?                       │
    │    │ bibliography_output (TEXT)?                   │
    │    │ keywords (JSONB)?                             │
    │    │ category (VARCHAR)?                            │
    │    │ created_at, updated_at                        │
    │    └───────────────────────────────────────────────┘
    │                          │
    │                          │ Many-to-Many
    │                          ▼
    │    ┌───────────────────────────────────────┐
    │    │          article_folder (Pivot)        │
    │    ├───────────────────────────────────────┤
    │    │ id (PK)                               │
    │    │ article_id (FK) ──→ articles.id       │ CASCADE
    │    │ folder_id (FK) ───→ folders.id        │ CASCADE
    │    │ created_at, updated_at                │
    │    │ UNIQUE(article_id, folder_id)         │
    │    └───────────────────────────────────────┘
    │
    │    ┌───────────────────────────────────────────────┐
    │    │              chat_histories                    │
    │    ├───────────────────────────────────────────────┤
    │    │ id (PK)                                       │
    │    │ user_id (FK) ─────────────────────────────────│──→ users.id
    │    │ article_id (FK, nullable) ────────────────────│──→ articles.id (CASCADE)
    │    │ folder_id (FK, nullable) ─────────────────────│──→ folders.id (CASCADE)
    │    │ message (TEXT)                                 │
    │    │ response (TEXT)                                │
    │    │ metadata (JSONB)?                              │
    │    │ created_at, updated_at                         │
    │    │ INDEX(user_id, article_id)                     │
    │    └───────────────────────────────────────────────┘
    │
    └──→ users.id (semua FK user_id mengarah ke sini)
```

### **Penjelasan Entitas**

| Entitas | Deskripsi | Jumlah Kolom |
|---------|-----------|:------------:|
| **users** | Data pengguna (autentikasi, profil, OAuth) | 7 |
| **kti_types** | Template jenis KTI dengan kolom analisis dinamis (JSONB) | 5 |
| **articles** | Dokumen penelitian yang diunggah + hasil analisis AI | 14 |
| **folders** | Pengelompokan logis artikel + cache konteks AI | 6 |
| **article_folder** | Tabel pivot many-to-many antara articles dan folders | 5 |
| **chat_histories** | Riwayat percakapan AI (per-artikel, per-folder, atau global) | 8 |

### **Penjelasan Relasi**

| Relasi | Tipe | Keterangan |
|--------|------|------------|
| users → kti_types | One-to-Many | Satu user memiliki banyak template KTI |
| users → articles | One-to-Many | Satu user memiliki banyak artikel |
| users → folders | One-to-Many | Satu user memiliki banyak folder |
| users → chat_histories | One-to-Many | Satu user memiliki banyak riwayat chat |
| kti_types → articles | One-to-Many | Satu template digunakan oleh banyak artikel |
| articles ↔ folders | Many-to-Many | Satu artikel bisa di banyak folder, satu folder bisa berisi banyak artikel (via `article_folder`) |
| articles → chat_histories | One-to-Many | Satu artikel bisa punya banyak riwayat chat (nullable — null berarti global/folder chat) |
| folders → chat_histories | One-to-Many | Satu folder bisa punya banyak riwayat chat (nullable — null berarti global/article chat) |

### **Aturan Cascade (Foreign Key)**

| FK | On Delete | Penjelasan |
|----|-----------|------------|
| `kti_types.user_id` → users | CASCADE | Hapus user → hapus semua template |
| `articles.user_id` → users | CASCADE | Hapus user → hapus semua artikel |
| `articles.kti_type_id` → kti_types | CASCADE | Hapus template → hapus artikel terkait |
| `folders.user_id` → users | CASCADE | Hapus user → hapus semua folder |
| `article_folder.article_id` → articles | CASCADE | Hapus artikel → hapus dari semua folder |
| `article_folder.folder_id` → folders | CASCADE | Hapus folder → hapus semua relasi pivot |
| `chat_histories.user_id` → users | — | Tidak cascade (user jarang dihapus) |
| `chat_histories.article_id` → articles | CASCADE | Hapus artikel → hapus chat terkait |
| `chat_histories.folder_id` → folders | CASCADE | Hapus folder → hapus chat folder terkait |

### **Tipe Chat (Berdasarkan Kolom Nullable)**

| article_id | folder_id | Tipe Chat |
|:----------:|:---------:|-----------|
| NOT NULL | NULL | Chat per-artikel (konteks: 1 artikel) |
| NULL | NOT NULL | Chat folder (konteks: semua artikel dalam folder) |
| NULL | NULL | Chat global (konteks: 3 artikel paling relevan via keyword search) |

### **Kolom JSONB (Fleksibel)**

| Tabel | Kolom | Isi |
|-------|-------|-----|
| kti_types | `columns` | Array nama kolom analisis, misal: `["Judul", "Penulis", "Jurnal Publikasi"]` |
| articles | `analysis_results` | Hasil lengkap ekstraksi AI dalam format key-value, misal: `{"Judul": "...", "abstract": "...", "keywords": [...]}` |
| articles | `keywords` | Array 5 kata kunci, misal: `["NLP", "klasifikasi", "deep learning", "BERT", "sentimen"]` |
| chat_histories | `metadata` | Info tambahan chat, misal: `{"model": "gemini-2.5-flash", "folder_name": "...", "sources": [...]}` |

### **Index Database**

| Tabel | Index | Kolom | Tujuan |
|-------|-------|-------|--------|
| article_folder | UNIQUE | `[article_id, folder_id]` | Mencegah duplikasi relasi |
| chat_histories | INDEX | `[user_id, article_id]` | Query cepat riwayat chat per user/artikel |
