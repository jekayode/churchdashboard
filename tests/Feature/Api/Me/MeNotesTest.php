<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Note;
use App\Models\ReadingDay;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeNotesTest extends TestCase
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

    private function otherMember(): Member
    {
        $user = User::factory()->create();
        $user->assignRole('church_member', $this->branch->id);

        return Member::factory()->create(['user_id' => $user->id, 'branch_id' => $this->branch->id]);
    }

    public function test_guest_cannot_access_notes(): void
    {
        $this->getJson('/api/me/notes')->assertUnauthorized();
    }

    public function test_member_can_write_a_standalone_note(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/me/notes', ['body' => 'A thought I had.', 'title' => 'Monday'])
            ->assertCreated()
            ->assertJsonPath('data.kind', 'personal')
            ->assertJsonPath('data.title', 'Monday')
            ->assertJsonPath('data.context.label', null);

        $this->assertDatabaseHas('notes', [
            'member_id' => $this->member->id,
            'notable_type' => null,
            'body' => 'A thought I had.',
        ]);
    }

    public function test_member_can_write_a_note_on_a_sermon(): void
    {
        $sermon = Sermon::factory()->create(['title' => 'Rooted and Rising']);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/me/notes', [
            'body' => 'Psalm 1 stood out.',
            'type' => 'sermon',
            'notable_id' => $sermon->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.kind', 'sermon')
            // Context is what makes the hub readable rather than a wall of text.
            ->assertJsonPath('data.context.label', 'Rooted and Rising')
            ->assertJsonPath('data.context.id', $sermon->id);
    }

    public function test_member_can_write_a_note_on_a_reading_day(): void
    {
        $day = ReadingDay::factory()->create(['label' => 'July 18']);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/me/notes', [
            'body' => 'Roots grow in the dark.',
            'type' => 'reading',
            'notable_id' => $day->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.kind', 'reading')
            ->assertJsonPath('data.context.label', 'July 18');
    }

    public function test_attaching_to_a_missing_item_is_rejected(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/me/notes', ['body' => 'x', 'type' => 'sermon', 'notable_id' => 999999])
            ->assertUnprocessable();
    }

    public function test_body_is_required(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/me/notes', ['title' => 'No body'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_hub_lists_all_kinds_with_counts(): void
    {
        $sermon = Sermon::factory()->create(['title' => 'Rooted']);
        $day = ReadingDay::factory()->create(['label' => 'July 18']);

        Note::factory()->create(['member_id' => $this->member->id]);
        Note::factory()->create(['member_id' => $this->member->id, 'notable_type' => Sermon::class, 'notable_id' => $sermon->id]);
        Note::factory()->create(['member_id' => $this->member->id, 'notable_type' => ReadingDay::class, 'notable_id' => $day->id]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/notes')->assertOk();

        $this->assertCount(3, $response->json('data'));
        $this->assertSame(3, $response->json('meta.counts.all'));
        $this->assertSame(1, $response->json('meta.counts.sermon'));
        $this->assertSame(1, $response->json('meta.counts.reading'));
        $this->assertSame(1, $response->json('meta.counts.personal'));
    }

    public function test_hub_can_be_filtered_by_kind(): void
    {
        $sermon = Sermon::factory()->create();
        Note::factory()->create(['member_id' => $this->member->id, 'body' => 'Standalone one']);
        Note::factory()->create([
            'member_id' => $this->member->id,
            'body' => 'Sermon one',
            'notable_type' => Sermon::class,
            'notable_id' => $sermon->id,
        ]);

        Sanctum::actingAs($this->user);

        $sermonNotes = $this->getJson('/api/me/notes?type=sermon')->assertOk()->json('data');
        $this->assertCount(1, $sermonNotes);
        $this->assertSame('Sermon one', $sermonNotes[0]['body']);

        $personal = $this->getJson('/api/me/notes?type=personal')->assertOk()->json('data');
        $this->assertCount(1, $personal);
        $this->assertSame('Standalone one', $personal[0]['body']);
    }

    public function test_hub_can_be_searched(): void
    {
        Note::factory()->create(['member_id' => $this->member->id, 'body' => 'Something about grace']);
        Note::factory()->create(['member_id' => $this->member->id, 'body' => 'Unrelated thought']);

        Sanctum::actingAs($this->user);

        $results = $this->getJson('/api/me/notes?search=grace')->assertOk()->json('data');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('grace', $results[0]['body']);
    }

    public function test_notes_for_a_single_item(): void
    {
        $sermon = Sermon::factory()->create();
        $otherSermon = Sermon::factory()->create();

        Note::factory()->count(2)->create([
            'member_id' => $this->member->id,
            'notable_type' => Sermon::class,
            'notable_id' => $sermon->id,
        ]);
        Note::factory()->create([
            'member_id' => $this->member->id,
            'notable_type' => Sermon::class,
            'notable_id' => $otherSermon->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->getJson("/api/me/notes/for/sermon/{$sermon->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_unknown_item_type_is_rejected(): void
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/notes/for/banana/1')->assertUnprocessable();
    }

    public function test_member_can_edit_and_delete_their_note(): void
    {
        $note = Note::factory()->create(['member_id' => $this->member->id, 'body' => 'Original']);
        Sanctum::actingAs($this->user);

        $this->putJson("/api/me/notes/{$note->id}", ['body' => 'Revised'])
            ->assertOk()
            ->assertJsonPath('data.body', 'Revised');

        $this->deleteJson("/api/me/notes/{$note->id}")->assertOk();

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    /**
     * Notes are private. Nothing in the app — including leadership — may read
     * another member's notes.
     */
    public function test_another_members_notes_are_never_listed(): void
    {
        Note::factory()->create(['member_id' => $this->otherMember()->id, 'body' => 'Not mine']);
        Note::factory()->create(['member_id' => $this->member->id, 'body' => 'Mine']);

        Sanctum::actingAs($this->user);

        $notes = $this->getJson('/api/me/notes')->assertOk()->json('data');

        $this->assertCount(1, $notes);
        $this->assertSame('Mine', $notes[0]['body']);
    }

    public function test_another_members_note_cannot_be_read_updated_or_deleted(): void
    {
        $foreign = Note::factory()->create(['member_id' => $this->otherMember()->id]);

        Sanctum::actingAs($this->user);

        // 404 rather than 403, so the API never confirms the note exists.
        $this->getJson("/api/me/notes/{$foreign->id}")->assertNotFound();
        $this->putJson("/api/me/notes/{$foreign->id}", ['body' => 'hijack'])->assertNotFound();
        $this->deleteJson("/api/me/notes/{$foreign->id}")->assertNotFound();

        $this->assertDatabaseHas('notes', ['id' => $foreign->id, 'deleted_at' => null]);
    }

    public function test_notes_for_an_item_exclude_other_members(): void
    {
        $sermon = Sermon::factory()->create();

        Note::factory()->create([
            'member_id' => $this->otherMember()->id,
            'notable_type' => Sermon::class,
            'notable_id' => $sermon->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->getJson("/api/me/notes/for/sermon/{$sermon->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_a_pastor_cannot_read_member_notes_through_the_api(): void
    {
        $note = Note::factory()->create(['member_id' => $this->member->id, 'body' => 'Private']);

        $pastor = User::factory()->create();
        $pastor->assignRole('branch_pastor', $this->branch->id);
        Member::factory()->create(['user_id' => $pastor->id, 'branch_id' => $this->branch->id]);

        Sanctum::actingAs($pastor);

        // me/* is always the caller's own data, whatever their role.
        $this->getJson("/api/me/notes/{$note->id}")->assertNotFound();
        $this->getJson('/api/me/notes')->assertOk()->assertJsonCount(0, 'data');
    }
}
