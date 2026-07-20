<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ReadingPlan;
use App\Support\BibleReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Imports a year (or any length) of readings from CSV or XLSX.
 *
 * Built against the church's One Year Bible export, whose columns are:
 *   date, month_day, label, old_testament, new_testament, psalm, proverbs,
 *   readings_combined, what_now_1, what_now_2, source_url
 *
 * The XLSX variant of the same data carries title rows before the header and
 * merges the two questions into a single "What Now?" column, so headers are
 * matched by name and the header row is found rather than assumed.
 */
final class ImportReadingPlan extends Command
{
    protected $signature = 'reading-plan:import
        {file : Path to the CSV or XLSX file}
        {--name= : Plan name (defaults to the file name)}
        {--slug= : Plan slug}
        {--annual : Plan repeats every year, keyed by month and day}
        {--branch= : Branch id to scope the plan to (omit for network-wide)}
        {--attribution= : Credit line shown with the plan}
        {--publish : Publish the plan immediately}
        {--default : Mark as the default plan offered to members}
        {--replace : Update the plan of this name in place instead of refusing}
        {--dry-run : Parse and report without writing anything}';

    protected $description = 'Import a reading plan (Bible in a Year or a devotional series) from CSV/XLSX';

    /**
     * Header aliases, so both the CSV and the XLSX export map cleanly.
     *
     * @var array<string, list<string>>
     */
    private const COLUMNS = [
        'date' => ['date'],
        'month_day' => ['month_day', 'monthday'],
        'label' => ['label', 'day'],
        'old_testament' => ['old_testament', 'old testament'],
        'new_testament' => ['new_testament', 'new testament'],
        'psalm' => ['psalm', 'psalms'],
        'proverbs' => ['proverbs', 'proverb'],
        'question_1' => ['what_now_1', 'what now?', 'what now', 'what_now'],
        'question_2' => ['what_now_2'],
        'source_url' => ['source_url', 'source url'],
        'title' => ['title'],
        'focus_verse' => ['focus_verse', 'focus verse'],
        'body' => ['body', 'devotional'],
        'reflection_prompt' => ['reflection_prompt', 'reflection', 'prompt'],
    ];

