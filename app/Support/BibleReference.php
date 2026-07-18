<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Translates human Bible references ("1 Chronicles 24", "Psalm 1:1-3") into
 * API.Bible passage identifiers ("1CH.24", "PSA.1.1-PSA.1.3").
 */
final class BibleReference
{
    /**
     * Book name (and common abbreviations) => API.Bible book id.
     *
     * @var array<string, string>
     */
    private const BOOKS = [
        'genesis' => 'GEN', 'gen' => 'GEN',
        'exodus' => 'EXO', 'exo' => 'EXO', 'ex' => 'EXO',
        'leviticus' => 'LEV', 'lev' => 'LEV',
        'numbers' => 'NUM', 'num' => 'NUM',
        'deuteronomy' => 'DEU', 'deut' => 'DEU', 'deu' => 'DEU',
        'joshua' => 'JOS', 'josh' => 'JOS',
        'judges' => 'JDG', 'judg' => 'JDG',
        'ruth' => 'RUT',
        '1 samuel' => '1SA', '1samuel' => '1SA', '1 sam' => '1SA', '1sam' => '1SA',
        '2 samuel' => '2SA', '2samuel' => '2SA', '2 sam' => '2SA', '2sam' => '2SA',
        '1 kings' => '1KI', '1kings' => '1KI', '1 kgs' => '1KI',
        '2 kings' => '2KI', '2kings' => '2KI', '2 kgs' => '2KI',
        '1 chronicles' => '1CH', '1chronicles' => '1CH', '1 chron' => '1CH', '1 chr' => '1CH',
        '2 chronicles' => '2CH', '2chronicles' => '2CH', '2 chron' => '2CH', '2 chr' => '2CH',
        'ezra' => 'EZR',
        'nehemiah' => 'NEH', 'neh' => 'NEH',
        'esther' => 'EST', 'esth' => 'EST',
        'job' => 'JOB',
        'psalm' => 'PSA', 'psalms' => 'PSA', 'ps' => 'PSA', 'psa' => 'PSA',
        'proverbs' => 'PRO', 'prov' => 'PRO', 'pro' => 'PRO',
        'ecclesiastes' => 'ECC', 'eccl' => 'ECC',
        'song of solomon' => 'SNG', 'song of songs' => 'SNG', 'song' => 'SNG',
        'isaiah' => 'ISA', 'isa' => 'ISA',
        'jeremiah' => 'JER', 'jer' => 'JER',
        'lamentations' => 'LAM', 'lam' => 'LAM',
        'ezekiel' => 'EZK', 'ezek' => 'EZK',
        'daniel' => 'DAN', 'dan' => 'DAN',
        'hosea' => 'HOS', 'hos' => 'HOS',
        'joel' => 'JOL',
        'amos' => 'AMO',
        'obadiah' => 'OBA', 'obad' => 'OBA',
        'jonah' => 'JON',
        'micah' => 'MIC', 'mic' => 'MIC',
        'nahum' => 'NAM',
        'habakkuk' => 'HAB', 'hab' => 'HAB',
        'zephaniah' => 'ZEP', 'zeph' => 'ZEP',
        'haggai' => 'HAG', 'hag' => 'HAG',
        'zechariah' => 'ZEC', 'zech' => 'ZEC',
        'malachi' => 'MAL', 'mal' => 'MAL',
        'matthew' => 'MAT', 'matt' => 'MAT', 'mat' => 'MAT',
        'mark' => 'MRK', 'mrk' => 'MRK',
        'luke' => 'LUK', 'luk' => 'LUK',
        'john' => 'JHN', 'jhn' => 'JHN',
        'acts' => 'ACT',
        'romans' => 'ROM', 'rom' => 'ROM',
        '1 corinthians' => '1CO', '1corinthians' => '1CO', '1 cor' => '1CO', '1cor' => '1CO',
        '2 corinthians' => '2CO', '2corinthians' => '2CO', '2 cor' => '2CO', '2cor' => '2CO',
        'galatians' => 'GAL', 'gal' => 'GAL',
        'ephesians' => 'EPH', 'eph' => 'EPH',
        'philippians' => 'PHP', 'phil' => 'PHP',
        'colossians' => 'COL', 'col' => 'COL',
        '1 thessalonians' => '1TH', '1 thess' => '1TH',
        '2 thessalonians' => '2TH', '2 thess' => '2TH',
        '1 timothy' => '1TI', '1 tim' => '1TI',
        '2 timothy' => '2TI', '2 tim' => '2TI',
        'titus' => 'TIT',
        'philemon' => 'PHM', 'phlm' => 'PHM',
        'hebrews' => 'HEB', 'heb' => 'HEB',
        'james' => 'JAS', 'jas' => 'JAS',
        '1 peter' => '1PE', '1 pet' => '1PE',
        '2 peter' => '2PE', '2 pet' => '2PE',
        '1 john' => '1JN', '1john' => '1JN',
        '2 john' => '2JN', '2john' => '2JN',
        '3 john' => '3JN', '3john' => '3JN',
        'jude' => 'JUD',
        'revelation' => 'REV', 'rev' => 'REV',
    ];

