<?php

declare(strict_types=1);

namespace Tests\Feature\Pastor;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Series;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class SermonManagementTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $pastor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->pastor = User::factory()->create(['email_verified_at' => now()]);
        $this->branch = Branch::factory()->create([
            'pastor_id' => $this->pastor->id,
            'status' => 'active',
        ]);
        $this->pastor->assignRole('branch_pastor', $this->branch->id);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Rooted and Rising',
            'description' => 'Growing deep while life moves fast.',
            'speaker' => 'Pastor Emmanuel Joseph',
            'preached_on' => now()->subWeek()->format('Y-m-d'),
            'duration_seconds' => 2530,
            'tone' => 'orange',
            'is_published' => '1',
        ], $overrides);
    }

    public function test_guest_is_redirected_from_sermon_pages(): void
    {
        $this->get(route('pastor.sermons'))->assertRedirect();
        $this->get(route('pastor.sermons.create'))->assertRedirect();
    }

    public function test_church_member_cannot_reach_sermon_management(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('church_member', $this->branch->id);

        // The role middleware redirects web requests away rather than returning 403.
        $this->actingAs($member)
            ->get(route('pastor.sermons.create'))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('sermons', 0);
    }

    public function test_pastor_can_view_index_and_create_form(): void
    {
        $this->actingAs($this->pastor);

        $this->get(route('pastor.sermons'))
            ->assertOk()
            ->assertSee('Sermon Library', false);

        $this->get(route('pastor.sermons.create'))
            ->assertOk()
            ->assertSee('Add sermon', false)
            ->assertSee('Bible passages', false);
    }

    public function test_pastor_can_create_a_sermon_with_passages(): void
    {
        $this->actingAs($this->pastor);

        $response = $this->post(route('pastor.sermons.store'), $this->validPayload([
            'passages' => [
                ['reference' => 'Psalm 1:1-3', 'book' => 'Psalm', 'chapter' => 1, 'verses' => '1-3'],
                ['reference' => 'Romans 4:1-12', 'book' => 'Romans', 'chapter' => 4, 'verses' => '1-12'],
            ],
        ]));

        $sermon = Sermon::firstWhere('title', 'Rooted and Rising');

        $this->assertNotNull($sermon);
        $response->assertRedirect(route('pastor.sermons.edit', $sermon));

        // Branch is taken from the pastor, never from user input.
        $this->assertSame($this->branch->id, $sermon->branch_id);
        $this->assertSame('rooted-and-rising', $sermon->slug);
        $this->assertTrue($sermon->is_published);

        $this->assertCount(2, $sermon->passages);
        $this->assertSame('Psalm 1:1-3', $sermon->passages[0]->reference);
        $this->assertSame(0, $sermon->passages[0]->position);
        $this->assertSame('Romans 4:1-12', $sermon->passages[1]->reference);
    }

    public function test_validation_rejects_incomplete_input(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.sermons.store'), ['title' => ''])
            ->assertSessionHasErrors(['title', 'speaker', 'preached_on']);
    }

    public function test_live_sermon_requires_a_stream_url(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.sermons.store'), $this->validPayload(['is_live' => '1']))
            ->assertSessionHasErrors('live_url');

        $this->post(route('pastor.sermons.store'), $this->validPayload([
            'is_live' => '1',
            'live_url' => 'https://stream.example/live',
        ]))->assertSessionHasNoErrors();
    }

    public function test_pastor_can_upload_recording_cover_and_slides(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.sermons.store'), $this->validPayload([
            'recording' => UploadedFile::fake()->create('sermon.mp3', 1024, 'audio/mpeg'),
            'cover' => UploadedFile::fake()->image('cover.jpg'),
            'slides' => [
                UploadedFile::fake()->image('slide-1.png'),
                UploadedFile::fake()->image('slide-2.png'),
            ],
        ]))->assertSessionHasNoErrors();

        $sermon = Sermon::firstWhere('title', 'Rooted and Rising');

        $this->assertNotNull($sermon->getFirstMedia('recording'));
        $this->assertNotNull($sermon->getFirstMedia('cover'));
        $this->assertCount(2, $sermon->getMedia('slides'));
        $this->assertNotNull($sermon->recording_url);
    }

    public function test_upload_rejects_a_non_audio_recording(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.sermons.store'), $this->validPayload([
            'recording' => UploadedFile::fake()->create('notes.txt', 16, 'text/plain'),
        ]))->assertSessionHasErrors('recording');
    }

    public function test_pastor_can_update_a_sermon_and_replace_passages(): void
    {
        $sermon = Sermon::factory()->create(['branch_id' => $this->branch->id, 'title' => 'Old Title']);
        $sermon->passages()->create(['reference' => 'John 1:1', 'position' => 0]);

        $this->actingAs($this->pastor);

        $this->put(route('pastor.sermons.update', $sermon), $this->validPayload([
            'title' => 'New Title',
            'passages' => [['reference' => 'Psalm 23:1-6', 'book' => 'Psalm', 'chapter' => 23, 'verses' => '1-6']],
        ]))->assertSessionHasNoErrors();

        $sermon->refresh()->load('passages');

        $this->assertSame('New Title', $sermon->title);
        $this->assertCount(1, $sermon->passages);
        $this->assertSame('Psalm 23:1-6', $sermon->passages[0]->reference);
    }

    public function test_pastor_cannot_edit_another_branchs_sermon(): void
    {
        $otherBranch = Branch::factory()->create();
        $sermon = Sermon::factory()->create(['branch_id' => $otherBranch->id]);

        $this->actingAs($this->pastor);

        $this->get(route('pastor.sermons.edit', $sermon))->assertForbidden();
        $this->put(route('pastor.sermons.update', $sermon), $this->validPayload())->assertForbidden();
    }

    public function test_pastor_cannot_edit_network_wide_sermon(): void
    {
        // Network-wide sermons belong to super admins, so one branch cannot rewrite them.
        $sermon = Sermon::factory()->create(['branch_id' => null]);

        $this->actingAs($this->pastor);

        $this->get(route('pastor.sermons.edit', $sermon))->assertForbidden();
    }

    public function test_index_hides_other_branch_sermons(): void
    {
        Sermon::factory()->create(['branch_id' => $this->branch->id, 'title' => 'My Branch Sermon']);
        Sermon::factory()->create(['branch_id' => null, 'title' => 'Network Sermon']);

        $otherBranch = Branch::factory()->create();
        Sermon::factory()->create(['branch_id' => $otherBranch->id, 'title' => 'Other Branch Sermon']);

        $this->actingAs($this->pastor)
            ->get(route('pastor.sermons'))
            ->assertOk()
            ->assertSee('My Branch Sermon')
            ->assertSee('Network Sermon')
            ->assertDontSee('Other Branch Sermon');
    }

    public function test_pastor_can_delete_a_sermon(): void
    {
        $sermon = Sermon::factory()->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->pastor)
            ->delete(route('pastor.sermons.destroy', $sermon))
            ->assertRedirect(route('pastor.sermons'));

        $this->assertSoftDeleted('sermons', ['id' => $sermon->id]);
    }

    public function test_pastor_can_remove_a_single_slide(): void
    {
        $sermon = Sermon::factory()->create(['branch_id' => $this->branch->id]);
        $sermon->addMedia(UploadedFile::fake()->image('slide.png'))->toMediaCollection('slides');
        $slide = $sermon->getFirstMedia('slides');

        $this->actingAs($this->pastor)
            ->delete(route('pastor.sermons.media.destroy', [$sermon, $slide->id]))
            ->assertRedirect();

        $this->assertCount(0, $sermon->refresh()->getMedia('slides'));
    }

    public function test_series_dropdown_is_scoped_to_the_pastors_branch(): void
    {
        Series::factory()->create(['name' => 'My Branch Series', 'branch_id' => $this->branch->id]);
        Series::factory()->create(['name' => 'Network Series', 'branch_id' => null]);

        $otherBranch = Branch::factory()->create();
        Series::factory()->create(['name' => 'Other Branch Series', 'branch_id' => $otherBranch->id]);

        $this->actingAs($this->pastor)
            ->get(route('pastor.sermons.create'))
            ->assertOk()
            ->assertSee('My Branch Series')
            ->assertSee('Network Series')
            ->assertDontSee('Other Branch Series');
    }

    public function test_created_sermon_becomes_visible_in_the_member_api(): void
    {
        // The whole point of this UI: what a pastor publishes reaches the app.
        $memberUser = User::factory()->create();
        $memberUser->assignRole('church_member', $this->branch->id);
        Member::factory()->create(['user_id' => $memberUser->id, 'branch_id' => $this->branch->id]);

        $this->actingAs($this->pastor)
            ->post(route('pastor.sermons.store'), $this->validPayload());

        \Laravel\Sanctum\Sanctum::actingAs($memberUser);

        $this->getJson('/api/me/sermons')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Rooted and Rising')
            ->assertJsonPath('data.0.duration_label', '42:10');
    }

    public function test_unpublished_sermon_stays_hidden_from_members(): void
    {
        $memberUser = User::factory()->create();
        $memberUser->assignRole('church_member', $this->branch->id);
        Member::factory()->create(['user_id' => $memberUser->id, 'branch_id' => $this->branch->id]);

        $this->actingAs($this->pastor)
            ->post(route('pastor.sermons.store'), $this->validPayload(['is_published' => '0']));

        \Laravel\Sanctum\Sanctum::actingAs($memberUser);

        $this->getJson('/api/me/sermons')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
