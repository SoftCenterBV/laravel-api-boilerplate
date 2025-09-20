<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Organization\StoreOrganizationRequest;
use App\Http\Requests\Api\V1\Organization\UpdateOrganizationRequest;
use App\Http\Resources\BaseApiResource;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;

class OrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        $organizations = Organization::query()
            ->orderBy('name')
            ->paginate();

        return BaseApiResource::makeResponse($organizations, 'Organizations retrieved successfully.', 200);
    }

    public function show(Organization $organization): JsonResponse
    {
        return BaseApiResource::makeResponse($organization, 'Organization retrieved successfully.', 200);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();
        $organization = Organization::query()->create($data);
        return BaseApiResource::makeResponse($organization, 'Organization created successfully.', 201);
    }

    public function update(Organization $organization, UpdateOrganizationRequest $request): JsonResponse
    {
        $organization->fill($request->validated());
        return BaseApiResource::makeResponse($organization, 'Organization updated successfully.', 200);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();
        return BaseApiResource::makeResponse(null, 'Organization deleted successfully.', 200);
    }
}