    public function handle(): int
    {
        $path = (string) $this->argument('file');

        if (! is_readable($path)) {
            $this->error("Cannot read file: {$path}");

            return self::FAILURE;
        }

        $rows = $this->readRows($path);

        if ($rows === []) {
            $this->error('No data rows found. Check the file has a header row.');

            return self::FAILURE;
        }

        [$parsed, $warnings] = $this->parseRows($rows);

        if ($parsed === []) {
            $this->error('No usable rows found.');

            return self::FAILURE;
        }

        $this->info(sprintf('Parsed %d day(s).', count($parsed)));

        foreach ($warnings as $warning) {
            $this->warn('  '.$warning);
        }

        if ($this->option('dry-run')) {
            $this->line('');
            $this->line('Dry run — nothing written. Sample:');
            $this->showSample($parsed[0]);

            return self::SUCCESS;
        }

        $plan = $this->createPlan($parsed);

        $this->info(sprintf('Imported "%s" with %d days (id %d).', $plan->name, $plan->days()->count(), $plan->id));

        if (! $plan->is_published) {
            $this->line('Plan is unpublished — pass --publish, or publish it later, to show it to members.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, string>>
     */
    private function readRows(string $path): array
    {
        $sheets = Excel::toArray([], $path);
        $rows = $sheets[0] ?? [];

        // Find the header row: the first row mentioning a known column.
        $headerIndex = null;
        $header = [];

        foreach ($rows as $index => $row) {
            $cells = array_map(fn ($cell): string => mb_strtolower(trim((string) $cell)), $row);

            if (array_intersect($cells, ['old_testament', 'old testament', 'date'])) {
                $headerIndex = $index;
                $header = $cells;
                break;
            }
        }

        if ($headerIndex === null) {
            return [];
        }

        $map = [];

        foreach (self::COLUMNS as $field => $aliases) {
            foreach ($header as $position => $name) {
                if (in_array($name, $aliases, true)) {
                    $map[$field] = $position;
                    break;
                }
            }
        }

        $data = [];

        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            $record = [];

            foreach ($map as $field => $position) {
                $record[$field] = trim((string) ($row[$position] ?? ''));
            }

            // Skip blank/filler rows.
            if (implode('', $record) !== '') {
                $data[] = $record;
            }
        }

        return $data;
    }

    /**
     * @param  list<array<string, string>>  $rows
     * @return array{0: list<array<string, mixed>>, 1: list<string>}
     */
    private function parseRows(array $rows): array
    {
        $parsed = [];
        $warnings = [];
        $unparseable = 0;
        $dayNumber = 0;

        foreach ($rows as $row) {
            $dayNumber++;

            $monthDay = $row['month_day'] ?? '';

            // Derive month-day from a date column when not given explicitly.
            if ($monthDay === '' && filled($row['date'] ?? '')) {
                $timestamp = strtotime((string) $row['date']);

                if ($timestamp !== false) {
                    $monthDay = date('md', $timestamp);
                }
            }

            foreach (['old_testament', 'new_testament', 'psalm', 'proverbs'] as $field) {
                $reference = $row[$field] ?? '';

                if ($reference !== '' && BibleReference::toPassageId($reference) === null) {
                    $unparseable++;

                    if (count($warnings) < 5) {
                        $warnings[] = "Unrecognised reference (kept as text): {$reference}";
                    }
                }
            }

            $parsed[] = [
                'day_number' => $dayNumber,
                'month_day' => $monthDay !== '' ? str_pad($monthDay, 4, '0', STR_PAD_LEFT) : null,
                'label' => $row['label'] ?? null,
                'old_testament' => $row['old_testament'] ?? null,
                'new_testament' => $row['new_testament'] ?? null,
                'psalm' => $row['psalm'] ?? null,
                'proverbs' => $row['proverbs'] ?? null,
                'title' => $row['title'] ?? null,
                'focus_verse' => $row['focus_verse'] ?? null,
                'body' => $row['body'] ?? null,
                'reflection_prompt' => $row['reflection_prompt'] ?? null,
                'study_question_1' => $row['question_1'] ?? null,
                'study_question_2' => $row['question_2'] ?? null,
                'source_url' => $row['source_url'] ?? null,
            ];
        }

        if ($unparseable > count($warnings)) {
            $warnings[] = sprintf('...and %d more unrecognised reference(s).', $unparseable - count($warnings));
        }

        return [$parsed, $warnings];
    }

    /**
     * Updates a plan in place, matching days on their day number.
     *
     * Emphatically not delete-then-recreate: member_reading_progress is tied to
     * reading_day_id with a cascade, so dropping the days would take every
     * member's reading history and streak with them. Matching on the unique
     * (plan, day_number) key keeps the ids, and the progress attached to them.
     *
     * @param  list<array<string, mixed>>  $parsed
     */
    private function replacePlan(ReadingPlan $plan, array $parsed): ReadingPlan
    {
        return DB::transaction(function () use ($plan, $parsed): ReadingPlan {
            $plan->update(array_filter([
                'is_annual' => (bool) $this->option('annual'),
                'length_days' => count($parsed),
                'is_published' => $this->option('publish') ? true : $plan->is_published,
                'is_default' => $this->option('default') ? true : $plan->is_default,
                'attribution' => $this->option('attribution') ?: $plan->attribution,
            ], fn ($value): bool => $value !== null));

            foreach ($parsed as $day) {
                $plan->days()->updateOrCreate(['day_number' => $day['day_number']], $day);
            }

            return $plan;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $parsed
     */
    private function createPlan(array $parsed): ReadingPlan
    {
        $name = (string) ($this->option('name') ?: pathinfo((string) $this->argument('file'), PATHINFO_FILENAME));

        $existing = ReadingPlan::where('name', $name)
            ->orWhere('slug', $this->option('slug') ?: Str::slug($name))
            ->first();

        /*
         * Slugs are made unique automatically, so without this a second run
         * quietly produces a second plan — "bible-in-a-year-2" — and --default
         * hands it the flag, leaving members reading from a plan nobody meant
         * to publish. Far likelier on a live server than locally, since the
         * obvious response to a half-finished import is to run it again.
         */
        if ($existing !== null && ! $this->option('replace')) {
            $this->error("A plan named \"{$name}\" already exists (id {$existing->id}).");
            $this->line('Use --replace to update it in place, or --name to import alongside it.');

            return $existing;
        }

        if ($existing !== null) {
            return $this->replacePlan($existing, $parsed);
        }

        return DB::transaction(function () use ($parsed, $name): ReadingPlan {
            $plan = ReadingPlan::create([
                'branch_id' => $this->option('branch') ? (int) $this->option('branch') : null,
                'name' => $name,
                'slug' => $this->option('slug') ?: null,
                'type' => ReadingPlan::TYPE_PASSAGES,
                'is_annual' => (bool) $this->option('annual'),
                'length_days' => count($parsed),
                'is_published' => (bool) $this->option('publish'),
                'is_default' => (bool) $this->option('default'),
                'attribution' => $this->option('attribution') ?: null,
                'source_url' => $parsed[0]['source_url'] ?? null,
            ]);

            foreach (array_chunk($parsed, 100) as $chunk) {
                foreach ($chunk as $day) {
                    $plan->days()->create($day);
                }
            }

            // Only one default plan at a time.
            if ($plan->is_default) {
                ReadingPlan::where('id', '!=', $plan->id)->update(['is_default' => false]);
            }

            return $plan;
        });
    }

    /**
     * @param  array<string, mixed>  $day
     */
    private function showSample(array $day): void
    {
        foreach (['label', 'month_day', 'old_testament', 'new_testament', 'psalm', 'proverbs'] as $field) {
            if (filled($day[$field] ?? null)) {
                $this->line(sprintf('  %-16s %s', $field.':', $day[$field]));
            }
        }

        foreach (['study_question_1', 'study_question_2'] as $field) {
            if (filled($day[$field] ?? null)) {
                $this->line(sprintf('  %-16s %s', $field.':', mb_substr((string) $day[$field], 0, 80).'…'));
            }
        }
    }
}
