<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Organization\StoreOrganizationRequest;
use App\Http\Requests\Api\V1\Organization\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;

class OrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        $organizations = Organization::query()
            ->orderBy('name')
            ->paginate();

        return response()->json($organizations);
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->json($organization);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();
        $organization = Organization::query()->create($data);
        return response()->json($organization, 201);
    }

    public function update(Organization $organization, UpdateOrganizationRequest $request): JsonResponse
    {
        $organization->fill($request->validated());
        // return object if save is successful, otherwise return the object with validation errors
        if ($organization->save()) {
            return response()->json($organization);
        }
        return response()->json([
            'message' => 'Failed to update organization',
            'errors' => $organization->getErrors()
        ], 422);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();
        return response()->json([
            'message' => 'Organization deleted successfully'
        ]);
    }
}
