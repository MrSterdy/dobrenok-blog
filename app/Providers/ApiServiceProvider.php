<?php

namespace App\Providers;

use App\Services\PostService;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\PartnerRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Repositories\Contracts\ProjectPaymentGoalRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\EventRepository;
use App\Repositories\ApplicationRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SubscriptionRepository;
use App\Services\ProjectService;
use App\Services\EmployeeService;
use App\Services\PartnerService;
use App\Services\EventService;
use App\Services\ApplicationService;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use App\Services\WebhookService;
use App\Repositories\ProjectPaymentGoalRepository;
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
        $this->app->scoped(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->scoped(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->scoped(ProjectPaymentGoalRepositoryInterface::class, ProjectPaymentGoalRepository::class);

        $this->app->scoped(PostService::class);
        $this->app->scoped(ProjectService::class);
        $this->app->scoped(PartnerService::class);
        $this->app->scoped(EmployeeService::class);
        $this->app->scoped(EventService::class);
        $this->app->scoped(ApplicationService::class);
        $this->app->scoped(PaymentService::class);
        $this->app->scoped(SubscriptionService::class);
        $this->app->scoped(WebhookService::class);
    }

    public function boot(): void
    {
        //
    }
}
