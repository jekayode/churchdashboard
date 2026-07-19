<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\BibleReference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Scripture text from API.Bible.
 *
 * Scripture never changes, so responses are cached hard. Failures degrade
 * gracefully to null: a Bible API outage must not break sermon or reading
 * screens, which still have their references to show.
 */
final class BibleService
{
    /**
     * Fetch a passage by human reference, e.g. "Psalm 1:1-3".
     *
     * @return array{reference: string, content: string, copyright: string, translation: string}|null
     */
    public function passage(string $reference, ?string $translation = null): ?array
    {
        $passageId = BibleReference::toPassageId($reference);

        if ($passageId === null) {
            return null;
        }

        return $this->passageById($passageId, $translation);
    }

    /**
     * @return array{reference: string, content: string, copyright: string, translation: string}|null
     */
    public function passageById(string $passageId, ?string $translation = null): ?array
    {
        $bibleId = $this->resolveBibleId($translation);

        if (blank(config('bible.key')) || blank($bibleId)) {
            return null;
        }

        $cacheKey = sprintf('bible:passage:%s:%s', $bibleId, $passageId);

        return Cache::remember($cacheKey, (int) config('bible.cache_ttl'), function () use ($bibleId, $passageId, $translation): ?array {
            try {
                $response = Http::withHeaders(['api-key' => config('bible.key')])
                    ->timeout((int) config('bible.timeout', 8))
                    ->get(sprintf('%s/v1/bibles/%s/passages/%s', rtrim((string) config('bible.url'), '/'), $bibleId, $passageId), [
                        'content-type' => 'text',
                        'include-verse-numbers' => 'true',
                        'include-titles' => 'false',
                        'include-notes' => 'false',
                        'include-chapter-numbers' => 'false',
                    ]);

                if (! $response->successful()) {
                    Log::warning('Bible passage lookup failed', [
                        'passage' => $passageId,
                        'status' => $response->status(),
                    ]);

                    return null;
                }

                $data = $response->json('data');

                if (! is_array($data)) {
                    return null;
                }

                return [
                    'reference' => (string) ($data['reference'] ?? ''),
                    'content' => trim((string) ($data['content'] ?? '')),
                    // Licensed translations require this notice to be shown.
                    'copyright' => trim((string) ($data['copyright'] ?? '')),
                    'translation' => $translation ?? $this->defaultTranslationCode(),
                ];
            } catch (\Throwable $e) {
                Log::warning('Bible passage lookup errored', [
                    'passage' => $passageId,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * Fetch several references at once, keyed by the original reference.
     *
     * @param  list<string>  $references
     * @return array<string, array{reference: string, content: string, copyright: string, translation: string}|null>
     */
    public function passages(array $references, ?string $translation = null): array
    {
        $results = [];

        foreach ($references as $reference) {
            $results[$reference] = $this->passage($reference, $translation);
        }

        return $results;
    }

    /**
     * Translations members may choose from.
     *
     * @return array<string, array{id: string, name: string}>
     */
    public function translations(): array
    {
        return (array) config('bible.translations', []);
    }

    public function isConfigured(): bool
    {
        return filled(config('bible.key'));
    }

    private function resolveBibleId(?string $translation): ?string
    {
        if ($translation === null) {
            return config('bible.default_bible');
        }

        $translations = $this->translations();
        $code = strtoupper($translation);

        return $translations[$code]['id'] ?? config('bible.default_bible');
    }

    private function defaultTranslationCode(): string
    {
        $defaultId = config('bible.default_bible');

        foreach ($this->translations() as $code => $translation) {
            if (($translation['id'] ?? null) === $defaultId) {
                return (string) $code;
            }
        }

        return 'NLT';
    }
}
