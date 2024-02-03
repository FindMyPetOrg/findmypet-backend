<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    public function updating(User $user): void
    {
        if ($user->isDirty('avatar')) {
            $original_avatar = $user->getOriginal('avatar');

            $storage_disk = Storage::disk('public');
            if ($original_avatar != null && $storage_disk->exists("users/{$user->id}/{$original_avatar}")) {
                $storage_disk->delete("users/{$user->id}/{$original_avatar}");
            }

            $request = request();
            if ($user->avatar == null) {
                $storage_disk->putFileAs("users/{$user->id}", $request->only('avatar'), $user->avatar);
            }
        }
    }

    public function forceDeleted(User $user): void
    {
        $storage_disk = Storage::disk('public');
        if ($storage_disk->exists("users/{$user->id}/{$user->avatar}")) {
            $storage_disk->delete("users/{$user->id}/{$user->avatar}");
        }
    }
}
