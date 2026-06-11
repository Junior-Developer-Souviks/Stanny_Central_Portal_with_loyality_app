<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AccountingRepository;
use App\Interfaces\AccountingRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AccountingRepositoryInterface::class, AccountingRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
