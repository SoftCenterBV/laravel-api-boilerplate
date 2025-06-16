<?php

namespace Tests\Feature\Api\V1\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;
    public function test_returns_paginated_organizations_ordered_by_name()
    {
        // Arrange: maak eerst een geldige user aan
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);

        // Maak organisaties met geldige owner_id
        Organization::factory()->create([
            'name' => 'Beta Org',
            'owner_id' => $owner->id,
            'parent_id' => null,
        ]);
        Organization::factory()->create([
            'name' => 'Alpha Org',
            'owner_id' => $owner->id,
            'parent_id' => null,
        ]);
        Organization::factory()->create([
            'name' => 'Gamma Org',
            'owner_id' => $owner->id,
            'parent_id' => null,
        ]);

        // Act: call de API
        $response = $this->getJson('/api/organizations');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
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
                        'metadata',
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active',
                    ],
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);

        $data = $response->json('data');
        $this->assertEquals('Alpha Org', $data[0]['name']);
        $this->assertEquals('Beta Org', $data[1]['name']);
        $this->assertEquals('Gamma Org', $data[2]['name']);
    }
}
