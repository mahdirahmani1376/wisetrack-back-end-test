<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')->constrained('posts','id')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users','id')->nullOnDelete();

            $table->ipAddress();
            $table->text('user_agent');

            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique([
                'post_id',
                'user_id',
                'viewed_at'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
