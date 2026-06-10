<?php

namespace App\Http\Resources;

use App\Models\PostView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PostView */
class PostViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post' => PostResource::make($this->post),
            'viewer' => UserResource::make($this->viewer),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'viewed_at' => $this->viewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
