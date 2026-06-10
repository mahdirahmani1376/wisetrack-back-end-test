<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
