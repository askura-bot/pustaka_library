Tentu, ini adalah **Product Requirements Document (PRD)** yang sangat lengkap, detail, dan terstruktur untuk proyek "AI-Powered Research Command Center". Dokumen ini dirancang agar siap dieksekusi oleh tim pengembang atau asisten AI builder.

---

# **PRODUCT REQUIREMENTS DOCUMENT (PRD): AI-POWERED RESEARCH COMMAND CENTER**

## **1. PENDAHULUAN & TUJUAN (OVERVIEW & GOALS)**

* **Nama Proyek:** AI-Powered Research Command Center (Personal Research Library).
* **Tujuan:** Membantu mahasiswa (khususnya Gen Z) mengelola, memahami, dan menganalisis tumpukan Karya Tulis Ilmiah (KTI) secara instan menggunakan kekuatan Gemini 3 flash.
* **Masalah yang Diselesaikan:** *Information overload* saat riset, kesulitan memahami jurnal akademik yang kompleks, dan proses pembuatan sitasi/rangkuman yang memakan waktu.
* **Target Market:** Mahasiswa Universitas (Gen Z).

---

## **2. PENGGUNAAN TEKNOLOGI (TECH STACK)**

* **Backend:** Laravel 13 (Latest).
* **Frontend:** Tailwind CSS & Livewire (Reaktivitas tinggi tanpa reload).
* **Database:** PostgreSQL (Wajib menggunakan ekstensi `pgvector` untuk pencarian semantik).
* **AI Engine:** Gemini 3 flash via API Key (Free Tier).
* **Autentikasi:** Laravel Socialite (Google Sign-In) & Standard Auth.
* **Background Jobs:** Laravel Queues (Redis/Database) dengan *Exponential Backoff*.
* **Storage:** Laravel Private Disk (S3/Local) untuk keamanan file PDF.

---

## **3. GAYA UI/UX (VISUAL IDENTITY)**

* **Konsep:** **Neubrutalism**.
* **Karakteristik:**
* **Borders:** Hitam tebal (`border-4`) pada semua elemen.
* **Shadows:** *Hard shadow* hitam padat (`shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]`).
* **Warna:** Warna vibran (Kuning Lemon, Ungu Elektrik, Hijau Neon) dipadukan dengan latar belakang putih atau abu-abu muda.
* **Tipografi:** Font Sans-serif tebal dan berani (Contoh: *Lexend*).
* **Vibe:** Energik, berani, dan tidak membosankan (Gen Z friendly).



---

## **4. STRUKTUR HALAMAN & FITUR DETAIL**

### **Halaman 1: Landing Page (Home)**

* **Fitur:**
* **Hero Section:** Slogan provokatif dan tombol CTA "Mulai Riset Gratis".
* **Feature Grid:** Penjelasan visual fitur (Gemini 3.1 Pro, Custom Template, dll).
* **How-to-Use:** Langkah interaktif menggunakan web (Upload -> Analyze -> Chat).
* **Footer:** Navigasi sosial dan FAQ singkat.


* **Akses:** Terbuka untuk publik (Unauthenticated).

### **Halaman 2: Login & Register**

* **Fitur:**
* **Google Sign-In:** Login cepat satu klik.
* **Standard Login:** Form email & password bergaya neubrutalism.
* **Auth Middleware:** Mencegah akses ke library sebelum login.



### **Halaman 3: Template Manager (Pengaturan Jenis KTI)**

* **Fitur:**
* **CRUD Jenis KTI:** User bisa menambah kategori (misal: Skripsi, Jurnal Ekonomi).
* **Dynamic Column Builder:** User menentukan judul kolom tabel analisis (misal: "Metode", "Hasil", "Kritik").



### **Halaman 4: Library Pustaka (Dashboard)**

* **Fitur:**
* **Article Grid:** Daftar KTI dengan kartu neubrutalist.
* **Upload Modal:** Dropdown pilih Jenis KTI + Drag & drop file PDF/Word.
* **Search Bar:** Pencarian teks dan pencarian makna (Semantic Search).
* **Delete File:** Hapus dokumen (sekaligus menghapus file fisik & riwayat chat).
* **Global Chat ("Ask Across Library"):** Chatbot untuk bertanya ke seluruh koleksi.



### **Halaman 5: Detail Artikel (Research Lab)**

* **Fitur:**
* **Split Screen Layout:** Kiri (PDF Viewer) | Kanan (AI Analysis Hub).
* **AI Analysis Output (Bahasa Indonesia):** Abstrak, Key Takeaways, Tabel Dinamis (sesuai template), Fitur "So What?", dan Kesimpulan.
* **Citation Generator:** Dropdown format (APA, MLA, IEEE, dll) + Button Copy.
* **Persistent Chatbot:** Riwayat chat tersimpan di database dan tidak hilang saat pindah halaman.



---

## **5. STRUKTUR DATABASE (POSTGRESQL)**

* **`users`:** Data autentikasi.
* **`kti_types`:** `id`, `user_id`, `name`, `columns` (JSONB).
* **`articles`:**
* `id`, `user_id`, `kti_type_id`, `file_path`, `title`, `author`, `year`.
* `abstract`, `analysis_results` (JSONB), `so_what`, `conclusion`.
* `embedding` (Vector - untuk `pgvector`).


* **`chat_histories`:**
* `id`, `user_id`, `article_id` (null jika global), `message`, `sender` (user/ai).



---

## **6. ALUR PENGGUNAAN (USER JOURNEY)**

1. **Landing:** User mendarat di Home, melihat fitur, dan klik login.
2. **Auth:** User masuk menggunakan akun Google.
3. **Setup:** User membuat Jenis KTI (misal: "Review Jurnal") dan menentukan kolom analisisnya.
4. **Ingest:** User upload PDF, memilih jenis "Review Jurnal".
5. **Process:** Laravel Queue mengirim file ke Gemini 3 flash. Jika terkena *Rate Limit*, sistem akan mencoba lagi otomatis (*Retry*).
6. **Analysis:** User masuk ke halaman detail, membaca dokumen asli di kiri, dan hasil bedah AI di kanan (Bahasa Indonesia).
7. **Interaction:** User bertanya ke chatbot tentang isi dokumen, jawabannya tersimpan permanen.
8. **Output:** User memilih format APA, klik copy sitasi untuk tugasnya.

---

## **7. PENANGANAN RATE LIMIT & STABILITAS**

* **Exponential Backoff:** Jika API Gemini mengembalikan error 429 (Too Many Requests), sistem akan menunggu (delay) secara bertahap (1m, 2m, 4m) sebelum mencoba lagi.
* **Visual Feedback:** User akan melihat status "Server Sibuk - Mengantre" daripada melihat aplikasi error/crash.

---

**Instruksi untuk AI Builder:**

> *"Gunakan PRD ini sebagai panduan tunggal. Implementasikan gaya Neubrutalism secara ketat pada setiap komponen Tailwind CSS. Gunakan database PostgreSQL dengan skema JSONB untuk fleksibilitas template dan simpan semua riwayat chat ke database untuk persistensi data."*