<?php

namespace Feature\Api\V1\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;

    #[Test]
    public function list_organization_returns_a_list_of_organizations()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/organizations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'vat_number',
                        'chamber_of_commerce',
                        'street',
                        'street_number',
                        'city',
                        'postal_code',
                        'country',
                        'billing_email',
                        'billing_details',
                    ],
                ],
            ])
            ->assertJson([
                'message' => 'Organizations retrieved successfully.',
                'data' => [
                    [
                        'id' => $organization->id,
                        'name' => $organization->name,
                        'vat_number' => $organization->vat_number,
                        'chamber_of_commerce' => $organization->chamber_of_commerce,
                        'street' => $organization->street,
                        'street_number' => $organization->street_number,
                        'city' => $organization->city,
                        'postal_code' => $organization->postal_code,
                        'country' => $organization->country,
                        'billing_email' => $organization->billing_email,
                        'billing_details' => $organization->billing_details,
                    ],
                ],
            ]);
    }

    #[Test]
    public function show_organization_returns_a_single_organization()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/organizations/' . $organization->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'vat_number',
                    'chamber_of_commerce',
                    'street',
                    'street_number',
                    'city',
                    'postal_code',
                    'country',
                    'billing_email',
                    'billing_details',
                ],
            ])
            ->assertJson([
                'message' => 'Organization retrieved successfully.',
                'data' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'vat_number' => $organization->vat_number,
                    'chamber_of_commerce' => $organization->chamber_of_commerce,
                    'street' => $organization->street,
                    'street_number' => $organization->street_number,
                    'city' => $organization->city,
                    'postal_code' => $organization->postal_code,
                    'country' => $organization->country,
                    'billing_email' => $organization->billing_email,
                    'billing_details' => $organization->billing_details,
                ],
            ]);
    }

    #[Test]
    public function store_organization_creates_a_new_organization()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'Test Organization',
            'vat_number' => '123456789',
            'chamber_of_commerce' => '987654321',
            'street' => 'Test Street',
            'street_number' => '123',
            'city' => 'Test City',
            'postal_code' => '12345',
            'country' => 'NL',
            'billing_email' => 'billing@example.com',
            'billing_details' => 'Test Billing Details',
        ];
        $response = $this->postJson('/api/organizations', $data);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'vat_number',
                    'chamber_of_commerce',
                    'street',
                    'street_number',
                    'city',
                    'postal_code',
                    'country',
                    'billing_email',
                    'billing_details',
                ],
            ])
            ->assertJson([
                'message' => 'Organization created successfully.',
                'data' => [
                    'name' => $data['name'],
                    'vat_number' => $data['vat_number'],
                    'chamber_of_commerce' => $data['chamber_of_commerce'],
                    'street' => $data['street'],
                    'street_number' => $data['street_number'],
                    'city' => $data['city'],
                    'postal_code' => $data['postal_code'],
                    'country' => $data['country'],
                    'billing_email' => $data['billing_email'],
                    'billing_details' => $data['billing_details'],
                ],
            ]);
    }

    #[Test]
    public function update_organization_updates_an_existing_organization()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'Updated Organization',
            'vat_number' => '987654321',
            'chamber_of_commerce' => '123456789',
            'street' => 'Updated Street',
            'street_number' => '456',
            'city' => 'Updated City',
            'postal_code' => '54321',
            'country' => 'BE',
            'billing_email' => 'billing@example.com',
            'billing_details' => 'Updated Billing Details',
        ];

        $response = $this->putJson('/api/organizations/' . $organization->id, $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'vat_number',
                    'chamber_of_commerce',
                    'street',
                    'street_number',
                    'city',
                    'postal_code',
                    'country',
                    'billing_email',
                    'billing_details',
                ],
            ])
            ->assertJson([
                'message' => 'Organization updated successfully.',
                'data' => [
                    'id' => $organization->id,
                    'name' => $data['name'],
                    'vat_number' => $data['vat_number'],
                    'chamber_of_commerce' => $data['chamber_of_commerce'],
                    'street' => $data['street'],
                    'street_number' => $data['street_number'],
                    'city' => $data['city'],
                    'postal_code' => $data['postal_code'],
                    'country' => $data['country'],
                    'billing_email' => $data['billing_email'],
                    'billing_details' => $data['billing_details'],
                ],
            ]);
    }

    #[Test]
    public function destroy_organization_deletes_an_existing_organization()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson('/api/organizations/' . $organization->id);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
            ])
            ->assertJson([
                'message' => 'Organization deleted successfully.',
                'data' => null,
            ]);

        // Verify that the organization is actually deleted
//        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

}
