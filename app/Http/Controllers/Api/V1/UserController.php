<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ExportUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\BaseApiResource;
use App\Models\User;

class UserController extends Controller
{

    public function me()
    {
        return BaseApiResource::makeResponse(auth()->user(), 'User retrieved successfully.', 200);
    }

    public function update(User $user, UpdateUserRequest $request){
        $user->fill($request->validated());
        $user->save();
        return BaseApiResource::makeResponse($user, 'User updated successfully.', 200);
    }

    public function destroy()
    {
        $user = auth()->user();
        $user->delete();
        return BaseApiResource::makeResponse(null, 'User deleted successfully.', 200);
    }

    public function exportData()
    {
        $user = auth()->user();
        ExportUserData::dispatch($user);
        return BaseApiResource::makeResponse(null, 'Data will be prepared. This can take a while.', 200);
    }

}
