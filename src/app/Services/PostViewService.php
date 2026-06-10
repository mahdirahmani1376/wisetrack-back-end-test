<?php

namespace App\Services;

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

}
