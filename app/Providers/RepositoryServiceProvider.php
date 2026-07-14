<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\AuthRepositoryInterface;
use App\Domain\Call\Contracts\CallRepositoryInterface;
use App\Domain\Contact\Contracts\ContactRepositoryInterface;
use App\Domain\Lead\Contracts\LeadRepositoryInterface;
use App\Domain\Pipeline\Contracts\DealRepositoryInterface;
use App\Domain\Pipeline\Contracts\PipelineRepositoryInterface;
use App\Domain\Tenant\Contracts\TenantRepositoryInterface;
use App\Infrastructure\Auth\Repositories\AuthRepository;
use App\Infrastructure\Call\Repositories\CallRepository;
use App\Infrastructure\Contact\Repositories\ContactRepository;
use App\Infrastructure\Lead\Repositories\LeadRepository;
use App\Infrastructure\Pipeline\Repositories\DealRepository;
use App\Domain\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Domain\WhatsApp\Contracts\WhatsAppRepositoryInterface;
use App\Domain\Task\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Dashboard\Repositories\DashboardRepository;
use App\Infrastructure\WhatsApp\Repositories\WhatsAppRepository;
use App\Infrastructure\Task\Repositories\TaskRepository;
use App\Infrastructure\Pipeline\Repositories\PipelineRepository;
use App\Infrastructure\Tenant\Repositories\TenantRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(CallRepositoryInterface::class, CallRepository::class);
        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(PipelineRepositoryInterface::class, PipelineRepository::class);
        $this->app->bind(DealRepositoryInterface::class, DealRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(WhatsAppRepositoryInterface::class, WhatsAppRepository::class);
    }
}
