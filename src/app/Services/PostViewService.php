<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostView;
use Illuminate\Support\Carbon;

class PostViewService
{

    public function create(array $data): PostView
    {
        if ($data['user_id']) {
            $userPostViewToday = PostView::query()
                ->where('post_id', data_get($data, 'post_id'))
                ->where('user_id', data_get($data, 'user_id'))
                ->whereDate('viewed_at', Carbon::create($data['viewed_at'] ?? now())->toDateString())
                ->first();

        } else {
            $userPostViewToday = PostView::query()->where([
                'post_id' => data_get($data,'post_id'),
                'ip_address' => data_get($data,'ip'),
                'user_agent' => data_get($data, 'user_agent'),
                'user_id' => null
            ])->first();
        }

        if (!$userPostViewToday) {
            $userPostViewToday = PostView::create([
                'post_id' => data_get($data,'post_id'),
                'user_id' => data_get($data,'user_id'),
                'ip_address' => data_get($data,'ip_address'),
                'user_agent' => data_get($data,'user_agent'),
                'viewed_at' => data_get($data, 'viewed_at', now()),
            ]);
        }

        return $userPostViewToday;
    }

    public function getAnalyticsForPost(Post $post, array $filters = []): array
    {
        $from = data_get($filters, 'from');
        $to = data_get($filters, 'to');
        $totalDays = (int)floor(Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($to)->endOfDay()));

        $filters = [
            ...$filters,
            'post_id' => $post->id
        ];

        $analytics = PostView::getAnalytics($filters);
        $totalUniqueUsers = PostView::getCountOfUniqueUsers();
        $totalViews = $analytics->sum('total_views');

        $averageDailyUsers = (int)round($analytics->avg('unique_users'), 0);
        $averageDailyViews = (int)round($analytics->avg('total_views'), 0);
        $peakDay = $analytics->sortByDesc('unique_users')->first()?->date;
        $peakUsers = $analytics->sortByDesc('unique_users')->first()?->unique_users;

        $firstDayViews = $analytics->last()->total_views;

        $trend = $averageDailyViews > $firstDayViews ? 'uptrend' : 'downtrend';
        if (!$firstDayViews) {
            $trendPercentage = 0;
        } else {
            $trendPercentage = (($averageDailyViews - $firstDayViews) / $firstDayViews) * 100;
        }

        return [
            'data' => [
                'post_id' => $post->id,
                'title' => $post->title,
                'analytics' => $analytics,
                'period' => [
                    'from' => $from,
                    'ro' => $to,
                ]
            ],
            'meta' => [
                "total_days" => $totalDays,
                "total_unique_users" => $totalUniqueUsers,
                "total_views" => $totalViews,
                "average_daily_users" => $averageDailyUsers,
                "peak_day" => $peakDay,
                "peak_users" => $peakUsers,
                "trend" => $trend,
                "trend_percentage" => $trendPercentage
            ]
        ];
    }

    public function giveSummaryAnalytics(Post $post): array
    {
        $filters = [
            'post_id' => $post->id
        ];

        $analytics = PostView::getAnalytics($filters);
        $totalUniqueUsers = PostView::getCountOfUniqueUsers();
        $totalViews = $analytics->sum('total_views');
        $totalDays = count($analytics);


        $averageDailyUsers = (int)round($analytics->avg('unique_users'), 0);
        $averageDailyViews = (int)round($analytics->avg('total_views'), 0);
        $peakDay = $analytics->sortByDesc('unique_users')->first()?->date;
        $peakUsers = $analytics->sortByDesc('unique_users')->first()?->unique_users;

        $firstDayViews = $analytics->last()->total_views;

        $trend = $averageDailyViews > $firstDayViews ? 'uptrend' : 'downtrend';
        if (!$firstDayViews) {
            $trendPercentage = 0;
        } else {
            $trendPercentage = (($averageDailyViews - $firstDayViews) / $firstDayViews) * 100;
        }

        return [
            'data' => [
                'post_id' => $post->id,
                'title' => $post->title,
                'analytics' => $analytics
            ],
            'meta' => [
                "total_days" => $totalDays,
                "total_unique_users" => $totalUniqueUsers,
                "total_views" => $totalViews,
                "average_daily_users" => $averageDailyUsers,
                "peak_day" => $peakDay,
                "peak_users" => $peakUsers,
                "trend" => $trend,
                "trend_percentage" => $trendPercentage
            ]
        ];
    }

}
