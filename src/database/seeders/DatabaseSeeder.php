<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(50)->create();

        $posts = collect();

        foreach ($users as $user) {
            $posts = $posts->merge(
                Post::factory()
                    ->count(rand(1, 10))
                    ->create([
                        'user_id' => $user->id,
                    ])
            );
        }

        foreach ($posts as $post) {

            $viewCount = rand(10, 500);

            for ($i = 0; $i < $viewCount; $i++) {

                $viewer = rand(0, 100) < 70
                    ? $users->random()
                    : null;

                PostView::create([
                    'post_id' => $post->id,
                    'user_id' => $viewer?->id,
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'viewed_at' => Carbon::now()
                        ->subDays(rand(0, 30))
                        ->subMinutes(rand(0, 1440)),
                ]);
            }
        }
    }
}
