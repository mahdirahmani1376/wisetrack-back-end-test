<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property $post_id
 * @property $user_id
 * @property $ip_address
 * @property $user_agent
 * @property $viewed_at
 */
class PostView extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function filter(Builder $query, array $filters = []): void
    {
        $query
            ->when(!empty($filters['from']), function ($q) use ($filters) {
                $q->where('created_at', '>=', $filters['from']);
            })
            ->when(!empty($filters['post_id']), function ($q) use ($filters) {
                $q->where('post_id', '=', $filters['post_id']);
            })
            ->when(!empty($filters['to']), function ($q) use ($filters) {
                $q->where('created_at', '<', $filters['to']);
            });
    }

    public static function getAnalytics(array $filters = []): Collection
    {
        return static::query()->filter($filters)->selectRaw('
                DATE(viewed_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as registered_users,
                COUNT(CASE WHEN user_id IS NULL THEN 1 END) as guest_users
            ')
            ->groupBy(DB::raw('Date(viewed_at)'))
            ->orderByDesc(DB::raw('Date(viewed_at)'))
            ->get();
    }

    public static function getCountOfUniqueUsers(): int
    {
        return static::query()->distinct('user_id')->count('user_id');
    }

    public static function getPeriodDays()
    {
        return static::query()
            ->selectRaw('
                COALESCE(DATEDIFF(MAX(viewed_at), MIN(viewed_at)), 0) as period_days'
            )
            ->value('period_days');
    }

    public static function getAverageViewsPerPost(): int
    {
        return (int)static::query()
            ->selectRaw('AVG(total_views) as avg_views')
            ->fromSub(function ($query) {
                $query->from('post_views')
                    ->selectRaw('post_id, COUNT(*) as total_views')
                    ->groupBy('post_id');
            }, 't')
            ->value('avg_views');
    }

}
