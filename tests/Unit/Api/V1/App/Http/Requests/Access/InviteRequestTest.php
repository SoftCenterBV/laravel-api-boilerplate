<?php

namespace Tests\Unit\Api\V1\App\Http\Requests\Access;

use App\Http\Requests\Api\V1\Access\InviteRequest;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InviteRequestTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    #[Test]
    public function it_validates_required_fields(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'email' => 'test@stevenhooisma.nl',
            'organization_id' => $organization->id,
        ];

        $event = new InviteRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_fails_on_empty_data(): void
    {
        $data = [
        ];

        $event = new InviteRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('organization_id', $validator->errors()->toArray());
    }
    #[Test]
    public function authorize_returns_true()
    {
        $request = new InviteRequest();

        $this->assertTrue($request->authorize());
    }

}
