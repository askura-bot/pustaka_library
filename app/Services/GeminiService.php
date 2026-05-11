<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Universal mandatory keys that must always be extracted regardless of KTI type.
     */
    private const UNIVERSAL_KEYS = ['abstract', 'so_what', 'conclusion'];

    /**
     * Metadata keys always extracted for citation purposes.
     */
    private const METADATA_KEYS = ['title', 'author', 'year'];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Mengunggah file ke Gemini File API.
     */
    public function uploadFile(string $filePath, string $mimeType = 'application/pdf'): ?string
    {
        $url = "https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}";

        $fileContent = file_get_contents($filePath);

        $response = Http::withBody($fileContent, $mimeType)
            ->post($url);

        if ($response->failed()) {
            Log::error('Gemini Upload Error: '.$response->body());
            throw new \Exception('Gagal mengunggah file ke AI Gemini.');
        }

        $data = $response->json();

        return $data['file']['uri'] ?? null;
    }

    /**
     * Menganalisis dokumen menggunakan Gemini 3 Flash.
     *
     * @param  array<int, string>  $columns  Template columns from kti_types
     * @param  string  $ktiTypeName  Name of the KTI type (e.g. "Article")
     * @return array<string, mixed>|null
     */
    public function analyzeDocument(string $fileUri, string $mimeType, array $columns, string $ktiTypeName = ''): ?array
    {
        $url = "{$this->baseUrl}/models/gemini-3-flash-preview:generateContent?key={$this->apiKey}";

        $prompt = $this->buildPrompt($columns, $ktiTypeName);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'fileData' => [
                                'mimeType' => $mimeType,
                                'fileUri' => $fileUri,
                            ],
                        ],
                        [
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.2,
            ],
        ];

        $response = Http::timeout(300)->post($url, $payload);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini Generate Error: '.$response->body());
            throw new \Exception('Gagal menganalisis dokumen dengan AI Gemini.');
        }

        $data = $response->json();
        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        // Bersihkan hasil jika masih terdapat backticks dari markdown
        $textResult = str_replace(['```json', '```'], '', $textResult);

        return json_decode(trim($textResult), true);
    }

    /**
     * Build the extraction prompt based on KTI type.
     *
     * @param  array<int, string>  $columns
     */
    protected function buildPrompt(array $columns, string $ktiTypeName): string
    {
        $isArticle = strtolower(trim($ktiTypeName)) === 'article';

        // Always required: metadata keys
        $metadataDescription = implode(', ', array_map(
            fn (string $key) => match ($key) {
                'title' => "'title' (judul asli dokumen dalam bahasa aslinya)",
                'author' => "'author' (nama lengkap semua penulis, dipisahkan koma)",
                'year' => "'year' (tahun publikasi, hanya angka 4 digit)",
                default => "'$key'",
            },
            self::METADATA_KEYS
        ));

        // Template-specific columns
        $templateColumnsList = implode(', ', array_map(fn ($col) => "'$col'", $columns));

        // Universal mandatory keys
        $universalDescription = implode(', ', array_map(
            fn (string $key) => match ($key) {
                'abstract' => "'abstract' (ringkasan isi dokumen dalam Bahasa Indonesia, 2-4 kalimat)",
                'so_what' => "'so_what' (esensi atau makna penting penelitian ini — mengapa ini relevan?)",
                'conclusion' => "'conclusion' (kesimpulan utama dari dokumen dalam Bahasa Indonesia)",
                default => "'$key'",
            },
            self::UNIVERSAL_KEYS
        ));

        if ($isArticle) {
            // Specific prompt for "Article" type — 8 points total
            return 'Analisis dokumen jurnal/artikel ilmiah terlampir. '.
                   'Berikan output HANYA dalam format JSON valid. '.
                   'Ekstrak tepat 8 poin informasi berikut: '.
                   "METADATA: {$metadataDescription}. ".
                   "KOLOM SPESIFIK ARTIKEL: {$templateColumnsList} (isi sesuai data yang ditemukan di dokumen). ".
                   "ANALISIS UMUM: {$universalDescription}. ".
                   'Catatan: Untuk kolom spesifik artikel, gunakan Bahasa Indonesia kecuali untuk judul, nama penulis, nama jurnal, dan DOI yang harus tetap dalam bahasa aslinya. '.
                   'Jangan tambahkan awalan ```json atau akhiran apapun, berikan JSON mentah saja.';
        }

        // Generic prompt for any other KTI type
        return 'Analisis dokumen terlampir. '.
               'Berikan output HANYA dalam format JSON valid menggunakan Bahasa Indonesia. '.
               'Ekstrak informasi berikut: '.
               "METADATA WAJIB: {$metadataDescription}. ".
               "KOLOM TEMPLATE: {$templateColumnsList} (isi sesuai data yang ditemukan di dokumen). ".
               "ANALISIS WAJIB: {$universalDescription}. ".
               'Catatan: Semua jawaban dalam Bahasa Indonesia kecuali judul asli, nama penulis, dan istilah teknis. '.
               'Jangan tambahkan awalan ```json atau akhiran apapun, berikan JSON mentah saja.';
    }

    /**
     * Menghapus file dari server Google setelah analisis selesai.
     */
    public function deleteFile(string $fileUri): void
    {
        try {
            $url = "{$fileUri}?key={$this->apiKey}";
            Http::delete($url);
        } catch (\Exception $e) {
            Log::warning('Gagal menghapus file dari Gemini: '.$e->getMessage());
        }
    }

    /**
     * Generate formatted citation and bibliography from analysis JSON data.
     *
     * Sends the extracted metadata to Gemini (no file upload needed) and asks
     * it to produce a properly formatted in-text citation and bibliography entry.
     *
     * @param  array<string, mixed>  $jsonData  The analysis_results data
     * @param  string  $style  Citation style (apa, mla, ieee, harvard)
     * @return array{citation: string, bibliography: string}
     */
    public function generateReference(array $jsonData, string $style = 'apa'): array
    {
        $url = "{$this->baseUrl}/models/gemini-3-flash-preview:generateContent?key={$this->apiKey}";

        $styleName = strtoupper($style);
        $jsonString = json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = "Kamu adalah ahli penulisan akademik. Berdasarkan data JSON berikut, buatkan:\n".
                  "1. In-text Citation (kutipan singkat untuk di dalam paragraf) sesuai format {$styleName}.\n".
                  "2. Bibliography/References (entri lengkap untuk daftar pustaka) sesuai format {$styleName}.\n\n".
                  "Data JSON:\n{$jsonString}\n\n".
                  "Berikan output HANYA dalam format JSON valid dengan dua kunci:\n".
                  "- \"citation\": string berisi in-text citation\n".
                  "- \"bibliography\": string berisi bibliography entry lengkap\n\n".
                  "Aturan:\n".
                  "- Gunakan data title, author, year, jurnal publikasi, link DOI jika tersedia.\n".
                  "- Jika data tidak lengkap, gunakan placeholder [Data Tidak Tersedia].\n".
                  "- Format harus sesuai standar {$styleName} yang benar dan akademis.\n".
                  '- Jangan tambahkan awalan ```json atau akhiran apapun.';

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.1,
            ],
        ];

        $response = Http::timeout(60)->post($url, $payload);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini Reference Error: '.$response->body());
            throw new \Exception('Gagal menghasilkan referensi dari AI Gemini.');
        }

        $data = $response->json();
        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $textResult = str_replace(['```json', '```'], '', $textResult);

        $result = json_decode(trim($textResult), true);

        return [
            'citation' => $result['citation'] ?? '',
            'bibliography' => $result['bibliography'] ?? '',
        ];
    }
}
