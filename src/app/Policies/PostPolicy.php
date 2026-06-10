<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Post $post): bool
    {
        return PostService::userCanAccessPost($post,$user);
    }

}
