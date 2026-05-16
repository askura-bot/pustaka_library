# 📚 AI-Powered Research Command Center

Aplikasi perpustakaan digital pribadi yang membantu mahasiswa mengelola, menganalisis, dan memahami Karya Tulis Ilmiah (KTI) secara instan menggunakan kekuatan **Gemini AI**.

## ✨ Fitur Utama

- **AI Document Analysis** — Upload PDF/DOCX, AI mengekstrak abstrak, metadata, kata kunci, dan insight secara otomatis
- **Smart Search** — Pencarian keyword-based yang mencari di judul, penulis, kata kunci, dan isi analisis dengan ranking
- **Citation Generator** — Generate sitasi (APA, MLA, IEEE, Harvard) dari data analisis via AI
- **Per-Article Chat** — Tanya jawab AI tentang isi dokumen tertentu
- **Global Chat (Ask AI)** — Tanya jawab AI lintas seluruh koleksi pustaka dengan source attribution
- **Folder System** — Kelompokkan artikel ke dalam folder dengan chatbot AI khusus per-folder
- **Custom Template** — Buat template analisis sendiri dengan kolom dinamis
- **Google Sign-In** — Login cepat via OAuth + set password flow

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Livewire 4, Tailwind CSS v4, Flux UI v2 |
| Database | PostgreSQL |
| AI | Google Gemini API (configurable via .env) |
| Auth | Laravel Fortify + Socialite |
| Queue | Laravel Queues (Database driver) |
| Style | Neubrutalism (Light Mode Only) |

## 📋 Prasyarat

- PHP 8.3+
- Composer
- Node.js 18+ & npm
- PostgreSQL 14+
- Google Cloud Console (untuk OAuth credentials)
- Gemini API Key (free tier)

## 🚀 Instalasi & Setup

### 1. Clone Repository

```bash
git clone https://github.com/askura-bot/pustaka_library.git
cd pustaka_library
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan isi konfigurasi berikut:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pustaka_library
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# Gemini AI
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL_ANALYSIS=gemini-3-flash-preview
GEMINI_MODEL_REFERENCE=gemini-3-flash-preview
GEMINI_MODEL_CHAT=gemini-2.5-flash
GEMINI_MODEL_GLOBAL_CHAT=gemini-2.5-flash
GEMINI_MODEL_FOLDER_CHAT=gemini-2.5-flash
```

### 4. Setup Database

```bash
php artisan migrate
```

### 5. Build Frontend

```bash
npm run build
```

### 6. Jalankan Aplikasi

**Opsi A — Menggunakan `composer run dev` (recommended):**

```bash
composer run dev
```

Ini akan menjalankan server Laravel, queue worker, dan Vite dev server secara bersamaan.

**Opsi B — Manual (jalankan di terminal terpisah):**

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker (untuk AI analysis)
php artisan queue:listen --tries=1

# Terminal 3: Vite dev server (hot reload)
npm run dev
```

### 7. Akses Aplikasi

Buka browser: [http://localhost:8000](http://localhost:8000)

## 📁 Struktur Penting

```
app/
├── Jobs/
│   ├── AnalyzeArticleJob.php      # AI document analysis
│   └── SyncFolderContextJob.php   # Folder context sync
├── Livewire/
│   ├── LibraryDashboard.php       # Dashboard + folder management
│   ├── ArticleDetail.php          # Detail artikel + chat
│   ├── FolderView.php             # Folder view + upload + chat
│   ├── GlobalChat.php             # Ask AI global
│   └── KtiTypeManager.php         # Template manager
├── Models/
│   ├── Article.php, Folder.php, KtiType.php, ChatHistory.php, User.php
└── Services/
    ├── GeminiService.php          # Semua interaksi Gemini API
    ├── CitationFormatter.php      # Local citation formatting
    └── FolderBrainService.php     # Folder AI context builder
```

## 🔑 Catatan Penting

- **Queue Worker wajib berjalan** agar analisis AI bisa diproses di background
- **Gemini API Key** bisa didapat gratis di [Google AI Studio](https://aistudio.google.com/apikey)
- **Google OAuth** memerlukan setup di [Google Cloud Console](https://console.cloud.google.com/) → APIs & Services → Credentials
- **Timezone** default: `Asia/Jakarta` (WIB)
- **File storage** menggunakan private disk — file tidak bisa diakses langsung via URL

## 📄 Lisensi

MIT License
