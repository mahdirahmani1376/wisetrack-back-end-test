<?php

namespace App\Services;

use App\Http\Resources\PostTopViewResource;
use App\Models\Post;
use App\Models\PostView;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function getPaginatedResults()
    {
        return Post::with('author')
            ->withCount('views')
            ->paginate(15);
    }

    public function store(array $data)
    {
        $post = Post::create([
            'user_id' => data_get($data, 'user_id'),
            'title' => data_get($data, 'title'),
            'content' => data_get($data, 'content'),
        ]);

        if (!empty($data['image_file'])) {
            /** @var UploadedFile $imageFile */
            $imageFile = $data['image_file'];

            $storageResult = Storage::disk('public')->putFileAs(
                "/uploads/posts-images/{$post->id}",
                $imageFile,
                $imageFile->getFilename()
            );

            if ($storageResult) {
                $post->update([
                    'image' => $storageResult
                ]);
            }
        }

        return $post;
    }

    public function topViewed(array $filters = []): array
    {
        $limit = $filters['limit'] ?? 10;

        $data = Post::getTopViews([
            'limit' => $limit
        ]);

        $meta = [
            "total_posts_analyzed" => Post::count(),
            "period_days" => PostView::getPeriodDays(),
            "average_views_per_post" => PostView::getAverageViewsPerPost()
        ];

        return [
            'data' => PostTopViewResource::collection($data),
            'meta' => $meta
        ];
    }

}
