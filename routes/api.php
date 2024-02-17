<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/user/{user}', function (Request $request, int $user) {
    return \App\Models\User::withTrashed()->where('id', $user)->get()[0];
});

Route::group([
    'as' => 'api.',
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['auth:sanctum']
], function () {
    Route::apiResource('users', 'UserProfileController')->except('index', 'store');
    Route::delete('users/forceDelete/{user}', 'UserProfileController@forceDelete')
        ->name('users.forceDelete');
    Route::patch('users/restore/{user}', 'UserProfileController@restore')
        ->name('users.restore');
    Route::patch('users/profilePhoto/{user}', 'UserProfileController@profilePhoto')
        ->name('users.profilePhoto');
    Route::delete('users/deleteProfilePhoto/{user}', 'UserProfileController@destroyProfilePhoto')
        ->name('users.deleteProfilePhoto');

    Route::apiResource('posts', 'PostController');
    Route::delete('posts/forceDelete/{post}', 'PostController@forceDelete')
        ->name('posts.forceDelete');
    Route::patch('posts/restore/{post}', 'PostController@restore')
        ->name('posts.restore');
    Route::patch('posts/changePostPhotos/{post}', 'PostController@changePostPhoto')
        ->name('posts.changePostPhotos');
    Route::patch('posts/changeLikePost/{post}', 'PostController@changeLikePost')
        ->name('posts.changeLikePost');

    Route::get('direct', function (Request $request) {
        try {
            $PER_PAGE_ENTRIES = 10;
            $directs = \App\Models\Direct::with('sender')
                ->select('sender_id', 'text', 'seen')
                ->selectRaw('MAX(id) as last_message_id')
                ->where('receiver_id', $request->user()->id)
                ->groupBy('sender_id', 'text', 'seen')
                ->orderByDesc('last_message_id')
                ->paginate($PER_PAGE_ENTRIES);
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error(
                "[Direct Message Socket] Couldn\'t fetch messages from database {$exception->getMessage()}");

            return response()->noContent()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['data' => $directs]);
    })->name('direct.show');
    Route::post('direct', function (\App\Http\Requests\DirectMessageRequest $directMessageRequest) {
        try {
            \App\Models\User::withoutTrashed()->findOrFail($directMessageRequest->receiver_id);
            $direct = \App\Models\Direct::create($directMessageRequest->only('sender_id', 'receiver_id', 'text'));
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error(
                "[Direct Message Socket] Couldn\'t save messages to database {$exception->getMessage()}");

            return response()->noContent()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        broadcast(new \App\Events\NewChatDirectMessage(
            $directMessageRequest->sender_id, $directMessageRequest->receiver_id, $direct->load('sender')))
            ->toOthers();
        return response()->json(['message' => $direct->load('sender')]);
    })->name('direct.post');
    Route::get('direct/{user}', function (Request $request, int $user) {
        try {
            $user = \App\Models\User::withTrashed()->where('id', $user)->get()[0];
            $directs = \App\Models\Direct::with('sender')->where(function($query) use ($request, $user) {
                $query->where('sender_id', $request->user()->id)
                    ->where('receiver_id', $user->id);
            })->orWhere(function($query) use ($request, $user) {
                $query->where('receiver_id', $request->user()->id)
                    ->where('sender_id', $user->id);
            })
            ->get();
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error(
                "[Direct Message Socket] Couldn\'t fetch messages from database {$exception->getMessage()}");

            return response()->noContent()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['messages' => $directs]);
    })->name('direct.user.show');
    Route::patch('direct/{user}', function (Request $request, \App\Models\User $user) {
        try {
            $directs = \App\Models\Direct::where('receiver_id', $request->user()->id)
                ->where('sender_id', $user->id)
                ->update(['seen' => true]);
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error(
                "[Direct Message Socket] Couldn\'t update messages from database {$exception->getMessage()}");

            return response()->noContent()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->noContent()->setStatusCode(Response::HTTP_OK);
    })->name('direct.user.update');
});
