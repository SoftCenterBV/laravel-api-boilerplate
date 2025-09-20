<?php

namespace Tests\Feature\Api\V1\Access;

use App\Events\SendUserInvite;
use App\Models\Organization;
use App\Models\OrganizationUserInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccessControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    #[Test]
    public function list_returns_user_invitations()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $invite = OrganizationUserInvite::factory()->create([
            'email' => $user->email,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/access/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'email',
                        'token',
                        'organization_id',
                        'accepted_at',
                        'rejected_at',
                        'deleted_at',
                    ],
                ],
            ])
            ->assertJson([
                'message' => 'Invitations retrieved successfully.',
            ]);

        $this->assertDatabaseHas('organization_user_invites', [
            'id' => $invite->id,
            'email' => $user->email,
        ]);
    }
    #[Test]
    public function invite_sends_invitation_and_returns_success()
    {
        Event::fake();

        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/access/invite', [
            'email' => 'invitee@example.com',
            'organization_id' => $organization->id,
            'role' => 'admin'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'email',
                    'token',
                    'organization_id',
                    'id',
                ],
            ])
            ->assertJson([
                'message' => 'Invitation sent successfully.',
            ]);

        $this->assertDatabaseHas('organization_user_invites', [
            'email' => 'invitee@example.com',
            'organization_id' => $organization->id,
        ]);

        Event::assertDispatched(SendUserInvite::class);
    }

    #[Test]
    public function invite_with_missing_fields_returns_validation_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/access/invite', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'organization_id']);
    }

    #[Test]
    public function accept_valid_invite()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $token = encrypt('valid-token');
        $invite = OrganizationUserInvite::factory()->create([
            'email' => $user->email,
            'organization_id' => $organization->id,
            'token' => $token,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/access/accept', [
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'email',
                    'token',
                    'organization_id',
                    'accepted_at',
                ],
            ])
            ->assertJson([
                'message' => 'Invitation accepted successfully.',
            ]);

        $this->assertDatabaseHas('organization_user_invites', [
            'id' => $invite->id,
            'accepted_at' => now(),
        ]);
    }

    #[Test]
    public function accept_invalid_invite_returns_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/access/accept', [
            'token' => encrypt('invalid-token'),
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Invalid or expired invitation token.',
                'data' => null,
            ]);
    }


    #[Test]
    public function reject_valid_invite()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $token = encrypt('valid-token');
        $invite = OrganizationUserInvite::factory()->create([
            'email' => $user->email,
            'organization_id' => $organization->id,
            'token' => $token,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/access/reject', [
            'token' => $token,
        ]);

        //        dd($response->json());

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Invitation rejected successfully.',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'organization_id',
                    'email',
                    'role',
                    'token',
                    'accepted_at',
                    'rejected_at',
                    'deleted_at',
                ],
            ]);

        $this->assertDatabaseHas('organization_user_invites', [
            'id' => $invite->id,
            'rejected_at' => now(),
        ]);
    }

    #[Test]
    public function reject_invalid_invite_returns_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/access/reject', [
            'token' => encrypt('invalid-token'),
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Invalid or expired invitation token.',
                'data' => null,
            ]);
    }

    #[Test]
    public function revoke_valid_invite()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $token = encrypt('valid-token');
        $invite = OrganizationUserInvite::factory()->create([
            'email' => 'invitee@example.com',
            'organization_id' => $organization->id,
            'token' => $token,
            'role' => 'admin',
        ]);
        $this->actingAs($user);
        $response = $this->postJson("/api/access/revoke", [
            'invitation_id' => $invite->id,
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
            ])
            ->assertJson([
                'message' => 'Invitation revoked successfully.',
            ]);

    }

}
