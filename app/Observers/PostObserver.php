<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    public function updating(Post $post): void
    {
        if ($post->isDirty('images')) {
            $original_images = $post->getOriginal('images');

            $storage_disk = Storage::disk('public');
            if ($original_images != null && $storage_disk->exists("posts/{$post->id}")) {
                $storage_disk->deleteDirectory("posts/{$post->id}");
            }

            $request = request();
            collect(array_map(null, $post->images, $request->only('images')))
                ->each(function ($element) use ($storage_disk, $post) {
                    $storage_disk->putFileAs("posts/{$post->id}", $element[1], $element[0]);
                });
        }
    }

    public function forceDeleted(Post $post): void
    {
        $storage_disk = Storage::disk('public');
        if ($storage_disk->exists("posts/{$post->id}")) {
            $storage_disk->deleteDirectory("posts/{$post->id}");
        }
    }
}
