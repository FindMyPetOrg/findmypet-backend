<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPhotoRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserProfileController extends Controller
{
    public function update(User $user, UpdateUserRequest $updateUserRequest): \Illuminate\Http\JsonResponse
    {
        try
        {
            $user->update([
                'name' => $updateUserRequest->post('name', $user->name),
                'email' => $updateUserRequest->post('email', $user->email),
                'nickname' => $updateUserRequest->post('nickname', $user->nickname),
                'is_admin' => $updateUserRequest->post('is_admin', $user->is_admin),
                'is_verified' => $updateUserRequest->post('is_verified', $user->is_verified),
                'address' => $updateUserRequest->post('address', $user->address),
                'phone' => $updateUserRequest->post('address', $user->phone),
                'description' => $updateUserRequest->post('description', $user->description),
            ]);
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'result' => 'error',
                'data' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'result' => 'success',
            'data' => []
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin)
            {
                throw new \Exception("You are not an administrator.");
            }

            $user->delete();
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'result' => 'error',
                'data' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'result' => 'success',
            'data' => []
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'result' => 'success',
            'data' => $user
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function forceDelete(User $user): \Illuminate\Http\JsonResponse
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin)
            {
                throw new \Exception("You are not an administrator.");
            }

            $user->forceDelete();
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'result' => 'error',
                'data' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'result' => 'success',
            'data' => []
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function restore(User $user)
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin)
            {
                throw new \Exception("You are not an administrator.");
            }

            $user->restore();
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'result' => 'error',
                'data' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'result' => 'success',
            'data' => []
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function profilePhoto(User $user, UpdateUserPhotoRequest $updateUserPhotoRequest)
    {
        try
        {
            $user->avatar = bin2hex(random_bytes(16)) .
            "profilePhoto_" . Str::slug($user->name) .
            ".{$updateUserPhotoRequest->avatar->clientExtension()}";

            $user->save();
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'result' => 'error',
                'data' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'result' => 'success',
            'data' => []
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function destroyProfilePhoto(User $user)
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin && $user->id != $authenticated_user->id)
            {
                throw new \Exception("You don't have permission to do this action.");
            }

            $user->avatar = null;
            $user->save();
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'result' => 'error',
                'data' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'result' => 'success',
            'data' => []
        ])->setStatusCode(Response::HTTP_OK);
    }
}
