<?php

declare(strict_types=1);

namespace Tests\Feature\Builders;

use App\Models\BuilderRegistration;
use App\Models\BuilderSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BuilderAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function directoryAdmin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::query()->where('name', 'directory_admin')->first();
        $user->roles()->attach($role->id, ['branch_id' => null]);

        return $user;
    }

    #[Test]
    public function directory_admin_can_access_admin_pages(): void
    {
        $user = $this->directoryAdmin();

        $this->actingAs($user)
            ->get(route('admin.builders.index'))
            ->assertOk();
    }

    #[Test]
    public function church_member_cannot_access_admin_api(): void
    {
        $role = Role::query()->where('name', 'church_member')->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role->id, ['branch_id' => null]);

        $this->actingAs($user)
            ->getJson('/api/admin/builders/stats')
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_update_settings_and_upload_pack_file(): void
    {
        Storage::fake('public');
        $user = $this->directoryAdmin();

        $this->actingAs($user)
            ->putJson('/api/admin/builders/settings', [
                'whatsapp_group_link' => 'https://chat.whatsapp.com/example',
                'google_drive_link' => null,
                'intro_text' => 'Welcome builders',
                'confirmation_body' => 'Thanks!',
            ])
            ->assertOk();

        $settings = BuilderSetting::instance();
        $this->assertSame('https://chat.whatsapp.com/example', $settings->whatsapp_group_link);

        $this->actingAs($user)
            ->postJson('/api/admin/builders/resources', [
                'title' => 'Business Plan Template',
                'file' => UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf'),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('builder_resources', ['title' => 'Business Plan Template']);
    }

    #[Test]
    public function admin_can_mark_registration_contacted(): void
    {
        $user = $this->directoryAdmin();
        $registration = BuilderRegistration::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/builders/registrations/'.$registration->id.'/contacted')
            ->assertOk();

        $registration->refresh();
        $this->assertSame('contacted', $registration->status->value);
        $this->assertSame($user->id, $registration->contacted_by_user_id);
    }
}
