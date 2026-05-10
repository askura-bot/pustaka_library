Fase 1: Fondasi UI & Autentikasi
Setup Theme Neubrutalism: Konfigurasi tailwind.config.js untuk border, hard shadow, dan palet warna neon. Buat Layout utama (App Shell).

Halaman Home (Landing Page): Implementasikan desain Landing Page agar Anda memiliki "wajah" aplikasi sejak awal.

Sistem Autentikasi: Pasang Laravel Socialite untuk Google Sign-In dan form login standar. Pastikan Auth Middleware sudah bekerja untuk memproteksi halaman Library.

Fase 2: Master Data & Template Dinamis
Template Manager (KTI Types): Buat fitur CRUD untuk Jenis KTI dan pengelola kolom dinamis (JSONB). Fitur ini wajib ada sebelum fitur upload, karena analisis AI bergantung pada template yang dipilih user.

Fase 3: Core Library & File Ingest
Dashboard Library: Buat tampilan daftar artikel (Grid/Card) menggunakan Livewire.

Fitur Upload (Basic): Implementasikan Drag & Drop upload, simpan file ke Private Storage, dan buat record di database (tanpa proses AI dulu).

Fitur Hapus: Pastikan penghapusan file fisik dan database berjalan sinkron.

Fase 4: Integrasi AI Gemini 3.1 Pro
Gemini Service & Queue: Buat class GeminiService dan implementasikan Laravel Queues. Tambahkan logika Retry/Backoff untuk menangani Rate Limit Free Tier.

Dynamic Extraction: Hubungkan proses upload dengan Gemini. Pastikan Gemini berhasil mengekstraksi data dalam Bahasa Indonesia sesuai kolom dari Template KTI yang dipilih.

Fase 5: Halaman Detail & Penampil PDF
Split-Screen UI: Bangun halaman detail dengan Document Viewer di sisi kiri dan panel analisis di sisi kanan.

Tabel Analisis Dinamis: Buat komponen Livewire yang merender tabel berdasarkan kolom yang dipilih user secara otomatis.

Generator Sitasi: Implementasikan dropdown format sitasi dan fitur salin teks.

Fase 6: Chatbot & Fitur Lanjutan
Persistent Chat (Database): Buat sistem chat yang tersimpan di database PostgreSQL baik untuk chat per artikel maupun chat global.

Semantic Search (pgvector): Implementasikan pencarian berdasarkan makna agar riset lebih canggih.

Polishing & Deployment: Optimasi desain Neubrutalism di semua halaman, pasang Dark Mode, dan deploy ke Google Cloud Run.