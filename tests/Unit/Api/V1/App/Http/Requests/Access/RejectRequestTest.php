<?php

namespace Tests\Unit\Api\V1\App\Http\Requests\Access;

use App\Http\Requests\Api\V1\Access\RejectRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RejectRequestTest extends TestCase
{
    #[Test]
    public function it_validates_required_fields(): void
    {
        $data = [
            'token' => 'some-valid-token', // Uncomment to test valid token
        ];

        $event = new RejectRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_fails_on_empty_data(): void
    {
        $data = [
        ];

        $event = new RejectRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('token', $validator->errors()->toArray());
    }

    #[Test]
    public function authorize_returns_true()
    {
        $request = new RejectRequest();

        $this->assertTrue($request->authorize());
    }

}
