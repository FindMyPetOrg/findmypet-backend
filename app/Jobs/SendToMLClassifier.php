<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendToMLClassifier implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
        ])
        ->withBody(json_encode([
            'URL_img' => Str::replace(env('APP_URL'), env('FILE_SERVICE_URL'), json_decode($this->post->images)[0])
        ]))->post(env('ML_SERVICE_LAMBDA'));

        $response = json_decode($response->body());
        $this->post->title = "[{$response->class_name}] {$this->post->title}";
        $this->post->save();
    }
}
