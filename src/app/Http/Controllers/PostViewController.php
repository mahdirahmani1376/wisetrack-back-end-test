<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostDailyAnalyticsRequest;
use App\Models\Post;
use App\Services\PostViewService;

class PostViewController
{
    public function dailyAnalytics(Post $post, PostDailyAnalyticsRequest $request, PostViewService $postViewService)
    {
        return response()->json(
            $postViewService->getAnalyticsForPost($post, $request->validated())
        );
    }

    public function AnalyticsSummary(Post $post, PostViewService $postViewService)
    {
        return response()->json(
            $postViewService->giveSummaryAnalytics($post)
        );
    }
}
