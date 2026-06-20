<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property $user_id
 * @property $title
 * @property $image
 * @property $content
 */
class Post extends Model
{
    use SoftDeletes,HasFactory;

    protected $fillable = [
      'user_id',
      'title',
      'image',
      'content'
    ];
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class,'post_id');
    }

    #[Scope]
    protected function filter(Builder $query, array $filters = []): void
    {
        $query
            ->when(!empty($filters['limit']), function ($q) use ($filters) {
                $q->where('created_at', '>=', $filters['limit']);
            });
    }

    public static function getTopViews(): Collection
    {
        $views = PostView::query()
            ->selectRaw('
                post_id,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users
            ')
            ->groupBy('post_id');

        return Post::query()
            ->with('author:id,name')
            ->selectRaw('
                posts.*,
                views.total_views,
                views.unique_users,
                RANK() OVER (ORDER BY views.total_views DESC) as view_rank
                ')
            ->leftJoinSub($views, 'views', function ($join) {
                $join->on('posts.id', '=', 'views.post_id');
            })
            ->orderByDesc('views.total_views')
            ->get();
    }



}
