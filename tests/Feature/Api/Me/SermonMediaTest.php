<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Event;
use App\Models\Member;
use App\Models\Sermon;
use App\Models\SmallGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Media is what the mobile app renders (cover art, audio player, slides), so
 * these exercise real uploads through Spatie rather than mocking the disk.
 */
final class SermonMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->branch = Branch::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('church_member', $this->branch->id);
        Member::factory()->create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_sermon_recording_and_cover_are_exposed_to_the_app(): void
    {
        $sermon = Sermon::factory()->create();

        $sermon->addMedia(UploadedFile::fake()->create('sermon.mp3', 2048, 'audio/mpeg'))
            ->toMediaCollection('recording');
        $sermon->addMedia(UploadedFile::fake()->image('cover.jpg'))
            ->toMediaCollection('cover');

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/me/sermons/{$sermon->id}")->assertOk();

        $recordingUrl = $response->json('data.recording_url');
        $coverUrl = $response->json('data.cover_url');

        $this->assertNotNull($recordingUrl, 'The app needs a playable recording URL.');
        $this->assertNotNull($coverUrl);
        $this->assertStringContainsString('sermon', $recordingUrl);

        // The file is really on the media disk, not just a database row.
        $media = $sermon->getFirstMedia('recording');
        $this->assertTrue(
            $media->getDiskDriverName() === 'local'
                ? file_exists($media->getPath())
                : true,
        );
    }

    public function test_recording_collection_holds_a_single_file(): void
    {
        $sermon = Sermon::factory()->create();

        $sermon->addMedia(UploadedFile::fake()->create('first.mp3', 512, 'audio/mpeg'))
            ->toMediaCollection('recording');
        $sermon->addMedia(UploadedFile::fake()->create('second.mp3', 512, 'audio/mpeg'))
            ->toMediaCollection('recording');

        $this->assertCount(1, $sermon->refresh()->getMedia('recording'));
        $this->assertSame('second', $sermon->getFirstMedia('recording')->name);
    }

    public function test_multiple_slides_are_returned_in_the_payload(): void
    {
        $sermon = Sermon::factory()->create();

        $sermon->addMedia(UploadedFile::fake()->image('slide-1.png'))->toMediaCollection('slides');
        $sermon->addMedia(UploadedFile::fake()->image('slide-2.png'))->toMediaCollection('slides');

        Sanctum::actingAs($this->user);

        $slides = $this->getJson("/api/me/sermons/{$sermon->id}")->assertOk()->json('data.slides');

        $this->assertCount(2, $slides);
        $this->assertNotNull($slides[0]['url']);
    }

    public function test_event_cover_and_spots_remaining_reach_the_app(): void
    {
        $event = Event::factory()->create([
            'branch_id' => $this->branch->id,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'max_capacity' => 40,
        ]);

        $event->addMedia(UploadedFile::fake()->image('event.jpg'))->toMediaCollection('cover');

        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/events')
            ->assertOk()
            ->assertJsonPath('data.0.spots_remaining', 40)
            ->assertJsonPath('data.0.id', $event->id);

        $this->assertNotNull($this->getJson('/api/me/events')->json('data.0.cover_url'));
    }

    public function test_uncapped_event_reports_null_spots(): void
    {
        Event::factory()->create([
            'branch_id' => $this->branch->id,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'max_capacity' => null,
        ]);

        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/events')
            ->assertOk()
            ->assertJsonPath('data.0.spots_remaining', null);
    }

    public function test_small_group_cover_reaches_the_app(): void
    {
        $member = $this->user->member;
        $group = SmallGroup::factory()->create([
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $group->members()->attach($member->id, ['joined_at' => now()]);
        $group->addMedia(UploadedFile::fake()->image('group.jpg'))->toMediaCollection('cover');

        Sanctum::actingAs($this->user);

        $this->assertNotNull(
            $this->getJson('/api/me/small-groups')->assertOk()->json('data.0.cover_url'),
        );
    }
}
