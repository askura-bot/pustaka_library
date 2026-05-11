<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ArticleFileController extends Controller
{
    /**
     * Serve the article file from private storage.
     */
    public function show(Article $article): Response
    {
        if ($article->user_id !== Auth::id()) {
            abort(403);
        }

        $path = storage_path('app/private/'.$article->file_path);

        if (! file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $mimeType = match ($article->file_type) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$article->file_name.'"',
        ]);
    }
}
