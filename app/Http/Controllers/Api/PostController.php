<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostPhotoRequest;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        try
        {
            $posts = Post::all();
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
            'data' => $posts
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function show(Post $post)
    {
        return response()->json([
            'result' => 'success',
            'data' => $post
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function edit(Post $post, PostRequest $postRequest)
    {
        try
        {
            $post->update(
                $postRequest->only('user_id', 'lat', 'lng', 'title', 'description', 'reward', 'type', 'tags')
            );
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
            'data' => $post
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function store(PostRequest $postRequest)
    {
        try
        {
            $post = Post::create(
                [
                    'user_id' => auth()->id(),
                    'lat' => $postRequest->lat,
                    'lng' => $postRequest->lang,
                    'title' => $postRequest->title,
                    'description' => $postRequest->description,
                    'reward' => $postRequest->reward,
                    'type' => $postRequest->type,
                    'tags' => json_encode($postRequest->tags),
                    'images' => []
                ]
            );
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

    public function delete(Post $post)
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin && $authenticated_user->id !== $post->user->id)
            {
                throw new \Exception("You don't have permissions to do that.");
            }

            $post->delete();
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

    public function restore(Post $post)
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin && $authenticated_user->id !== $post->user->id)
            {
                throw new \Exception("You don't have permissions to do that.");
            }

            $post->restore();
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

    public function forceDelete(Post $post)
    {
        try
        {
            $authenticated_user = Auth::user();
            if (! $authenticated_user->is_admin)
            {
                throw new \Exception("You are not an administrator.");
            }

            $post->forceDelete();
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

    public function changePostPhoto(Post $post, PostPhotoRequest $postPhotoRequest)
    {
        try
        {
            $post->images = Arr::map($postPhotoRequest->images, function ($image) use ($post) {
                return bin2hex(random_bytes(16)) .
                    "postPhoto_" . Str::slug($post->id) .
                    ".{$image->clientExtension()}";
            });
            $post->save();
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
            'data' => $post
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function changeLikePost(Post $post)
    {
        try
        {
            if (! Auth::check())
            {
                throw new \Exception("You can not like or unlike a post beacuse you are not logged in.");
            }
            $authenticated_user = Auth::user();

            if ($authenticated_user->posts()->where('post_id', $post->id)->exists()) {
                $authenticated_user->posts()->detach($post->id);
            } else {
                $authenticated_user->posts()->attach($post->id);
            }
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
            'data' => $post
        ])->setStatusCode(Response::HTTP_OK);
    }
}
