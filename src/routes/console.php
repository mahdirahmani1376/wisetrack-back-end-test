<?php

use App\Services\PostService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function (PostService $postService) {
    $post = \App\Models\Post::find(15);

//    $result = $postService->giveDailyAnalyticsForPost($post);
    $result = $postService->getTopViewPosts();
//    $result =             $postService->giveDailyAnalyticsForPost($post,[
//            'from' => now()->subWeek()->toDateString(),
//            'to' => now()->addWeek()->toDateString(),
//        ]);

    dd($result);
    $analytics = DB::table('post_views')
        ->selectRaw('
                DATE(viewed_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as registered_users,
                COUNT(CASE WHEN user_id IS NULL THEN 1 END) as guest_users,
                AVG(DISTINCT user_id) as average_daily_users
            ')
        ->where('post_id', 15)
        ->groupBy(DB::raw('Date(viewed_at)'))
        ->orderBy(DB::raw('Date(viewed_at)'))
        ->get();

//    $t = DB::table('post_views')
//        ->selectRaw('
//                COUNT(DISTINCT user_id) as unique_users,
//                COUNT(*) as total_views,
//            ')
//        ->where('post_id', 15)
//        ->groupBy(DB::raw('Date(viewed_at)'))
//        ->orderBy(DB::raw('Date(viewed_at)'))
//        ->get();

    $totalViews = $analytics->sum('total_views');

    dd($analytics,$totalViews);
//    $file = UploadedFile::fake()->image('test.png');
//    $data = [
//        'title' => fake()->word(),
//        'content' => fake()->word(),
//        'user_id' => 5,
//        'image_file' => $file
//    ];
//
//    $post = app(PostService::class)->store($data);
//    dd($post->toArray());
//    \Database\Factories\PostFactory::create();
//    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
