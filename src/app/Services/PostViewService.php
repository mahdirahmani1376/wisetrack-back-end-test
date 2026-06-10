<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostView;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PostViewService
{

    public function create(array $data): PostView
    {
        if ($data['user_id']) {
            $userPostViewToday = PostView::firstWhere([
                'post_id' => data_get($data,'post_id'),
                'user_id' => data_get($data,'user_id'),
                'viewed_at' => now()->toDateString()
            ]);
        } else {
            $userPostViewToday = PostView::firstWhere([
                'post_id' => data_get($data,'post_id'),
                'ip_address' => data_get($data,'ip'),
                'user_id' => null
            ]);
        }

        if (!$userPostViewToday) {
            $userPostViewToday = PostView::create([
                'post_id' => data_get($data,'post_id'),
                'user_id' => data_get($data,'user_id'),
                'ip_address' => data_get($data,'ip_address'),
                'user_agent' => data_get($data,'user_agent'),
                'viewed_at' => now()
            ]);
        }

        return $userPostViewToday;
    }

    public function giveDailyAnalyticsForPost(Post $post, array $filters = []): array
    {
        $responseData = [];
        $from = data_get($filters, 'from');
        $to = data_get($filters, 'to');

        $responseData['post_id'] = $post->id;
        $responseData['title'] = $post->title;
        $responseData['period'] = [
            'from' => $from,
            'to' => $to
        ];

        $analytics = DB::table('post_views')
            ->selectRaw('
                DATE(viewed_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as registered_users,
                COUNT(CASE WHEN user_id IS NULL THEN 1 END) as guest_users
            ')
            ->where('post_id', $post->id)
            ->when(!empty($from), function (Builder $q) use ($from) {
                $q->where('created_at', '>=', $from);
            })
            ->when(!empty($to), function (Builder $q) use ($to) {
                $q->where('created_at', '<', $to);
            })
            ->whereBetween('viewed_at', [$from, $to])
            ->groupBy(DB::raw('Date(viewed_at)'))
            ->orderBy(DB::raw('Date(viewed_at)'))
            ->get();

        $totalDays = (int)Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($to)->endOfDay()) + 1;
        $totalViews = $analytics->sum('total_views');


        $totalUniqueUsers = PostView::query()
            ->where('post_id', $post->id)
            ->when(!empty($from), function (Builder $q) use ($from) {
                $q->where('created_at', '>=', $from);
            })
            ->when(!empty($to), function (Builder $q) use ($to) {
                $q->where('created_at', '<', $to);
            })
            ->distinct('user_id')
            ->count('user_id');


        $averageDailyUsers = (int)round($analytics->avg('unique_users'), 0);
        $averageDailyViews = (int)round($analytics->avg('total_views'), 0);
        $peakDay = $analytics->sortByDesc('unique_users')->first()?->date;
        $peakUsers = $analytics->sortByDesc('unique_users')->first()?->unique_users;

        $firstDayViews = $analytics->sort('date')->first()?->total_views;

        $trend = $averageDailyViews > $firstDayViews ? 'uptrend' : 'downtrend';
        $trendPercentage = (($averageDailyViews - $firstDayViews) / $firstDayViews) * 100;

        $responseData['analytics'] = $analytics;
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
}
