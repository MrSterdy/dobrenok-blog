<?php

namespace App\Providers;

use App\Services\PostService;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->scoped(PostRepositoryInterface::class, PostRepository::class);

        $this->app->scoped(PostService::class);
        $this->app->scoped(ProjectService::class);
    }

    public function boot(): void
    {
        //
    }
}