    /**
     * Convert a reference to an API.Bible passage id.
     *
     * Covers every shape in the church's One Year Bible plan:
     *   "1 CHRONICLES 24"                  whole chapter
     *   "PROVERBS 10:5"                    single verse
     *   "PSALM 3:1-8"                      verse range
     *   "GENESIS 1:1-2:25"                 chapter span
     *   "LEVITICUS 27:14-NUMBERS 1:1-54"   book span
     *
     * Returns null when the book is unrecognised.
     */
    public static function toPassageId(string $reference): ?string
    {
        $normalised = trim(preg_replace('/\s+/', ' ', $reference) ?? '');

        if ($normalised === '') {
            return null;
        }

        // A span across two books is any hyphen whose left and right sides both
        // parse as "book + range". Trying each hyphen keeps this working whether
        // the file writes "LEVITICUS 27:14-NUMBERS 1:1-54" or
        // "DEUTERONOMY 34:1-12 - JOSHUA 1:1-2:24".
        foreach (self::hyphenPositions($normalised) as $position) {
            $left = self::parseSegment(trim(substr($normalised, 0, $position)));
            $right = self::parseSegment(trim(substr($normalised, $position + 1)));

            if ($left !== null && $right !== null) {
                return sprintf(
                    '%s.%s%s-%s.%s%s',
                    $left['book'], $left['start_chapter'],
                    $left['start_verse'] !== null ? '.'.$left['start_verse'] : '',
                    $right['book'], $right['end_chapter'],
                    $right['end_verse'] !== null ? '.'.$right['end_verse'] : '',
                );
            }
        }

        $segment = self::parseSegment($normalised);

        if ($segment === null) {
            return null;
        }

        // Whole chapter.
        if ($segment['start_verse'] === null) {
            return $segment['book'].'.'.$segment['start_chapter'];
        }

        $start = sprintf('%s.%s.%s', $segment['book'], $segment['start_chapter'], $segment['start_verse']);

        // Single verse.
        if ($segment['start_chapter'] === $segment['end_chapter']
            && $segment['start_verse'] === $segment['end_verse']) {
            return $start;
        }

        return $start.sprintf('-%s.%s.%s', $segment['book'], $segment['end_chapter'], $segment['end_verse']);
    }

    /**
     * Byte offsets of hyphens that could separate two books.
     *
     * @return list<int>
     */
    private static function hyphenPositions(string $reference): array
    {
        $positions = [];
        $offset = 0;

        while (($position = strpos($reference, '-', $offset)) !== false) {
            $positions[] = $position;
            $offset = $position + 1;
        }

        return $positions;
    }

    /**
     * Parse "BOOK 1:1-2:25" style segments into their parts.
     *
     * @return array{book: string, start_chapter: string, start_verse: ?string, end_chapter: string, end_verse: ?string}|null
     */
    private static function parseSegment(string $segment): ?array
    {
        $segment = trim($segment);

        if ($segment === '') {
            return null;
        }

        // BOOK c:v-c:v
        if (preg_match('/^(.+?)\s+(\d+):(\d+)\s*-\s*(\d+):(\d+)$/u', $segment, $m)) {
            $book = self::bookId($m[1]);

            return $book === null ? null : [
                'book' => $book,
                'start_chapter' => $m[2], 'start_verse' => $m[3],
                'end_chapter' => $m[4], 'end_verse' => $m[5],
            ];
        }

        // BOOK c:v-v
        if (preg_match('/^(.+?)\s+(\d+):(\d+)\s*-\s*(\d+)$/u', $segment, $m)) {
            $book = self::bookId($m[1]);

            return $book === null ? null : [
                'book' => $book,
                'start_chapter' => $m[2], 'start_verse' => $m[3],
                'end_chapter' => $m[2], 'end_verse' => $m[4],
            ];
        }

        // BOOK c:v
        if (preg_match('/^(.+?)\s+(\d+):(\d+)$/u', $segment, $m)) {
            $book = self::bookId($m[1]);

            return $book === null ? null : [
                'book' => $book,
                'start_chapter' => $m[2], 'start_verse' => $m[3],
                'end_chapter' => $m[2], 'end_verse' => $m[3],
            ];
        }

        // BOOK c
        if (preg_match('/^(.+?)\s+(\d+)$/u', $segment, $m)) {
            $book = self::bookId($m[1]);

            return $book === null ? null : [
                'book' => $book,
                'start_chapter' => $m[2], 'start_verse' => null,
                'end_chapter' => $m[2], 'end_verse' => null,
            ];
        }

        return null;
    }

    /**
     * Resolve a book name or abbreviation to its API.Bible id.
     */
    public static function bookId(string $book): ?string
    {
        $key = mb_strtolower(trim(str_replace('.', '', $book)));

        if ($key === '') {
            return null;
        }

        // "1st Samuel" / "Second Kings" style prefixes.
        $key = preg_replace('/^(1st|first)\s+/', '1 ', $key) ?? $key;
        $key = preg_replace('/^(2nd|second)\s+/', '2 ', $key) ?? $key;
        $key = preg_replace('/^(3rd|third)\s+/', '3 ', $key) ?? $key;

        if (isset(self::BOOKS[$key])) {
            return self::BOOKS[$key];
        }

        // "1THESSALONIANS" — numbered book written without a separating space.
        if (preg_match('/^([1-3])\s*([a-z]+)$/', $key, $m)) {
            return self::BOOKS[$m[1].' '.$m[2]] ?? null;
        }

        return null;
    }

    /**
     * Build a reference from stored parts, e.g. ("Psalm", 1, "1-3").
     */
    public static function fromParts(?string $book, ?int $chapter, ?string $verses): ?string
    {
        if (blank($book) || $chapter === null) {
            return null;
        }

        return blank($verses)
            ? $book.' '.$chapter
            : $book.' '.$chapter.':'.$verses;
    }
}
