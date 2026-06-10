<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use App\Services\PostViewService;
use Carbon\CarbonPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        PostView::truncate();
        Post::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        User::factory()->create([
            'email' => 'test@test.com',
            'password' => '123@QWER'
        ]);

        $users = User::factory(10)->create();

        $posts = collect();

        foreach ($users as $user) {
            $posts = $posts->merge(
                Post::factory()
                    ->count(rand(1, 5))
                    ->create([
                        'user_id' => $user->id,
                    ])
            );
        }

        $from = Carbon::now()->subMonths(2);
        $to = Carbon::now();

        $period = CarbonPeriod::create($from, $to);

        dump('seeding started wait for apx: 10-20 seconds');

        foreach ($period as $range) {
            foreach ($posts as $post) {

                foreach ($users as $user) {
                    $viewer = rand(0, 100) < 50
                        ? $user
                        : null;

                    $randNumDate = rand(10, 100) < 70;
                    $randNumPost = rand(10, 100) < 70;
                    if (!$randNumDate || $randNumPost) {
                        continue;
                    }


                    app(PostViewService::class)->create([
                        'post_id' => $post->id,
                        'user_id' => $viewer?->id,
                        'ip_address' => fake()->ipv4(),
                        'user_agent' => fake()->userAgent(),
                        'viewed_at' => $range
                    ]);
                }
            }
        }
        dump('seeding finished');

    }
}
