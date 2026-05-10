<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CheckDomainJob;
use App\Models\Domain;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CheckDomainsCommand extends Command
{
    protected $signature = 'domains:check {--domain=}';

    protected $description = 'Dispatch check jobs for all due domains';

    public function handle(): int
    {
        $query = Domain::query()->active();

        if ($domainId = $this->option('domain')) {
            $query->where('id', $domainId);
        }

        /**
         * @var Collection<int, Domain> $domains
         */
        $domains = $query
            ->whereRaw('last_checked_at IS NULL OR last_checked_at <= NOW() - INTERVAL check_interval MINUTE')
            ->get()
            ->values();

        if ($domains->isEmpty()) {
            $this->info('No domains due for checking.');

            return Command::SUCCESS;
        }

        foreach ($domains as $domain) {
            CheckDomainJob::dispatch($domain->getKey());
            $this->line("  Dispatched: {$domain->url}");
        }

        $this->info("Dispatched {$domains->count()} check(s).");

        return Command::SUCCESS;
    }
}
