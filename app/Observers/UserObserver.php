<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserObserver
{
    public function updating(User $user): void
    {
        if ($user->isDirty('avatar')) {
            $original_avatar = $user->getOriginal('avatar');

            $array = explode('/', $original_avatar);
            $original_avatar = end($array);

            $storage_disk = Storage::disk('public');
            if ($original_avatar != null && !Str::contains($original_avatar, 'https')) {
                if ($storage_disk->exists("users/{$user->id}/{$original_avatar}")) {
                    $storage_disk->delete("users/{$user->id}/{$original_avatar}");
                }
            }

            if (!$storage_disk->exists("users/{$user->id}")) {
                $storage_disk->makeDirectory("users/{$user->id}");
            }

            if (request()->has('file')) {
                $storage_disk->putFileAs("users/{$user->id}", request()->only('file')["file"], $user->avatar);

                $user->avatar = env('APP_URL') . "/storage/users/{$user->id}/" . $user->avatar;
            }
        }
    }

    public function forceDeleted(User $user): void
    {
        $storage_disk = Storage::disk('public');
        if ($storage_disk->exists("users/{$user->id}")) {
            $storage_disk->deleteDirectory("users/{$user->id}");
        }
    }
}
