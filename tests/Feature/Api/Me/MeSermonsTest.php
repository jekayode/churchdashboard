<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Series;
use App\Models\Sermon;
use App\Models\SermonPassage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeSermonsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Member $member;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->branch = Branch::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('church_member', $this->branch->id);
        $this->member = Member::factory()->create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_guest_cannot_list_sermons(): void
    {
        $this->getJson('/api/me/sermons')->assertUnauthorized();
    }

    public function test_lists_published_sermons_only(): void
    {
        Sermon::factory()->create(['title' => 'Published One']);
        Sermon::factory()->unpublished()->create(['title' => 'Draft One']);

        Sanctum::actingAs($this->user);

        $titles = collect($this->getJson('/api/me/sermons')->assertOk()->json('data'))->pluck('title');

        $this->assertContains('Published One', $titles);
        $this->assertNotContains('Draft One', $titles);
    }

    public function test_branch_scoping_allows_network_wide_and_own_branch(): void
    {
        Sermon::factory()->create(['title' => 'Network Wide', 'branch_id' => null]);
        Sermon::factory()->create(['title' => 'My Branch', 'branch_id' => $this->branch->id]);

        $otherBranch = Branch::factory()->create();
        Sermon::factory()->create(['title' => 'Other Branch', 'branch_id' => $otherBranch->id]);

        Sanctum::actingAs($this->user);

        $titles = collect($this->getJson('/api/me/sermons')->assertOk()->json('data'))->pluck('title');

        $this->assertContains('Network Wide', $titles);
        $this->assertContains('My Branch', $titles);
        $this->assertNotContains('Other Branch', $titles);
    }

    public function test_can_filter_by_series_speaker_and_search(): void
    {
        $series = Series::factory()->create(['name' => 'Grow Deep']);
        Sermon::factory()->create(['title' => 'Rooted', 'series_id' => $series->id, 'speaker' => 'Pastor Emmanuel']);
        Sermon::factory()->create(['title' => 'Unrelated', 'speaker' => 'Pastor Joy']);

        Sanctum::actingAs($this->user);

        $bySeries = $this->getJson('/api/me/sermons?series_id='.$series->id)->assertOk()->json('data');
        $this->assertCount(1, $bySeries);
        $this->assertSame('Rooted', $bySeries[0]['title']);

        $bySpeaker = $this->getJson('/api/me/sermons?speaker=Emmanuel')->assertOk()->json('data');
        $this->assertCount(1, $bySpeaker);

        $bySearch = $this->getJson('/api/me/sermons?search=Rooted')->assertOk()->json('data');
        $this->assertCount(1, $bySearch);
    }

    public function test_sort_orders_results(): void
    {
        Sermon::factory()->create(['title' => 'Older', 'preached_on' => now()->subMonth()]);
        Sermon::factory()->create(['title' => 'Newer', 'preached_on' => now()->subDay()]);

        Sanctum::actingAs($this->user);

        $newest = $this->getJson('/api/me/sermons')->assertOk()->json('data');
        $this->assertSame('Newer', $newest[0]['title']);

        $oldest = $this->getJson('/api/me/sermons?sort=oldest')->assertOk()->json('data');
        $this->assertSame('Older', $oldest[0]['title']);
    }

    public function test_detail_includes_series_and_ordered_passages(): void
    {
        $series = Series::factory()->create(['name' => 'Grow Deep']);
        $sermon = Sermon::factory()->create(['series_id' => $series->id, 'duration_seconds' => 2530]);

        SermonPassage::factory()->create(['sermon_id' => $sermon->id, 'reference' => 'Romans 4:1-12', 'position' => 1]);
        SermonPassage::factory()->create(['sermon_id' => $sermon->id, 'reference' => 'Psalm 1:1-3', 'position' => 0]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/me/sermons/{$sermon->id}")->assertOk();

        $response->assertJsonPath('data.series.name', 'Grow Deep')
            ->assertJsonPath('data.duration_label', '42:10')
            ->assertJsonPath('data.passages.0.reference', 'Psalm 1:1-3')
            ->assertJsonPath('data.passages.1.reference', 'Romans 4:1-12');
    }

    public function test_cannot_view_unpublished_or_other_branch_sermon(): void
    {
        $draft = Sermon::factory()->unpublished()->create();

        $otherBranch = Branch::factory()->create();
        $other = Sermon::factory()->create(['branch_id' => $otherBranch->id]);

        Sanctum::actingAs($this->user);

        $this->getJson("/api/me/sermons/{$draft->id}")->assertNotFound();
        $this->getJson("/api/me/sermons/{$other->id}")->assertNotFound();
    }

    public function test_series_endpoint_counts_published_sermons(): void
    {
        $series = Series::factory()->create(['name' => 'Grow Deep']);
        Sermon::factory()->count(2)->create(['series_id' => $series->id]);
        Sermon::factory()->unpublished()->create(['series_id' => $series->id]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/sermons/series')->assertOk();

        $this->assertSame('Grow Deep', $response->json('data.0.name'));
        $this->assertSame(2, $response->json('data.0.sermons_count'));
    }

    public function test_member_can_save_and_unsave_a_sermon(): void
    {
        $sermon = Sermon::factory()->create();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/sermons/{$sermon->id}/save")->assertOk();

        $this->assertDatabaseHas('member_saved_sermons', [
            'member_id' => $this->member->id,
            'sermon_id' => $sermon->id,
        ]);

        $this->getJson("/api/me/sermons/{$sermon->id}")
            ->assertOk()
            ->assertJsonPath('data.is_saved', true);

        $saved = $this->getJson('/api/me/sermons/saved')->assertOk()->json('data');
        $this->assertCount(1, $saved);

        $this->deleteJson("/api/me/sermons/{$sermon->id}/save")->assertOk();

        $this->assertDatabaseMissing('member_saved_sermons', [
            'member_id' => $this->member->id,
            'sermon_id' => $sermon->id,
        ]);
    }

    public function test_saving_is_idempotent(): void
    {
        $sermon = Sermon::factory()->create();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/sermons/{$sermon->id}/save")->assertOk();
        $this->postJson("/api/me/sermons/{$sermon->id}/save")->assertOk();

        $this->assertDatabaseCount('member_saved_sermons', 1);
    }

    public function test_saved_sermons_do_not_leak_between_members(): void
    {
        $sermon = Sermon::factory()->create();

        $otherUser = User::factory()->create();
        $otherUser->assignRole('church_member', $this->branch->id);
        $otherMember = Member::factory()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $this->branch->id,
        ]);
        $otherMember->savedSermons()->attach($sermon->id, ['saved_at' => now()]);

        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/sermons/saved')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_live_sermon_exposes_live_url(): void
    {
        $sermon = Sermon::factory()->live()->create();
        Sanctum::actingAs($this->user);

        $this->getJson("/api/me/sermons/{$sermon->id}")
            ->assertOk()
            ->assertJsonPath('data.is_live', true)
            ->assertJsonPath('data.live_url', 'https://example.test/live');
    }

    public function test_passages_endpoint_returns_scripture_text(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'rest.api.bible/*' => \Illuminate\Support\Facades\Http::response([
                'data' => [
                    'reference' => 'Psalms 1:1-3',
                    'content' => '[1] Oh, the joys of those who do not follow the advice of the wicked...',
                    'copyright' => 'Holy Bible, New Living Translation',
                ],
            ]),
        ]);
        config()->set('bible.key', 'test-key');

        $sermon = Sermon::factory()->create();
        SermonPassage::factory()->create(['sermon_id' => $sermon->id, 'reference' => 'Psalm 1:1-3', 'position' => 0]);

        Sanctum::actingAs($this->user);

        $this->getJson("/api/me/sermons/{$sermon->id}/passages")
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'Psalm 1:1-3')
            ->assertJsonPath('data.0.copyright', 'Holy Bible, New Living Translation')
            ->assertJsonPath('meta.scripture_available', true);
    }

    public function test_passages_endpoint_still_returns_references_when_scripture_is_unavailable(): void
    {
        config()->set('bible.key', null);

        $sermon = Sermon::factory()->create();
        SermonPassage::factory()->create(['sermon_id' => $sermon->id, 'reference' => 'Psalm 1:1-3', 'position' => 0]);

        Sanctum::actingAs($this->user);

        // The reference must survive even with no scripture provider.
        $this->getJson("/api/me/sermons/{$sermon->id}/passages")
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'Psalm 1:1-3')
            ->assertJsonPath('data.0.text', null)
            ->assertJsonPath('meta.scripture_available', false);
    }

    public function test_passages_endpoint_rejects_unknown_translation(): void
    {
        $sermon = Sermon::factory()->create();
        Sanctum::actingAs($this->user);

        $this->getJson("/api/me/sermons/{$sermon->id}/passages?translation=FAKE")
            ->assertUnprocessable();
    }
}
