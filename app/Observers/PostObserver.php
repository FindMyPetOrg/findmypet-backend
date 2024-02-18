<?php

namespace App\Observers;

use App\Jobs\SendToMLClassifier;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostObserver
{
    public function created(Post $post): void
    {
        if (request()->has('images')) {
            $images = request()->only('images')['images'];

            $storage_disk = Storage::disk('public');

            if ($storage_disk->exists("posts/{$post->id}")) {
                $storage_disk->deleteDirectory("posts/{$post->id}");
            }

            if (!$storage_disk->exists("posts/{$post->id}")) {
                $storage_disk->makeDirectory("posts/{$post->id}");
            }

            $links = [];
            foreach($images as $image) {
                $generated_name = bin2hex(random_bytes(16)) .
                    "postPhoto_" . Str::slug($post->id) .
                    ".{$image->clientExtension()}";


                $storage_disk->putFileAs("posts/{$post->id}", $image, $generated_name);

                $links[] = env('APP_URL') . "/storage/posts/{$post->id}/" . $generated_name;
            }
            $post->images = json_encode($links);
            $post->save();

            dispatch(new SendToMLClassifier($post));
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
