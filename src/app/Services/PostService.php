<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public static function userCanAccessPost(Post $post, User $user): bool
    {
        $post = Post::firstWhere([
            'id' => $post->id,
            'user_id' => $user->id
        ]);

        return !empty($post);
    }

    public function giveDailyAnalyticsForPost(Post $post, array $filters = []): array
    {
        $responseData = [];
        $from = data_get($filters, 'from');
        $to = data_get($filters, 'to');

        $responseData['data']['post_id'] = $post->id;
        $responseData['data']['title'] = $post->title;

        $analytics = DB::table('post_views')
            ->selectRaw('
                DATE(viewed_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as registered_users,
                COUNT(CASE WHEN user_id IS NULL THEN 1 END) as guest_users
            ')
            ->where('post_id', $post->id)
            ->when(!empty($from), function ($q) use ($from) {
                $q->where('created_at', '>=', $from);
            })
            ->when(!empty($to), function ($q) use ($to) {
                $q->where('created_at', '<', $to);
            })
            ->groupBy(DB::raw('Date(viewed_at)'))
            ->orderBy(DB::raw('Date(viewed_at)'))
            ->get();


        $responseData['data']['period'] = [
            'from' => $from,
            'to' => $to
        ];

        $totalDays = (int)floor(Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($to)->endOfDay()));
        $totalViews = $analytics->sum('total_views');

        $totalUniqueUsers = PostView::query()
            ->where('post_id', $post->id)
            ->when(!empty($from), function ($q) use ($from) {
                $q->where('created_at', '>=', $from);
            })
            ->when(!empty($to), function ($q) use ($to) {
                $q->where('created_at', '<', $to);
            })
            ->distinct('user_id')
            ->count('user_id');


        $averageDailyUsers = (int)round($analytics->avg('unique_users'), 0);
        $averageDailyViews = (int)round($analytics->avg('total_views'), 0);
        $peakDay = $analytics->sortByDesc('unique_users')->first()?->date;
        $peakUsers = $analytics->sortByDesc('unique_users')->first()?->unique_users;

        $firstDayViews = $analytics->sortBy(function ($item) {
            return strtotime($item->date);
        })?->first()?->total_views;

        $trend = $averageDailyViews > $firstDayViews ? 'uptrend' : 'downtrend';
        if (!$firstDayViews) {
            $trendPercentage = 0;
        } else {
            $trendPercentage = (($averageDailyViews - $firstDayViews) / $firstDayViews) * 100;
        }

        $responseData['data']['analytics'] = $analytics;
        $responseData['meta'] = [
            "total_days" => $totalDays,
            "total_unique_users" => $totalUniqueUsers,
            "total_views" => $totalViews,
            "average_daily_users" => $averageDailyUsers,
            "peak_day" => $peakDay,
            "peak_users" => $peakUsers,
            "trend" => $trend,
            "trend_percentage" => $trendPercentage
        ];

        return $responseData;
    }

    public function giveSummaryAnalytics(Post $post): array
    {
        $responseData = [];

        $responseData['data']['post_id'] = $post->id;
        $responseData['data']['title'] = $post->title;

        $analytics = DB::table('post_views')
            ->selectRaw('
                DATE(viewed_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as registered_users,
                COUNT(CASE WHEN user_id IS NULL THEN 1 END) as guest_users
            ')
            ->where('post_id', $post->id)
            ->groupBy(DB::raw('Date(viewed_at)'))
            ->orderBy(DB::raw('Date(viewed_at)'))
            ->get();


        $totalDays = count($analytics);
        $totalViews = $analytics->sum('total_views');

        $totalUniqueUsers = PostView::query()
            ->where('post_id', $post->id)
            ->distinct('user_id')
            ->count('user_id');


        $averageDailyUsers = (int)round($analytics->avg('unique_users'), 0);
        $averageDailyViews = (int)round($analytics->avg('total_views'), 0);
        $peakDay = $analytics->sortByDesc('unique_users')->first()?->date;
        $peakUsers = $analytics->sortByDesc('unique_users')->first()?->unique_users;

        $firstDayViews = $analytics->sortBy(function ($item) {
            return strtotime($item->date);
        })?->first()?->total_views;

        $trend = $averageDailyViews > $firstDayViews ? 'uptrend' : 'downtrend';
        if (!$firstDayViews) {
            $trendPercentage = 0;
        } else {
            $trendPercentage = (($averageDailyViews - $firstDayViews) / $firstDayViews) * 100;
        }

        $responseData['data']['analytics'] = $analytics;
        $responseData['data']['meta'] = [
            "total_days" => $totalDays,
            "total_unique_users" => $totalUniqueUsers,
            "total_views" => $totalViews,
            "average_daily_users" => $averageDailyUsers,
            "peak_day" => $peakDay,
            "peak_users" => $peakUsers,
            "trend" => $trend,
            "trend_percentage" => $trendPercentage
        ];

        return $responseData;
    }

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
            $path = storage_path("app/public/uploads/posts-images/{$post->id}");

            $storageResult = Storage::putFileAs(
                $path,
                $imageFile,
                $imageFile->getFilename()
            );

            if ($storageResult) {
                $post->update([
                    'image' => $path
                ]);
            }
        }

        return $post;
    }

    public function getTopViewPosts(array $filters = [])
    {
        $limit = $filters['limit'] ?? 10;

        $views = PostView::query()
            ->selectRaw('
                post_id,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users
            ')
            ->groupBy('post_id');

        $data = Post::query()
            ->with('author:id,name')
            ->selectRaw('
                posts.*,
                views.total_views,
                views.unique_users
                ')
            ->leftJoinSub($views, 'views', function ($join) {
                $join->on('posts.id', '=', 'views.post_id');
            })
            ->orderByDesc('views.total_views')
            ->limit($limit)
            ->get()
            ->each(fn(Post $post, int $key) => $post->setAttribute('rank',$key +1));

        return $data;
    }

    public function getTopViewPostsMetaData()
    {
        $nearestTime = PostView::query()->latest('viewed_at')->first()->viewed_at;
        $furthestTime = PostView::query()->oldest('viewed_at')->first()->viewed_at;
        if (!$nearestTime || !$furthestTime) {
            $periodDays = 0;
        } else {
            $periodDays = (int)floor($furthestTime->diffInDays($nearestTime));
        }

        $views = PostView::query()
            ->selectRaw('
                post_id,
                COUNT(*) as total_views
            ')
            ->groupBy('post_id')
            ->get();


        return [
            "total_posts_analyzed" => Post::count(),
            "period_days" => $periodDays,
            "average_views_per_post" => $views->average('total_views'),
        ];
    }
}
