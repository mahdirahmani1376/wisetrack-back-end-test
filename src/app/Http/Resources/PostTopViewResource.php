<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostTopViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank' => $this->when(isset($this->rank), $this->rank),
            'post_id' => $this->id,
            'title' => $this->title,
            'author' => $this->author->name,
            'total_views' => $this->total_views,
            'unique_users' => $this->unique_users,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
