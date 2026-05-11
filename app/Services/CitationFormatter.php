<?php

namespace App\Services;

use App\Models\Article;

class CitationFormatter
{
    /**
     * Placeholder strings for missing data.
     */
    private const PLACEHOLDER_AUTHOR = '[Nama Penulis]';

    private const PLACEHOLDER_YEAR = '[Tahun]';

    private const PLACEHOLDER_TITLE = '[Judul Artikel]';

    /**
     * Supported citation styles.
     *
     * @var array<string, string>
     */
    public const STYLES = [
        'apa' => 'APA',
        'mla' => 'MLA',
        'ieee' => 'IEEE',
        'harvard' => 'Harvard',
    ];

    /**
     * Extract citation fields from an article, using fallback keys from analysis_results.
     *
     * @return array{author: string, year: string, title: string, hasAuthor: bool, hasYear: bool, hasTitle: bool}
     */
    public static function extractFields(Article $article): array
    {
        $results = $article->analysis_results ?? [];

        $author = $article->author
            ?: self::pickFirst($results, ['author', 'Author', 'penulis', 'Penulis', 'authors', 'Authors']);

        $year = $article->year
            ?: self::pickFirst($results, ['year', 'Year', 'tahun', 'Tahun', 'publication_year', 'Publication Year']);

        $title = $article->title
            ?: self::pickFirst($results, ['title', 'Title', 'judul', 'Judul']);

        return [
            'author' => $author ?: self::PLACEHOLDER_AUTHOR,
            'year' => $year ? (string) $year : self::PLACEHOLDER_YEAR,
            'title' => $title ?: self::PLACEHOLDER_TITLE,
            'hasAuthor' => (bool) $author,
            'hasYear' => (bool) $year,
            'hasTitle' => (bool) $title,
        ];
    }

    /**
     * Generate a short in-text citation (e.g. "Doe, 2024").
     */
    public static function inText(Article $article, string $style = 'apa'): string
    {
        $fields = self::extractFields($article);
        $authorShort = self::shortenAuthor($fields['author']);

        return match (strtolower($style)) {
            'apa' => "({$authorShort}, {$fields['year']})",
            'harvard' => "({$authorShort}, {$fields['year']})",
            'mla' => "({$authorShort} {$fields['year']})",
            'ieee' => self::ieeeInText($article),
            default => "({$authorShort}, {$fields['year']})",
        };
    }

    /**
     * Generate a full bibliography entry for the reference list.
     */
    public static function bibliography(Article $article, string $style = 'apa'): string
    {
        $fields = self::extractFields($article);
        $author = $fields['author'];
        $year = $fields['year'];
        $title = $fields['title'];

        return match (strtolower($style)) {
            'apa' => "{$author} ({$year}). {$title}.",
            'mla' => "{$author}. \"{$title}.\" {$year}.",
            'ieee' => self::ieeeBibliography($article, $fields),
            'harvard' => "{$author} ({$year}) '{$title}'.",
            default => "{$author} ({$year}). {$title}.",
        };
    }

    /**
     * Shorten an author name to surname for in-text citations.
     * "John Doe" -> "Doe", "Doe, J." -> "Doe"
     * Multiple authors: "Doe, Smith, Jones" -> "Doe et al."
     */
    protected static function shortenAuthor(string $author): string
    {
        if ($author === self::PLACEHOLDER_AUTHOR) {
            return $author;
        }

        // Check if multiple authors (comma-separated full names or semicolons)
        $separators = [';', ' dan ', ' and '];
        $isMultiple = false;
        foreach ($separators as $sep) {
            if (str_contains($author, $sep)) {
                $isMultiple = true;
                $author = explode($sep, $author)[0];
                break;
            }
        }

        // If comma-separated and has more than 2 commas, likely multiple authors
        if (! $isMultiple && substr_count($author, ',') >= 2) {
            $isMultiple = true;
            $parts = explode(',', $author);
            $author = trim($parts[0]);
        }

        // Get surname from first author
        $trimmed = trim($author);
        if (str_contains($trimmed, ',')) {
            $surname = trim(explode(',', $trimmed)[0]);
        } else {
            $words = preg_split('/\s+/', $trimmed);
            $surname = end($words) ?: $trimmed;
        }

        return $isMultiple ? "{$surname} et al." : $surname;
    }

    /**
     * IEEE in-text uses reference number [1], but without a list we fallback to author-year.
     */
    protected static function ieeeInText(Article $article): string
    {
        $fields = self::extractFields($article);
        $authorShort = self::shortenAuthor($fields['author']);

        return "[{$authorShort}, {$fields['year']}]";
    }

    /**
     * IEEE full bibliography: "A. Author, "Title," Year."
     */
    protected static function ieeeBibliography(Article $article, array $fields): string
    {
        return "{$fields['author']}, \"{$fields['title']},\" {$fields['year']}.";
    }

    /**
     * Pick the first non-empty scalar value from an array using a list of candidate keys.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    protected static function pickFirst(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if (is_array($value)) {
                $value = implode(', ', array_filter($value, 'is_scalar'));
            }

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }
}
