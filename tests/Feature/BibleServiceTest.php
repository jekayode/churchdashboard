<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BibleService;
use App\Support\BibleReference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class BibleServiceTest extends TestCase
{
    private BibleService $bible;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('bible.key', 'test-key');
        config()->set('bible.url', 'https://rest.api.bible');
        config()->set('bible.default_bible', 'd6e14a625393b4da-01');

        Cache::flush();

        $this->bible = app(BibleService::class);
    }

    private function fakeSuccess(): void
    {
        Http::fake([
            'rest.api.bible/*' => Http::response([
                'data' => [
                    'reference' => 'Psalms 1:1-3',
                    'content' => '[1] Oh, the joys of those who do not follow the advice of the wicked...',
                    'copyright' => 'Holy Bible, New Living Translation, copyright © 1996, 2004, 2015',
                ],
            ]),
        ]);
    }

    /**
     * These are the exact formats used in the church's daily WhatsApp post.
     */
    public function test_parses_real_world_references(): void
    {
        $cases = [
            'Psalm 1:1-3' => 'PSA.1.1-PSA.1.3',
            '1 CHRONICLES 24' => '1CH.24',
            '1 CHRONICLES 26:1-11' => '1CH.26.1-1CH.26.11',
            'ROMANS 4:1-12' => 'ROM.4.1-ROM.4.12',
            'PSALM 13:1-6' => 'PSA.13.1-PSA.13.6',
            'PROVERBS 19:15-16' => 'PRO.19.15-PRO.19.16',
            '2 Tim 3:16' => '2TI.3.16',
            'Song of Solomon 2:1' => 'SNG.2.1',
            '1st Samuel 3' => '1SA.3',
        ];

        foreach ($cases as $reference => $expected) {
            $this->assertSame($expected, BibleReference::toPassageId($reference), "Failed parsing: {$reference}");
        }
    }

    /**
     * The One Year Bible plan compresses multi-chapter readings, so these
     * shapes make up roughly a third of the church's 1,460 references.
     */
    public function test_parses_chapter_and_book_spans(): void
    {
        $cases = [
            // Chapter span within one book
            'GENESIS 1:1-2:25' => 'GEN.1.1-GEN.2.25',
            '1 SAMUEL 1:1-2:21' => '1SA.1.1-1SA.2.21',
            '1 CHRONICLES 24:1-26:11' => '1CH.24.1-1CH.26.11',
            // Span across two books; the trailing verse is the true end verse
            'LEVITICUS 27:14-NUMBERS 1:1-54' => 'LEV.27.14-NUM.1.54',
            // Plain ranges must keep working
            '1 SAMUEL 14:1-52' => '1SA.14.1-1SA.14.52',
            'PROVERBS 10:5' => 'PRO.10.5',
        ];

        foreach ($cases as $reference => $expected) {
            $this->assertSame($expected, BibleReference::toPassageId($reference), "Failed parsing: {$reference}");
        }
    }

    public function test_unknown_book_returns_null(): void
    {
        $this->assertNull(BibleReference::toPassageId('Book of Nonsense 3'));
        $this->assertNull(BibleReference::toPassageId(''));
    }

    public function test_fetches_a_passage_with_copyright(): void
    {
        $this->fakeSuccess();

        $passage = $this->bible->passage('Psalm 1:1-3');

        $this->assertNotNull($passage);
        $this->assertSame('Psalms 1:1-3', $passage['reference']);
        $this->assertStringContainsString('Oh, the joys', $passage['content']);
        // Licensed translations require the notice to travel with the text.
        $this->assertStringContainsString('New Living Translation', $passage['copyright']);
        $this->assertSame('NLT', $passage['translation']);
    }

    public function test_results_are_cached_so_repeat_reads_do_not_hit_the_api(): void
    {
        $this->fakeSuccess();

        $this->bible->passage('Psalm 1:1-3');
        $this->bible->passage('Psalm 1:1-3');
        $this->bible->passage('Psalm 1:1-3');

        Http::assertSentCount(1);
    }

    public function test_different_translations_are_cached_separately(): void
    {
        $this->fakeSuccess();

        $this->bible->passage('Psalm 1:1-3', 'NLT');
        $this->bible->passage('Psalm 1:1-3', 'KJV');

        Http::assertSentCount(2);
    }

    public function test_api_failure_degrades_gracefully(): void
    {
        Http::fake(['rest.api.bible/*' => Http::response([], 500)]);

        // The screen still has the reference to show; it must not error.
        $this->assertNull($this->bible->passage('Psalm 1:1-3'));
    }

    public function test_network_error_degrades_gracefully(): void
    {
        Http::fake(function (): void {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        });

        $this->assertNull($this->bible->passage('Psalm 1:1-3'));
    }

    public function test_returns_null_when_no_api_key_is_configured(): void
    {
        config()->set('bible.key', null);
        Http::fake();

        $this->assertNull($this->bible->passage('Psalm 1:1-3'));
        $this->assertFalse($this->bible->isConfigured());
        Http::assertNothingSent();
    }

    public function test_invalid_reference_never_calls_the_api(): void
    {
        Http::fake();

        $this->assertNull($this->bible->passage('Book of Nonsense 3'));
        Http::assertNothingSent();
    }

    public function test_can_fetch_several_references_at_once(): void
    {
        $this->fakeSuccess();

        $results = $this->bible->passages(['Psalm 1:1-3', 'ROMANS 4:1-12']);

        $this->assertCount(2, $results);
        $this->assertNotNull($results['Psalm 1:1-3']);
        $this->assertNotNull($results['ROMANS 4:1-12']);
    }

    public function test_builds_reference_from_stored_parts(): void
    {
        $this->assertSame('Psalm 1:1-3', BibleReference::fromParts('Psalm', 1, '1-3'));
        $this->assertSame('1 Chronicles 24', BibleReference::fromParts('1 Chronicles', 24, null));
        $this->assertNull(BibleReference::fromParts(null, 1, '1-3'));
    }
}
