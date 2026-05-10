<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Jobs\CheckDomainJob;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

final class CheckDomainsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_jobs_only_for_active_due_domains(): void
    {
        Bus::fake();

        $dueNeverChecked = $this->createDomain([
            'url' => 'https://never-checked.example.com',
            'is_active' => true,
            'last_checked_at' => null,
            'check_interval' => 10,
        ]);

        $dueByInterval = $this->createDomain([
            'url' => 'https://due-by-interval.example.com',
            'is_active' => true,
            'last_checked_at' => now()->subMinutes(11),
            'check_interval' => 10,
        ]);

        $notDueYet = $this->createDomain([
            'url' => 'https://not-due.example.com',
            'is_active' => true,
            'last_checked_at' => now()->subMinutes(4),
            'check_interval' => 10,
        ]);

        $inactiveButDue = $this->createDomain([
            'url' => 'https://inactive-but-due.example.com',
            'is_active' => false,
            'last_checked_at' => now()->subMinutes(11),
            'check_interval' => 10,
        ]);

        $this->artisan('domains:check')
            ->expectsOutput("  Dispatched: {$dueNeverChecked->url}")
            ->expectsOutput("  Dispatched: {$dueByInterval->url}")
            ->expectsOutput('Dispatched 2 check(s).')
            ->assertSuccessful();

        Bus::assertDispatched(CheckDomainJob::class, 2);
        Bus::assertDispatched(CheckDomainJob::class, static function (CheckDomainJob $job) use ($dueNeverChecked): bool {
            return self::jobDomainId($job) === $dueNeverChecked->id;
        });
        Bus::assertDispatched(CheckDomainJob::class, static function (CheckDomainJob $job) use ($dueByInterval): bool {
            return self::jobDomainId($job) === $dueByInterval->id;
        });

        Bus::assertNotDispatched(CheckDomainJob::class, static function (CheckDomainJob $job) use ($notDueYet): bool {
            return self::jobDomainId($job) === $notDueYet->id;
        });

        Bus::assertNotDispatched(CheckDomainJob::class, static function (CheckDomainJob $job) use ($inactiveButDue): bool {
            return self::jobDomainId($job) === $inactiveButDue->id;
        });
    }

    public function test_it_outputs_message_when_no_domains_are_due(): void
    {
        Bus::fake();

        $this->createDomain([
            'is_active' => true,
            'last_checked_at' => now(),
            'check_interval' => 15,
        ]);

        $this->createDomain([
            'is_active' => false,
            'last_checked_at' => now()->subMinutes(30),
            'check_interval' => 10,
        ]);

        $this->artisan('domains:check')
            ->expectsOutput('No domains due for checking.')
            ->assertSuccessful();

        Bus::assertNothingDispatched();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createDomain(array $overrides = []): Domain
    {
        $user = User::factory()->create();

        $defaults = [
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'name' => 'Example',
            'is_active' => true,
            'check_method' => 'GET',
            'check_interval' => 5,
            'check_timeout' => 10,
            'last_checked_at' => null,
        ];

        return Domain::withoutEvents(static fn (): Domain => Domain::query()->create(array_merge($defaults, $overrides)));
    }

    private static function jobDomainId(CheckDomainJob $job): int
    {
        /** @var int */
        return (fn (): int => $this->domainId)->call($job);
    }
}
