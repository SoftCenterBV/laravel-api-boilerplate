<?php

namespace Tests\Unit\Api\V1\App\Http\Requests\Access;

use App\Http\Requests\Api\V1\Access\RevokeRequest;
use App\Models\OrganizationUserInvite;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RevokeRequestTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    #[Test]
    public function it_validates_required_fields(): void
    {
        $data = OrganizationUserInvite::factory()->create();
        $data = [
            'invitation_id' => $data->id,
        ];

        $event = new RevokeRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_fails_on_empty_data(): void
    {
        $data = [];

        $event = new RevokeRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('invitation_id', $validator->errors()->toArray());
    }

    #[Test]
    public function it_fails_on_wrong_invitation_id(): void
    {
        $data = [
            'invitation_id' => '7',
        ];

        $event = new RevokeRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function authorize_returns_true()
    {
        $request = new RevokeRequest();

        $this->assertTrue($request->authorize());
    }

}
