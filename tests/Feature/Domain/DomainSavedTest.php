<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Events\Domain\DomainSaved;
use App\Jobs\CheckDomainJob;
use App\Listeners\Domain\CheckDomain;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class DomainSavedTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_saved_event_is_dispatched_when_domain_is_created(): void
    {
        Event::fake([DomainSaved::class]);

        $domain = $this->createDomain();

        Event::assertDispatched(DomainSaved::class, static fn (DomainSaved $event): bool => $event->domain->is($domain));
    }

    public function test_saving_domain_dispatches_check_domain_job_via_listener_when_due(): void
    {
        Bus::fake();

        $this->createDomain();

        Bus::assertDispatched(CheckDomainJob::class);
    }

    public function test_check_domain_listener_dispatches_job_when_domain_is_due(): void
    {
        Bus::fake();

        $domain = Domain::withoutEvents(fn (): Domain => $this->createDomain());

        $listener = app(CheckDomain::class);
        $listener->handle(new DomainSaved($domain));

        Bus::assertDispatched(CheckDomainJob::class);
    }

    public function test_check_domain_listener_does_not_dispatch_job_when_domain_is_not_due(): void
    {
        Bus::fake();

        $domain = Domain::withoutEvents(fn (): Domain => $this->createDomain());
        $domain->forceFill([
            'last_checked_at' => now(),
            'check_interval' => 5,
        ])->saveQuietly();

        $listener = app(CheckDomain::class);
        $listener->handle(new DomainSaved($domain));

        Bus::assertNotDispatched(CheckDomainJob::class);
    }

    private function createDomain(): Domain
    {
        $user = User::factory()->create();

        return Domain::query()->create([
            'user_id' => $user->getKey(),
            'url' => 'https://example.com',
            'name' => 'Example',
            'is_active' => true,
            'check_method' => 'GET',
            'check_interval' => 5,
            'check_timeout' => 10,
        ]);
    }
}
