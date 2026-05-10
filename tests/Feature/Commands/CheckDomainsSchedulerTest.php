<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckDomainsSchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_domains_check_command_is_scheduled_every_minute(): void
    {
        $events = app(Schedule::class)->events();

        $domainsCheckEvent = collect($events)
            ->first(static fn ($event): bool => str_contains((string) $event->command, 'domains:check'));

        $this->assertNotNull($domainsCheckEvent, 'domains:check is not registered in scheduler.');
        $this->assertSame('* * * * *', $domainsCheckEvent->expression);
    }
}
