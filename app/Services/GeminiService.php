<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    protected string $modelAnalysis;

    protected string $modelReference;

    protected string $modelChat;

    protected string $modelGlobalChat;

    protected string $modelFolderChat;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->modelAnalysis = config('services.gemini.model_analysis', 'gemini-3-flash-preview');
        $this->modelReference = config('services.gemini.model_reference', 'gemini-3-flash-preview');
        $this->modelChat = config('services.gemini.model_chat', 'gemini-2.5-flash');
        $this->modelGlobalChat = config('services.gemini.model_global_chat', 'gemini-2.5-flash');
        $this->modelFolderChat = config('services.gemini.model_folder_chat', 'gemini-2.5-flash');
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
     * Menganalisis dokumen menggunakan model analysis.
     *
     * @param  array<int, string>  $columns  Template columns from kti_types
     * @param  string  $ktiTypeName  Name of the KTI type (e.g. "Article")
     * @return array<string, mixed>|null
     */
    public function analyzeDocument(string $fileUri, string $mimeType, array $columns, string $ktiTypeName = ''): ?array
    {
        $url = "{$this->baseUrl}/models/{$this->modelAnalysis}:generateContent?key={$this->apiKey}";

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

        $columnKeys = array_map(fn ($col) => "'{$col}'", $columns);
        $columnsList = implode(', ', $columnKeys);

        if ($isArticle) {
            return 'Analisis dokumen jurnal/artikel ilmiah terlampir. '.
                   'Berikan output HANYA dalam format JSON valid dengan kunci-kunci berikut: '.
                   "{$columnsList}, ".
                   "'abstract' (ringkasan isi dokumen dalam Bahasa Indonesia, 2-4 kalimat), ".
                   "'so_what' (esensi atau makna penting penelitian ini — mengapa ini relevan?), ".
                   "'conclusion' (kesimpulan utama dari dokumen dalam Bahasa Indonesia), ".
                   "'keywords' (array berisi tepat 5 kata kunci campuran Bahasa Indonesia dan Inggris yang menggambarkan isi dokumen). ".
                   'Aturan: Isi setiap kunci sesuai data yang ditemukan di dokumen. '.
                   'Untuk judul, nama penulis, nama jurnal, dan DOI gunakan bahasa aslinya. Sisanya dalam Bahasa Indonesia. '.
                   'Jangan tambahkan awalan ```json atau akhiran apapun, berikan JSON mentah saja.';
        }

        return 'Analisis dokumen terlampir. '.
               'Berikan output HANYA dalam format JSON valid dengan kunci-kunci berikut: '.
               "{$columnsList}, ".
               "'abstract' (ringkasan isi dokumen dalam Bahasa Indonesia, 2-4 kalimat), ".
               "'so_what' (esensi atau makna penting penelitian ini — mengapa ini relevan?), ".
               "'conclusion' (kesimpulan utama dari dokumen dalam Bahasa Indonesia), ".
               "'keywords' (array berisi tepat 5 kata kunci campuran Bahasa Indonesia dan Inggris yang menggambarkan isi dokumen). ".
               'Aturan: Isi setiap kunci sesuai data yang ditemukan di dokumen. '.
               'Gunakan Bahasa Indonesia kecuali judul asli, nama penulis, dan istilah teknis. '.
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
     * @param  array<string, mixed>  $jsonData  The analysis_results data
     * @param  string  $style  Citation style (apa, mla, ieee, harvard)
     * @return array{citation: string, bibliography: string}
     */
    public function generateReference(array $jsonData, string $style = 'apa'): array
    {
        $url = "{$this->baseUrl}/models/{$this->modelReference}:generateContent?key={$this->apiKey}";

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

    /**
     * Chat with Gemini using article analysis as context.
     *
     * @param  string  $message  User's question
     * @param  array<string, mixed>  $analysisContext  The article's analysis_results
     * @param  array<int, array{message: string, response: string}>  $chatHistory  Previous chat messages for continuity
     */
    public function chatWithContext(string $message, array $analysisContext, array $chatHistory = []): string
    {
        $url = "{$this->baseUrl}/models/{$this->modelChat}:generateContent?key={$this->apiKey}";

        $contextJson = json_encode($analysisContext, JSON_UNESCAPED_UNICODE);

        $contents = [];

        $systemPrompt = 'Kamu adalah asisten riset akademik yang membantu mahasiswa memahami dokumen ilmiah. '.
                        "Berikut adalah data analisis dari sebuah dokumen:\n\n".
                        "{$contextJson}\n\n".
                        'Jawab pertanyaan user berdasarkan data di atas. Gunakan Bahasa Indonesia yang mudah dipahami. '.
                        'Jika informasi tidak tersedia di data, katakan dengan jujur bahwa data tersebut tidak ada dalam analisis.';

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Baik, saya siap membantu kamu memahami dokumen ini. Silakan tanya apa saja!']],
        ];

        $recentHistory = array_slice($chatHistory, -10);
        foreach ($recentHistory as $chat) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $chat['message']]],
            ];
            $contents[] = [
                'role' => 'model',
                'parts' => [['text' => $chat['response']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];

        $response = Http::timeout(60)->post($url, $payload);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini Chat Error: '.$response->body());
            throw new \Exception('Gagal mendapatkan respons dari AI.');
        }

        $data = $response->json();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak bisa menjawab saat ini.';
    }

    /**
     * Global chat across the user's entire library using keyword-retrieved context.
     *
     * @param  string  $message  User's question
     * @param  array<int, array{title: ?string, author: ?string, so_what: ?string, keywords: ?array}>  $relevantArticles  Top matched articles
     * @param  array<int, array{message: string, response: string}>  $chatHistory  Previous chat messages
     */
    public function globalChat(string $message, array $relevantArticles, array $chatHistory = []): string
    {
        $url = "{$this->baseUrl}/models/{$this->modelGlobalChat}:generateContent?key={$this->apiKey}";

        $articlesContext = '';
        foreach ($relevantArticles as $i => $article) {
            $num = $i + 1;
            $title = $article['title'] ?? 'Tanpa Judul';
            $author = $article['author'] ?? 'Penulis tidak diketahui';
            $soWhat = $article['so_what'] ?? 'Tidak tersedia';
            $keywords = is_array($article['keywords'] ?? null) ? implode(', ', $article['keywords']) : 'Tidak tersedia';
            $abstract = $article['abstract'] ?? '';

            $articlesContext .= "--- Artikel {$num} ---\n";
            $articlesContext .= "Judul: {$title}\n";
            $articlesContext .= "Penulis: {$author}\n";
            $articlesContext .= "Kata Kunci: {$keywords}\n";
            $articlesContext .= "Esensi (So What): {$soWhat}\n";
            if ($abstract) {
                $articlesContext .= "Abstrak: {$abstract}\n";
            }
            $articlesContext .= "\n";
        }

        $contents = [];

        $systemPrompt = 'Kamu adalah asisten riset akademik yang membantu mahasiswa menjawab pertanyaan berdasarkan koleksi pustaka mereka. '.
                        "Berikut adalah ringkasan dari artikel-artikel yang paling relevan di pustaka user:\n\n".
                        "{$articlesContext}\n".
                        "Aturan:\n".
                        "- Jawab dalam Bahasa Indonesia yang mudah dipahami.\n".
                        "- WAJIB sebutkan judul artikel yang kamu jadikan referensi dalam jawaban (gunakan format: *Referensi: [Judul Artikel]*).\n".
                        "- Jika tidak ada artikel yang relevan dengan pertanyaan, katakan dengan jujur.\n".
                        '- Berikan jawaban yang informatif dan terstruktur.';

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Baik, saya siap membantu! Saya akan menjawab berdasarkan koleksi pustakamu dan selalu menyebutkan sumber referensinya. Silakan tanya!']],
        ];

        $recentHistory = array_slice($chatHistory, -10);
        foreach ($recentHistory as $chat) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $chat['message']]],
            ];
            $contents[] = [
                'role' => 'model',
                'parts' => [['text' => $chat['response']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];

        $response = Http::timeout(60)->post($url, $payload);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini Global Chat Error: '.$response->body());
            throw new \Exception('Gagal mendapatkan respons dari AI.');
        }

        $data = $response->json();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak bisa menjawab saat ini.';
    }

    /**
     * Chat with Gemini using a folder's cached context.
     * Uses the dedicated folder chat model from config.
     */
    public function folderChat(string $message, string $contextText, string $folderName, array $chatHistory = []): string
    {
        $model = $this->modelFolderChat ?? config('services.gemini.model_folder_chat', 'gemini-2.5-flash');
        $url = "{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}";

        $contents = [];

        $systemPrompt = "Kamu adalah asisten riset akademik untuk folder \"{$folderName}\". ".
                        "Berikut adalah ringkasan dari semua artikel di dalam folder ini:\n\n".
                        "{$contextText}\n\n".
                        "Aturan:\n".
                        "- Jawab dalam Bahasa Indonesia yang mudah dipahami.\n".
                        "- Sebutkan judul artikel yang kamu jadikan referensi.\n".
                        "- Jika informasi tidak tersedia, katakan dengan jujur.\n".
                        '- Berikan jawaban yang informatif dan terstruktur.';

        $contents[] = ['role' => 'user', 'parts' => [['text' => $systemPrompt]]];
        $contents[] = ['role' => 'model', 'parts' => [['text' => "Baik, saya siap membantu! Saya akan menjawab berdasarkan artikel-artikel di folder \"{$folderName}\". Silakan tanya!"]]];

        $recentHistory = array_slice($chatHistory, -10);
        foreach ($recentHistory as $chat) {
            $contents[] = ['role' => 'user', 'parts' => [['text' => $chat['message']]]];
            $contents[] = ['role' => 'model', 'parts' => [['text' => $chat['response']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];

        $response = Http::timeout(60)->post($url, $payload);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini Folder Chat Error: '.$response->body());
            throw new \Exception('Gagal mendapatkan respons dari AI.');
        }

        $data = $response->json();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak bisa menjawab saat ini.';
    }
}
