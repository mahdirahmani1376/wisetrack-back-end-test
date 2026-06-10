<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $file = UploadedFile::fake();
        $data = [
            'title' => $this->faker->word(),
            'content' => $this->faker->word(),
            'user_id' => 5,
            'file' => $file
        ];

        return app(PostService::class)->store($data);
    }
}
