<?php

namespace App\Services;

use App\Http\Resources\PostTopViewResource;
use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        $responseData['post_id'] = $post->id;
        $responseData['title'] = $post->title;

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
            ->groupBy(DB::raw('Date(viewed_at)'))
            ->orderBy(DB::raw('Date(viewed_at)'))
            ->get();


        $responseData['period'] = [
            'from' => $from,
            'to' => $to
        ];

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

    public function getTopViewPosts(array $filters=[]): Collection
    {
        $limit = $filters['limit'] ?? 10;

        $views = PostView::query()
            ->selectRaw('
                post_id,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users
            ')
            ->groupBy('post_id');

        return Post::query()
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

    }
}
