<?php

namespace Tests\Unit\Api\V1\App\Http\Requests\Auth;

use App\Http\Requests\Api\V1\Auth\VerifySetupMfaRequest;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerifySetupMfaRequestTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    #[Test]
    public function it_validates_all_data_fields(): void
    {
        $data = [
            'code' => '123456',
        ];

        $event = new VerifySetupMfaRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_fails_on_empty_data(): void
    {
        $data = [];

        $event = new VerifySetupMfaRequest($data);
        $rules = $event->rules();

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('code', $validator->errors()->toArray());
    }
    #[Test]
    public function authorize_returns_true()
    {
        $request = new VerifySetupMfaRequest();

        $this->assertTrue($request->authorize());
    }

}
