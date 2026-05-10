<?php

declare(strict_types=1);

namespace App\Application\Providers;

use App\Events\Domain\DomainSaved;
use App\Listeners\Domain\CheckDomain;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Override;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        DomainSaved::class => [
            CheckDomain::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    #[Override]
    public function boot(): void
    {
        parent::boot();
    }
}
