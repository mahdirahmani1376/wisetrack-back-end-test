<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostDailyAnalyticsRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\TopViewPostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostTopViewResource;
use App\Models\Post;
use App\Services\PostService;
use App\Services\PostViewService;
use Illuminate\Http\Request;

class PostController
{
    public function index(PostService $postService)
    {
        return response()->json(
            PostResource::collection(
                $postService->getPaginatedResults()
            )
        );
    }

    public function store(StorePostRequest $request, PostService $postService)
    {
        $validatedData = $request->validated();
        $validatedData['image_file'] = $request->file('image');
        $validatedData['user_id'] = auth()->id();

        return response()->json(
            PostResource::make(
                $postService->store($validatedData)
            )
        );
    }

    public function show(Post $post, Request $request, PostViewService $postViewService)
    {
        $data = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'post_id' => $post->id,
            'viewed_at' => now()
        ];

        $postViewService->create($data);

        return response()->json(
            PostResource::make(
                $post
            )
        );
    }

    public function dailyAnalytics(Post $post, PostDailyAnalyticsRequest $request, PostService $postService)
    {
        return response()->json(
            $postService->giveDailyAnalyticsForPost($post, $request->validated())
        );
    }

    public function AnalyticsSummary(Post $post, PostService $postService)
    {
        return response()->json(
            $postService->giveSummaryAnalytics($post)
        );
    }

    public function topViewed(TopViewPostRequest $request, PostService $postService)
    {
        return PostTopViewResource::collection(
            $postService->getTopViewPosts($request->validated())
        )->additional(['meta' => $postService->getTopViewPostsMetaData()]);
    }
}
