<?php

declare(strict_types=1);

namespace App\Listeners\Domain;

use Illuminate\Support\Facades\Bus;
use App\Events\Domain\DomainSaved;
use App\Jobs\CheckDomainJob;
use Throwable;

final class CheckDomain
{
    /**
     * @throws Throwable
     */
    public function handle(DomainSaved $event): void
    {
        $domain = $event->domain;

        if ($domain->isDue()) {
            Bus::dispatch(new CheckDomainJob($domain));
        }
    }
}
