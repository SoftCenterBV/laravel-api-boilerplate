<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseApiResource;
use App\Models\OrganizationUserInvite;

class OrganizationUserController extends Controller
{
    public function pendingInvites()
    {
        $invites = OrganizationUserInvite::query()
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return BaseApiResource::makeResponse($invites, 'Pending invitations retrieved successfully.', 200);
    }

    public function list()
    {

        return BaseApiResource::makeResponse([], 'Organization users retrieved successfully.', 200);
    }

    public function delete()
    {
        // Implementation for deleting an organization user
        return BaseApiResource::makeResponse(null, 'Organization user deleted successfully.', 200);
    }

}
