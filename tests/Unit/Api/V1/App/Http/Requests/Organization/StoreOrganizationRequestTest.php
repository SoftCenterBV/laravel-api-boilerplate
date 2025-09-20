<?php

namespace Tests\Unit\Api\V1\App\Http\Requests\Organization;

use App\Http\Requests\Api\V1\Organization\StoreOrganizationRequest;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreOrganizationRequestTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    public function correctData(): array
    {
        $organization = Organization::factory()->create();
        return [
            'name' => $organization->name, // Uncomment to test valid token
            'vat_number' => $organization->vat_number,
            'chamber_of_commerce' => $organization->chamber_of_commerce,
            'street' => $organization->street,
            'street_number' => $organization->street_number,
            'city' => $organization->city,
            'postal_code' => $organization->postal_code,
            'country' => $organization->country,
            'billing_email' => $organization->billing_email,
            'billing_details' => $organization->billing_details,
            'metadata' => ['key1' => 'value1', 'key2' => 'value2']
        ];
    }
    #[Test]
    public function it_validates_all_data_fields(): void
    {
        $data = $this->correctData();

        $event = new StoreOrganizationRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_fails_on_empty_data(): void
    {
        $data = [];

        $event = new StoreOrganizationRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('street', $validator->errors()->toArray());
        $this->assertArrayHasKey('street_number', $validator->errors()->toArray());
        $this->assertArrayHasKey('city', $validator->errors()->toArray());
        $this->assertArrayHasKey('postal_code', $validator->errors()->toArray());
        $this->assertArrayHasKey('country', $validator->errors()->toArray());
    }
    #[Test]
    public function authorize_returns_true()
    {
        $request = new StoreOrganizationRequest();

        $this->assertTrue($request->authorize());
    }

}
