<?php

namespace App\Jobs;

use App\Models\CheckLog;
use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(private readonly Domain $domain)
    {
    }

    public function handle(): void
    {
        $domain = $this->domain;

        $checkedAt    = now();
        $isUp         = false;
        $statusCode   = null;
        $responseTime = null;
        $error        = null;

        try {
            $start = microtime(true);

            $response = Http::timeout($domain->check_timeout)
                ->withOptions(['allow_redirects' => true])
                ->send($domain->check_method, $domain->url);

            $responseTime = (int) round((microtime(true) - $start) * 1000);
            $statusCode   = $response->status();
            $isUp         = $response->successful() || in_array($statusCode, [301, 302, 303, 307, 308]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = 'Connection failed: ' . $e->getMessage();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("Domain check failed [{$domain->url}]: {$e->getMessage()}");
        }

        // Save log
        CheckLog::create([
            'domain_id'     => $domain->id,
            'checked_at'    => $checkedAt,
            'is_up'         => $isUp,
            'status_code'   => $statusCode,
            'response_time' => $responseTime,
            'error'         => $error,
            'check_method'  => $domain->check_method,
        ]);

        // Update domain status cache
        $previousStatus = $domain->is_up;

        $domain->update([
            'is_up'             => $isUp,
            'last_status_code'  => $statusCode,
            'last_response_time'=> $responseTime,
            'last_checked_at'   => $checkedAt,
            'status_changed_at' => ($previousStatus !== $isUp) ? $checkedAt : $domain->status_changed_at,
        ]);
    }
}
