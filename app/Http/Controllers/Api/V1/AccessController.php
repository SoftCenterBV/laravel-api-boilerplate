<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\AcceptUserInvite;
use App\Events\RejectUserInvite;
use App\Events\RevokeUserInvite;
use App\Events\SendUserInvite;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Access\AcceptRequest;
use App\Http\Requests\Api\V1\Access\InviteRequest;
use App\Http\Requests\Api\V1\Access\RejectRequest;
use App\Http\Requests\Api\V1\Access\RevokeRequest;
use App\Http\Resources\BaseApiResource;
use App\Models\OrganizationUserInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AccessController extends Controller
{
    public function list(): JsonResponse
    {
        $invites = OrganizationUserInvite::query()
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return BaseApiResource::makeResponse($invites, 'Invitations retrieved successfully.', 200);
    }
    public function invite(InviteRequest $request): JsonResponse
    {
        $data = $request->validated();
        // generate a unique token based on a random string + email + current timestamp
        $token = Str::random(32) . '-' . md5($data['email']) . '-' . time();
        // Encrypt the token for security
        $encryptedToken = encrypt($token);
        // Store the token in the database
        $invite = OrganizationUserInvite::query()
            ->create([
                'email' => $data['email'],
                'token' => $encryptedToken,
                'organization_id' => $data['organization_id'],
                'invited_by' => auth()->id(),
            ]);

        SendUserInvite::dispatch($invite);

        return BaseApiResource::makeResponse($invite, 'Invitation sent successfully.', 201);
    }

    public function accept(AcceptRequest $request): JsonResponse
    {
        $data = $request->validated();
        $invite = OrganizationUserInvite::query()->where('token', $data['token'])->first();

        if (!$invite || $invite->isAccepted() || $invite->isRejected()) {
            return BaseApiResource::makeResponse(null, 'Invalid or expired invitation token.', 404);
        }

        $invite->update([
            'accepted_at' => now(),
        ]);

        $invite->organization->users()->attach(auth()->id());

        AcceptUserInvite::dispatch($invite);

        return BaseApiResource::makeResponse($invite, 'Invitation accepted successfully.', 200);
    }

    public function reject(RejectRequest $request): JsonResponse
    {
        $data = $request->validated();
        $invite = OrganizationUserInvite::query()
            ->where('token', $data['token'])
            ->first();

        if (!$invite || $invite->isAccepted() || $invite->isRejected()) {
            return BaseApiResource::makeResponse(null, 'Invalid or expired invitation token.', 404);
        }

        $invite->update([
            'rejected_at' => now(),
        ]);

        RejectUserInvite::dispatch($invite);

        return BaseApiResource::makeResponse($invite, 'Invitation rejected successfully.', 200);
    }

    public function revoke(RevokeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $invite = OrganizationUserInvite::query()
            ->where('id', $data['invitation_id'])
            ->delete();

        RevokeUserInvite::dispatch($invite);

        return BaseApiResource::makeResponse($invite, 'Invitation revoked successfully.', 200);
    }


}
