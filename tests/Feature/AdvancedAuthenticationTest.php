<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

final class AdvancedAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2fa = new Google2FA();
    }

    public function test_api_user_registration(): void
    {
        $branch = Branch::factory()->create();
        $role = Role::factory()->create(['name' => 'church_member']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+1234567890',
            'branch_id' => $branch->id,
            'role_id' => $role->id,
            'device_name' => 'iPhone',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'phone', 'primary_role', 'primary_branch'],
                    'token',
                    'expires_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'phone' => '+1234567890',
        ]);
    }

    public function test_api_user_login(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'expires_at',
                ],
            ]);
    }

    public function test_api_user_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful',
            ]);
    }

    public function test_api_get_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'roles'],
                ],
            ]);
    }

    public function test_api_token_refresh(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device');
        
        // Use the token to authenticate
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ]);

        $response = $this->postJson('/api/auth/refresh', [
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'expires_at',
                ],
            ]);
    }

    public function test_two_factor_authentication_setup(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/two-factor');

        $response->assertStatus(200)
            ->assertViewIs('auth.two-factor')
            ->assertViewHas('user', $user);
    }

    public function test_two_factor_authentication_enable(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Generate a secret and save it
        $secret = $this->google2fa->generateSecretKey();
        $user->update(['two_factor_secret' => Crypt::encryptString($secret)]);

        // Generate a valid TOTP code
        $validCode = $this->google2fa->getCurrentOtp($secret);

        $response = $this->post('/two-factor/enable', [
            'code' => $validCode,
        ]);

        $response->assertRedirect()
            ->assertSessionHas('status', 'Two-factor authentication has been enabled successfully!');

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_two_factor_authentication_disable(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'two_factor_secret' => Crypt::encryptString('test-secret'),
            'two_factor_confirmed_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/two-factor/disable', [
            'password' => 'password123',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('status', 'Two-factor authentication has been disabled.');

        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_api_two_factor_verification_with_totp(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $validCode = $this->google2fa->getCurrentOtp($secret);

        $response = $this->postJson('/api/two-factor/verify', [
            'code' => $validCode,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Two-factor authentication code verified successfully.',
            ]);
    }

    public function test_api_two_factor_verification_with_recovery_code(): void
    {
        $recoveryCodes = collect(['RECOVERY1', 'RECOVERY2', 'RECOVERY3']);
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => Crypt::encryptString('test-secret'),
            'two_factor_recovery_codes' => Crypt::encryptString($recoveryCodes->toJson()),
            'two_factor_confirmed_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/two-factor/verify', [
            'code' => 'RECOVERY1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Recovery code verified successfully.',
                'recovery_code_used' => true,
            ]);

        // Verify the recovery code was removed
        $user->refresh();
        $updatedCodes = collect(json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true));
        $this->assertFalse($updatedCodes->contains('RECOVERY1'));
        $this->assertTrue($updatedCodes->contains('RECOVERY2'));
    }

    public function test_custom_password_reset_notification(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(302);
        // Note: In a real test, you would check that the custom notification was sent
        // This would require mocking the notification system
    }

    public function test_api_health_check(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }
} 