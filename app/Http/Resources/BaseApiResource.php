<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;

class BaseApiResource extends JsonResource
{
    public static function makeResponse($data, string $message, int $status = 200): JsonResponse
    {
        $response = [
            'message' => $message,
            'data' => $data
        ];

        if ($data instanceof AbstractPaginator) {
            $response['pagination'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];

            $response['links'] = [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ];

            $response['data'] = $data->items();
        }

        return response()->json($response, $status);
    }

}
