<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Commands\Domain\CheckDomain;
use App\Models\CheckLog;
use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(private readonly int $domainId) {}

    public function handle(): void
    {
        $domain = Domain::findOrFail($this->domainId);

        $checkedAt = now();

        $command = app()->make(CheckDomain::class, [
            'checkTimeout' => $domain->check_timeout,
            'checkMethod' => $domain->check_method,
            'url' => $domain->url,
        ]);
        $command->execute();
        $result = $command->getResult();

        CheckLog::create([
            'domain_id' => $domain->id,
            'checked_at' => $checkedAt,
            'is_up' => $result['is_up'],
            'status_code' => $result['status_code'],
            'response_time' => $result['response_time'],
            'error' => $result['error'],
            'check_method' => $domain->check_method,
        ]);

        // Update domain status cache
        $previousStatus = $domain->is_up;

        $domain->updateQuietly([
            'is_up' => $result['is_up'],
            'last_status_code' => $result['status_code'],
            'last_response_time' => $result['response_time'],
            'last_checked_at' => $checkedAt,
            'status_changed_at' => ($previousStatus !== $result['is_up']) ? $checkedAt : $domain->status_changed_at,
        ]);
    }
}
