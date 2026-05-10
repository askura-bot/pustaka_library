<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Mengunggah file ke Gemini File API
     */
    public function uploadFile($filePath, $mimeType = 'application/pdf')
    {
        $url = "https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}";

        $fileContent = file_get_contents($filePath);
        $fileSize = filesize($filePath);

        $response = Http::withBody($fileContent, $mimeType)
            ->post($url);

        if ($response->failed()) {
            Log::error('Gemini Upload Error: ' . $response->body());
            throw new \Exception('Gagal mengunggah file ke AI Gemini.');
        }

        $data = $response->json();
        return $data['file']['uri'] ?? null;
    }

    /**
     * Menganalisis dokumen menggunakan Gemini 3 Flash (Model ringan, limit lebih besar)
     */
    public function analyzeDocument($fileUri, $mimeType, array $columns)
    {
        $url = "{$this->baseUrl}/models/gemini-3-flash-preview:generateContent?key={$this->apiKey}";

        $columnsList = implode(', ', array_map(fn($col) => "'$col'", $columns));

        $prompt = "Analisis dokumen terlampir. Berikan output HANYA dalam format JSON valid menggunakan Bahasa Indonesia. " .
                  "Gunakan tepat kunci-kunci berikut beserta isi jawabannya: " .
                  "$columnsList, 'abstract', 'so_what' (berisi esensi atau makna penting penelitian ini secara singkat), dan 'conclusion'. " .
                  "Penting: Jangan tambahkan awalan ```json atau akhiran apapun, berikan JSON mentah saja.";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'fileData' => [
                                'mimeType' => $mimeType,
                                'fileUri' => $fileUri,
                            ]
                        ],
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.2,
            ]
        ];

        $response = Http::timeout(120)->post($url, $payload);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini Generate Error: ' . $response->body());
            throw new \Exception('Gagal menganalisis dokumen dengan AI Gemini.');
        }

        $data = $response->json();
        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        // Bersihkan hasil jika masih terdapat backticks dari markdown
        $textResult = str_replace(['```json', '```'], '', $textResult);

        return json_decode(trim($textResult), true);
    }

    /**
     * Menghapus file dari server Google setelah analisis selesai
     */
    public function deleteFile($fileUri)
    {
        try {
            // fileUri biasanya "https://generativelanguage.googleapis.com/v1beta/files/abcxyz"
            // Tambahkan API Key sebagai query string
            $url = "{$fileUri}?key={$this->apiKey}";
            Http::delete($url);
        } catch (\Exception $e) {
            Log::warning('Gagal menghapus file dari Gemini: ' . $e->getMessage());
        }
    }
}
