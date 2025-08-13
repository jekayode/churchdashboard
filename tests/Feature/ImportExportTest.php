<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

final class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $branchPastor;
    private User $regularUser;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $superAdminRole = Role::create([
            'name' => 'super_admin', 
            'display_name' => 'Super Admin',
            'description' => 'System Administrator'
        ]);
        $branchPastorRole = Role::create([
            'name' => 'branch_pastor', 
            'display_name' => 'Branch Pastor',
            'description' => 'Branch Pastor'
        ]);
        $memberRole = Role::create([
            'name' => 'church_member', 
            'display_name' => 'Church Member',
            'description' => 'Church Member'
        ]);

        // Create branch
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'venue' => '123 Test St, Test City, TS 12345',
            'service_time' => '10:00 AM',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'status' => 'active',
        ]);

        // Create users
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->superAdmin->roles()->attach($superAdminRole, ['branch_id' => null]);

        $this->branchPastor = User::create([
            'name' => 'Branch Pastor',
            'email' => 'pastor@branch.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->branchPastor->roles()->attach($branchPastorRole, ['branch_id' => $this->branch->id]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->regularUser->roles()->attach($memberRole, ['branch_id' => $this->branch->id]);

        // Link pastor to branch
        $this->branch->pastor()->associate($this->branchPastor);
        $this->branch->save();

        Storage::fake('public');
    }

    /**
     * Test super admin can import members
     */
    public function test_super_admin_can_import_members(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Debug user authorization
        $user = auth()->user();
        dump([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
            'is_super_admin' => $user->isSuperAdmin(),
            'is_branch_pastor' => $user->isBranchPastor(),
        ]);

        $csvContent = "first_name,last_name,email,phone,gender,date_of_birth,member_status,growth_level\n";
        $csvContent .= "John,Doe,john@example.com,1234567890,male,1990-01-01,member,100_level\n";
        $csvContent .= "Jane,Smith,jane@example.com,0987654321,female,1985-05-15,member,200_level\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
            'branch_id' => $this->branch->id,
        ]);

        // Debug response
        dump($response->json());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Import completed successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'summary' => [
                    'total_processed',
                    'successful_imports',
                    'failed_imports',
                    'success_rate',
                    'errors',
                    'successes',
                ],
            ]);

        $this->assertDatabaseHas('members', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('members', [
            'email' => 'jane@example.com',
            'name' => 'Jane Smith',
        ]);
    }

    /**
     * Test branch pastor can only import members to their branch
     */
    public function test_branch_pastor_can_only_import_to_their_branch(): void
    {
        Sanctum::actingAs($this->branchPastor);

        $csvContent = "First Name,Last Name,Email,Phone,Address,Gender,Birth Date,Baptism Date,Growth Level,TECI\n";
        $csvContent .= "John,Doe,john@example.com,1234567890,123 Main St,Male,1990-01-01,2010-01-01,1,2\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test branch pastor cannot import to other branches
     */
    public function test_branch_pastor_cannot_import_to_other_branch(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Other Branch',
            'venue' => '456 Other St, Other City, OT 67890',
            'service_time' => '9:00 AM',
            'phone' => '9876543210',
            'email' => 'other@example.com',
            'status' => 'active',
        ]);

        Sanctum::actingAs($this->branchPastor);

        $csvContent = "First Name,Last Name,Email,Phone,Address,Gender,Birth Date,Baptism Date,Growth Level,TECI\n";
        $csvContent .= "John,Doe,john@example.com,1234567890,123 Main St,Male,1990-01-01,2010-01-01,1,2\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
            'branch_id' => $otherBranch->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test regular user cannot import members
     */
    public function test_regular_user_cannot_import_members(): void
    {
        Sanctum::actingAs($this->regularUser);

        $csvContent = "First Name,Last Name,Email,Phone,Address,Gender,Birth Date,Baptism Date,Growth Level,TECI\n";
        $csvContent .= "John,Doe,john@example.com,1234567890,123 Main St,Male,1990-01-01,2010-01-01,1,2\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test import validation rejects invalid file types
     */
    public function test_import_validation_rejects_invalid_file_types(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Test import validation requires branch_id
     */
    public function test_import_validation_requires_branch_id(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $csvContent = "First Name,Last Name,Email,Phone,Address,Gender,Birth Date,Baptism Date,Growth Level,TECI\n";
        $csvContent .= "John,Doe,john@example.com,1234567890,123 Main St,Male,1990-01-01,2010-01-01,1,2\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['branch_id']);
    }

    /**
     * Test super admin can export members
     */
    public function test_super_admin_can_export_members(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Create test members
        Member::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'member_status' => 'member',
            'growth_level' => 'new_believer',
            'teci_status' => 'not_started',
            'branch_id' => $this->branch->id,
        ]);

        Member::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'gender' => 'female',
            'date_of_birth' => '1985-05-15',
            'member_status' => 'member',
            'growth_level' => 'growing',
            'teci_status' => '100_level',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->postJson('/api/import-export/members/export', [
            'branch_id' => $this->branch->id,
            'filters' => [],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Export completed successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'filename',
                'download_url',
                'summary',
            ]);
    }

    /**
     * Test branch pastor can export their branch members
     */
    public function test_branch_pastor_can_export_their_branch_members(): void
    {
        Sanctum::actingAs($this->branchPastor);

        // Create test member
        Member::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'member_status' => 'member',
            'growth_level' => 'new_believer',
            'teci_status' => 'not_started',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->postJson('/api/import-export/members/export', [
            'branch_id' => $this->branch->id,
            'filters' => [],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Export completed successfully',
            ]);
    }

    /**
     * Test regular user cannot export members
     */
    public function test_regular_user_cannot_export_members(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->postJson('/api/import-export/members/export', [
            'branch_id' => $this->branch->id,
            'filters' => [],
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test can get member import template
     */
    public function test_can_get_member_import_template(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/import-export/members/import-template');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="member_import_template.csv"');
    }

    /**
     * Test can validate import file
     */
    public function test_can_validate_import_file(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $csvContent = "First Name,Last Name,Email,Phone,Address,Gender,Birth Date,Baptism Date,Growth Level,TECI\n";
        $csvContent .= "John,Doe,john@example.com,1234567890,123 Main St,Male,1990-01-01,2010-01-01,1,2\n";
        $csvContent .= "Jane,Smith,jane@example.com,0987654321,456 Oak Ave,Female,1985-05-15,2015-05-15,2,3\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/validate-import-file', [
            'file' => $file,
            'type' => 'members',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File validation successful',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'valid_rows',
                    'invalid_rows',
                    'total_rows',
                    'errors',
                ],
            ]);
    }

    /**
     * Test can get import/export statistics
     */
    public function test_can_get_import_export_statistics(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/import-export/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'imports' => [
                        'total',
                        'successful',
                        'failed',
                        'today',
                        'this_week',
                        'this_month',
                    ],
                    'exports' => [
                        'total',
                        'today',
                        'this_week',
                        'this_month',
                    ],
                    'storage' => [
                        'used',
                        'available',
                        'percentage',
                    ],
                ],
            ]);
    }

    /**
     * Test can cleanup old exports
     */
    public function test_can_cleanup_old_exports(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson('/api/import-export/cleanup-exports');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'deleted_count',
            ]);
    }

    /**
     * Test import handles validation errors properly
     */
    public function test_import_handles_validation_errors_properly(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $csvContent = "First Name,Last Name,Email,Phone,Address,Gender,Birth Date,Baptism Date,Growth Level,TECI\n";
        $csvContent .= "John,Doe,invalid-email,1234567890,123 Main St,Male,1990-01-01,2010-01-01,1,2\n";
        $csvContent .= "Jane,Smith,jane@example.com,0987654321,456 Oak Ave,Female,invalid-date,2015-05-15,2,3\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $file,
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Import completed with some errors',
            ])
            ->assertJsonStructure([
                'data' => [
                    'processed',
                    'successful',
                    'failed',
                    'errors',
                ],
            ]);
    }

    /**
     * Test export with filters
     */
    public function test_export_with_filters(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Create test members with different statuses
        Member::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'member_status' => 'member',
            'growth_level' => 'new_believer',
            'teci_status' => 'not_started',
            'branch_id' => $this->branch->id,
        ]);

        Member::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'gender' => 'female',
            'date_of_birth' => '1985-05-15',
            'member_status' => 'volunteer',
            'growth_level' => 'growing',
            'teci_status' => '100_level',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->postJson('/api/import-export/members/export', [
            'branch_id' => $this->branch->id,
            'filters' => [
                'member_status' => 'member',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Export completed successfully',
            ]);
    }
} 