<?php

namespace App\Providers;

use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Eloquent\ProjectRepository;
use App\Repositories\Eloquent\TaskRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bind repository interfaces to their Eloquent implementations.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        ProjectRepositoryInterface::class => ProjectRepository::class,
        TaskRepositoryInterface::class => TaskRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
