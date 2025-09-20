<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    use WithFaker;
    /**
     * A basic feature test example.
     */
    #[Test]
    public function user_can_login_without_mfa_enabled(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'email_verified_at',
                ],
            ])->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_user_has_wrong_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'pass',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
            ])->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }
    #[Test]
    public function user_cannot_login_when_mfa_enabled()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'mfa_secret' => 'some-secret',
        ]);



        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'mfaSessionToken',
            ])->assertJson([
                'message' => 'MFA required',
            ]);
    }

    #[Test]
    public function logout_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token')->plainTextToken;
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/auth/logout');
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'Test Token',
        ]);
    }


    public function test_init_mfa_setup_stores_secret_and_returns_qr_code()
    {
        $user = User::factory()->create(['mfa_secret' => null]);
        $this->actingAs($user);

        $response = $this->getJson('/api/auth/setup-mfa');

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'secret', 'qrCodeUrl']);
    }

    #[Test]
    public function mfa_already_enabled()
    {
        $user = User::factory()->create(['mfa_secret' => 'test']);
        $this->actingAs($user);
        $response = $this->getJson('/api/auth/setup-mfa');
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'MFA already enabled',
            ]);
    }

    #[Test]
    public function verify_mfa_setup_with_valid_code()
    {
        $user = User::factory()->create(['mfa_secret' => null]);
        $this->actingAs($user);

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        Cache::put("mfa_setup_{$user->id}", $secret, now()->addMinutes(10));

        $response = $this->postJson('/api/auth/setup-mfa', [
            'code' => $google2fa->getCurrentOtp($secret),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'MFA enabled successfully',
            ]);

        $user->refresh();
        $this->assertNotNull($user->mfa_secret);
    }

    #[Test]
    public function verify_mfa_setup_with_invalid_code()
    {
        $user = User::factory()->create(['mfa_secret' => null]);
        $this->actingAs($user);

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        Cache::put("mfa_setup_{$user->id}", $secret, now()->addMinutes(10));

        $response = $this->postJson('/api/auth/setup-mfa', [
            'code' => '123456', // Invalid code
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid MFA code',
            ]);
    }

    #[Test]
    public function verify_mfa_setup_without_secret_returns_error()
    {
        $user = User::factory()->create(['mfa_secret' => null]);
        $this->actingAs($user);

        // Ensure no secret is cached
        Cache::forget("mfa_setup_{$user->id}");

        $response = $this->postJson('/api/auth/setup-mfa', [
            'code' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'No MFA setup in progress',
            ]);
    }

    // tests/Feature/Api/V1/Auth/AuthControllerTest.php

    #[Test]
    public function verify_mfa_with_valid_code()
    {
        $user = User::factory()->create([
            'mfa_secret' => null,
            'password' => bcrypt('password123'),
        ]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->mfa_secret = Crypt::encrypt($secret);
        $user->save();

        $mfaSessionToken = bin2hex(random_bytes(32));
        Cache::put("mfa_session_{$mfaSessionToken}", $user->id, 300);

        $response = $this->postJson('/api/auth/verify-mfa', [
            'mfaSessionToken' => $mfaSessionToken,
            'mfaCode' => $google2fa->getCurrentOtp($secret),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ])
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    #[Test]
    public function verify_mfa_with_invalid_code()
    {
        $user = User::factory()->create([
            'mfa_secret' => null,
            'password' => bcrypt('password123'),
        ]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->mfa_secret = Crypt::encrypt($secret);
        $user->save();

        $mfaSessionToken = bin2hex(random_bytes(32));
        Cache::put("mfa_session_{$mfaSessionToken}", $user->id, 300);

        $response = $this->postJson('/api/auth/verify-mfa', [
            'mfaSessionToken' => $mfaSessionToken,
            'mfaCode' => '123456', // Invalid code
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid MFA code',
            ]);
    }

    #[Test]
    public function verify_mfa_with_invalid_or_expired_session_token()
    {
        $user = User::factory()->create([
            'mfa_secret' => Crypt::encrypt('dummy-secret'),
        ]);

        $response = $this->postJson('/api/auth/verify-mfa', [
            'mfaSessionToken' => 'invalid_token',
            'mfaCode' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired MFA session token',
            ]);
    }
}
