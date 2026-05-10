<?php

declare(strict_types=1);

namespace App\Commands\Domain;

use App\Commands\BaseCommand;
use App\Commands\Interfaces\CommandInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckDomain extends BaseCommand implements CommandInterface
{
    private array $result = [];

    public function __construct(
        private int|float $checkTimeout,
        private string $checkMethod,
        private string $url
    ) {}

    public function execute(): void
    {
        try {
            $start = microtime(true);

            $response = Http::timeout($this->checkTimeout)
                ->withOptions(['allow_redirects' => true])
                ->send($this->checkMethod, $this->url);

            $responseTime = (int) round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();
            $isUp = $response->successful() || in_array($statusCode, [301, 302, 303, 307, 308]);
        } catch (ConnectionException $e) {
            $error = 'Connection failed: ' . $e->getMessage();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("Domain check failed [{$this->url}]: {$e->getMessage()}");
        }

        $this->result = [
            'response_time' => $responseTime ?? null,
            'status_code' => $statusCode ?? null,
            'is_up' => $isUp ?? false,
            'error' => $error ?? null,
        ];
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
