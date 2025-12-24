<?php

namespace App\Providers;

use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
  protected $policies = [
    \App\Models\Post::class => \App\Policies\PostPolicy::class,
];

    public function boot(): void
    {
        //
    }
}
