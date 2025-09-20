<?php

namespace Tests\Unit\Api\V1\App\Http\Resources;

use App\Http\Resources\BaseApiResource;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BaseApiResourceTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;
    #[Test]
    public function it_make_response_with_normal_data()
    {
        $data = ['foo' => 'bar'];
        $message = 'Success';

        $response = BaseApiResource::makeResponse($data, $message);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $responseData = $response->getData(true);

        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($data, $responseData['data']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayNotHasKey('pagination', $responseData);
    }

    #[Test]
    public function it_make_response_with_pagination_data()
    {
        Organization::factory(10)->create();
        $data = Organization::query()->paginate(2, ['*'], 'page', 1); // Simulating pagination with 2 items per page, on page 1
        $message = 'Paginated Success';

        $response = BaseApiResource::makeResponse($data, $message);

        $responseData = $response->getData(true);


        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals(collect($data->items())->map->toArray()->all(), $responseData['data']);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertEquals($data->currentPage(), $responseData['pagination']['current_page']);
        $this->assertEquals($data->lastPage(), $responseData['pagination']['last_page']);
        $this->assertEquals($data->perPage(), $responseData['pagination']['per_page']);
        $this->assertEquals($data->total(), $responseData['pagination']['total']);

    }

    //    #[Test]
    //    public function it_should_not_return_pagination_when_not_needed()
    //    {
    //        Organization::factory(10)->create();
    //        $data = Organization::query()->paginate(15, ['*'], 'page', 1); // Simulating pagination with 2 items per page, on page 1
    //        $message = 'Paginated Success';
    //
    //        $response = BaseApiResource::makeResponse($data, $message);
    //
    //        $responseData = $response->getData(true);
    //
    //
    //        $this->assertEquals($message, $responseData['message']);
    //        $this->assertEquals(collect($data->items())->map->toArray()->all(), $responseData['data']);
    //        $this->assertEquals(200, $response->getStatusCode());
    //    }
}
