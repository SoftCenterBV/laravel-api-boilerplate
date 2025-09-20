<?php

namespace Tests\Unit\Api\V1\App\Http\Requests\Access;

use App\Http\Requests\Api\V1\Access\AcceptRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AcceptRequestTest extends TestCase
{
    #[Test]
    public function it_validates_required_fields(): void
    {
        $data = [
            'token' => 'some-valid-token',
        ];

        $event = new AcceptRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_fails_on_empty_data(): void
    {
        $data = [];

        $event = new AcceptRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('token', $validator->errors()->toArray());
    }
    #[Test]
    public function authorize_returns_true()
    {
        $request = new AcceptRequest();

        $this->assertTrue($request->authorize());
    }

}
