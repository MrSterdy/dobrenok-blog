<?php

namespace App\Providers;

use App\Services\PostService;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\PartnerRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\EventRepository;
use App\Repositories\ApplicationRepository;
use App\Services\ProjectService;
use App\Services\EmployeeService;
use App\Services\PartnerService;
use App\Services\EventService;
use App\Services\ApplicationService;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->scoped(PostRepositoryInterface::class, PostRepository::class);
        $this->app->scoped(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->scoped(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->scoped(EventRepositoryInterface::class, EventRepository::class);
        $this->app->scoped(ApplicationRepositoryInterface::class, ApplicationRepository::class);

        $this->app->scoped(PostService::class);
        $this->app->scoped(ProjectService::class);
        $this->app->scoped(PartnerService::class);
        $this->app->scoped(EmployeeService::class);
        $this->app->scoped(EventService::class);
        $this->app->scoped(ApplicationService::class);
    }

    public function boot(): void
    {
        //
    }
}
