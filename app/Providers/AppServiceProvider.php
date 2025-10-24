<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Post;
use App\Observers\PaymentObserver;
use App\Observers\PostObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрируем Observer для модели Payment
        Payment::observe(PaymentObserver::class);

        // Регистрируем Observer для модели Post
        Post::observe(PostObserver::class);
    }
}
