<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * File-based retrieval with no database and no embeddings.
 *
 * Documents live on disk under a root folder (the "docs library"), organised into
 * sub-folders called collections. A search reads the relevant files, splits them
 * into passages, scores each passage lexically against the query, and returns the
 * best excerpts. Nothing is indexed or persisted.
 */
class FileSearchService
{
    /** @var list<string> */
    private const EXTENSIONS = ['md', 'markdown', 'txt'];

    private const MAX_SNIPPET = 600;

    private const MIN_KEYWORD_LENGTH = 2;

    private const CHUNK_TARGET = 900;

    /** @var list<string> */
    private const STOP_WORDS = [
        'the', 'and', 'for', 'with', 'that', 'this', 'are', 'was', 'were', 'has',
        'have', 'from', 'will', 'can', 'you', 'your', 'our', 'who', 'what', 'when',
        'where', 'how', 'why', 'which', 'into', 'about', 'any', 'all', 'not',
    ];

    public function __construct(private readonly string $rootPath) {}

    /**
     * Search the docs library for passages relevant to the query.
     *
     * @param  list<string>  $collections  Sub-folders to limit to ([] = whole library).
     * @return list<array{collection: string, file: string, snippet: string, score: float}>
     */
    public function search(string $query, array $collections = [], int $limit = 5): array
    {
        $keywords = $this->keywords($query);

        if ($keywords === [] || ! File::isDirectory($this->rootPath)) {
            return [];
        }

        $hits = [];

        foreach ($this->files($collections) as $file) {
            $relative = $this->relativePath($file);
            $collection = str_contains($relative, '/') ? explode('/', $relative)[0] : '(root)';

            foreach ($this->chunk(File::get($file)) as $chunk) {
                $score = $this->score($chunk, $keywords, $query);

                if ($score <= 0) {
                    continue;
                }

                $hits[] = [
                    'collection' => $collection,
                    'file' => $relative,
                    'snippet' => $this->snippet($chunk, $keywords),
                    'score' => $score,
                ];
            }
        }

        usort($hits, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($hits, 0, max(1, $limit));
    }

    /**
     * The collection sub-folders that exist in the library.
     *
     * @return list<string>
     */
    public function collections(): array
    {
        if (! File::isDirectory($this->rootPath)) {
            return [];
        }

        return collect(File::directories($this->rootPath))
            ->map(fn (string $dir): string => basename($dir))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Resolve the set of files to search.
     *
     * @param  list<string>  $collections
     * @return list<string>
     */
    private function files(array $collections): array
    {
        $directories = [];

        if ($collections === []) {
            $directories[] = $this->rootPath;
        } else {
            foreach ($collections as $collection) {
                $safe = $this->sanitizeCollection($collection);
                $path = $safe !== null ? $this->rootPath.DIRECTORY_SEPARATOR.$safe : null;

                if ($path !== null && File::isDirectory($path)) {
                    $directories[] = $path;
                }
            }
        }

        $files = [];

        foreach ($directories as $directory) {
            foreach (File::allFiles($directory) as $file) {
                if (in_array(strtolower($file->getExtension()), self::EXTENSIONS, true)) {
                    $files[$file->getRealPath()] = true;
                }
            }
        }

        return array_keys($files);
    }

    /**
     * Split text into coherent passages, greedily merging short blocks.
     *
     * @return list<string>
     */
    private function chunk(string $content): array
    {
        $blocks = preg_split('/\n\s*\n/', str_replace("\r\n", "\n", $content)) ?: [];
        $chunks = [];
        $buffer = '';

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            if ($buffer === '') {
                $buffer = $block;
            } elseif (strlen($buffer) + strlen($block) + 2 <= self::CHUNK_TARGET) {
                $buffer .= "\n\n".$block;
            } else {
                $chunks[] = $buffer;
                $buffer = $block;
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        $out = [];

        foreach ($chunks as $chunk) {
            if (strlen($chunk) <= 1400) {
                $out[] = $chunk;

                continue;
            }

            foreach (str_split($chunk, 1200) as $piece) {
                $out[] = $piece;
            }
        }

        return $out;
    }

    /**
     * Distinct, lower-cased query keywords (stop-words removed).
     *
     * @return list<string>
     */
    private function keywords(string $query): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query)) ?: [];

        return array_values(array_unique(array_filter(
            $words,
            fn (string $word): bool => mb_strlen($word) >= self::MIN_KEYWORD_LENGTH
                && ! in_array($word, self::STOP_WORDS, true),
        )));
    }

    /**
     * Lexical relevance score: term frequency + distinct-term coverage + phrase bonus.
     *
     * @param  list<string>  $keywords
     */
    private function score(string $chunk, array $keywords, string $query): float
    {
        $haystack = mb_strtolower($chunk);
        $score = 0;
        $coverage = 0;

        foreach ($keywords as $keyword) {
            $count = substr_count($haystack, $keyword);

            if ($count > 0) {
                $coverage++;
                $score += $count;
            }
        }

        if ($coverage === 0) {
            return 0.0;
        }

        $score += $coverage * 3;

        $phrase = trim(mb_strtolower($query));

        if ($phrase !== '' && str_contains($haystack, $phrase)) {
            $score += 6;
        }

        return (float) $score;
    }

    /**
     * A readable excerpt centred on the first keyword hit.
     *
     * @param  list<string>  $keywords
     */
    private function snippet(string $chunk, array $keywords): string
    {
        $lower = mb_strtolower($chunk);
        $position = null;

        foreach ($keywords as $keyword) {
            $found = mb_strpos($lower, $keyword);

            if ($found !== false) {
                $position = $position === null ? $found : min($position, $found);
            }
        }

        $start = max(0, ($position ?? 0) - 120);
        $snippet = trim(mb_substr($chunk, $start, self::MAX_SNIPPET));
        $snippet = preg_replace('/\s+/', ' ', $snippet) ?? $snippet;

        if ($start > 0) {
            $snippet = '…'.$snippet;
        }

        if (mb_strlen($chunk) > $start + self::MAX_SNIPPET) {
            $snippet .= '…';
        }

        return $snippet;
    }

    private function relativePath(string $absolute): string
    {
        $relative = str_replace($this->rootPath.DIRECTORY_SEPARATOR, '', $absolute);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    private function sanitizeCollection(string $collection): ?string
    {
        $clean = trim($collection);

        return preg_match('/^[a-z0-9_-]+$/i', $clean) === 1 ? $clean : null;
    }
}
