<?php

declare(strict_types=1);

namespace Tests\Feature\Pastor;

use App\Models\Branch;
use App\Models\Series;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class SeriesManagementTest extends TestCase
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

    public function test_guest_is_redirected(): void
    {
        $this->get(route('pastor.series'))->assertRedirect();
    }

    public function test_church_member_cannot_manage_series(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('church_member', $this->branch->id);

        $this->actingAs($member)
            ->get(route('pastor.series.create'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_pastor_can_create_a_series(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.series.store'), [
            'name' => 'Grow Deep',
            'description' => 'A series on rootedness.',
            'tone' => 'purple',
            'starts_on' => now()->subWeek()->format('Y-m-d'),
            'is_published' => '1',
        ])->assertRedirect(route('pastor.series'));

        $series = Series::firstWhere('name', 'Grow Deep');

        $this->assertNotNull($series);
        $this->assertSame('grow-deep', $series->slug);
        $this->assertSame('purple', $series->tone);
        $this->assertSame($this->branch->id, $series->branch_id);
        $this->assertTrue($series->is_published);
    }

    public function test_validation_rejects_missing_name_and_bad_dates(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.series.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->post(route('pastor.series.store'), [
            'name' => 'Backwards',
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-05-01',
        ])->assertSessionHasErrors('ends_on');
    }

    public function test_pastor_can_upload_a_series_cover(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.series.store'), [
            'name' => 'On Mission',
            'cover' => UploadedFile::fake()->image('series.jpg'),
        ])->assertSessionHasNoErrors();

        $series = Series::firstWhere('name', 'On Mission');

        $this->assertNotNull($series->getFirstMedia('cover'));
        $this->assertNotNull($series->cover_url);
    }

    public function test_pastor_can_update_a_series(): void
    {
        $series = Series::factory()->create(['name' => 'Old Name', 'branch_id' => $this->branch->id]);

        $this->actingAs($this->pastor)
            ->put(route('pastor.series.update', $series), [
                'name' => 'New Name',
                'tone' => 'amber',
                'is_published' => '1',
            ])->assertRedirect(route('pastor.series'));

        $series->refresh();

        $this->assertSame('New Name', $series->name);
        $this->assertSame('amber', $series->tone);
    }

    public function test_deleting_a_series_keeps_its_sermons(): void
    {
        $series = Series::factory()->create(['branch_id' => $this->branch->id]);
        $sermon = Sermon::factory()->create([
            'series_id' => $series->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->pastor)
            ->delete(route('pastor.series.destroy', $series))
            ->assertRedirect(route('pastor.series'));

        $this->assertSoftDeleted('series', ['id' => $series->id]);

        // The sermon survives, just without a series.
        $sermon->refresh();
        $this->assertNull($sermon->deleted_at);
        $this->assertNull($sermon->series_id);
    }

    public function test_pastor_cannot_edit_another_branchs_series(): void
    {
        $otherBranch = Branch::factory()->create();
        $series = Series::factory()->create(['branch_id' => $otherBranch->id]);

        $this->actingAs($this->pastor);

        $this->get(route('pastor.series.edit', $series))->assertForbidden();
        $this->put(route('pastor.series.update', $series), ['name' => 'Hijacked'])->assertForbidden();
    }

    public function test_index_scopes_to_own_and_network_series(): void
    {
        Series::factory()->create(['name' => 'My Branch Series', 'branch_id' => $this->branch->id]);
        Series::factory()->create(['name' => 'Network Series', 'branch_id' => null]);

        $otherBranch = Branch::factory()->create();
        Series::factory()->create(['name' => 'Other Branch Series', 'branch_id' => $otherBranch->id]);

        $this->actingAs($this->pastor)
            ->get(route('pastor.series'))
            ->assertOk()
            ->assertSee('My Branch Series')
            ->assertSee('Network Series')
            ->assertDontSee('Other Branch Series');
    }

    public function test_new_series_is_selectable_on_the_sermon_form(): void
    {
        $this->actingAs($this->pastor);

        $this->post(route('pastor.series.store'), ['name' => 'Fresh Series']);

        $this->get(route('pastor.sermons.create'))
            ->assertOk()
            ->assertSee('Fresh Series');
    }

    /**
     * The sidebar is rendered from consolidated-sidebar, not the role partials,
     * so links added to the wrong file silently never appear.
     */
    public function test_sidebar_exposes_sermons_and_series_links(): void
    {
        $this->actingAs($this->pastor)
            ->get(route('pastor.sermons'))
            ->assertOk()
            ->assertSee(route('pastor.sermons'), false)
            ->assertSee(route('pastor.series'), false);
    }

    public function test_super_admin_sidebar_exposes_sermons_and_series_links(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get(route('pastor.sermons'))
            ->assertOk()
            ->assertSee(route('pastor.sermons'), false)
            ->assertSee(route('pastor.series'), false);
    }
}
