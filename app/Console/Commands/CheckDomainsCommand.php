<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CheckDomainJob;
use App\Models\Domain;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CheckDomainsCommand extends Command
{
    protected $signature = 'domains:check {--domain=}';

    protected $description = 'Dispatch check jobs for all due domains';

    public function handle(): int
    {
        $query = Domain::due();

        if ($domainId = $this->option('domain')) {
            $query = Domain::active()->where('id', $domainId);
        }

        /**
         * @var Collection<int, Domain> $domains
         */
        $domains = $query->get();

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
