<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Commands\Domain\CheckDomain;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class CheckDomainTest extends TestCase
{
    public function test_execute_sets_result_for_successful_response(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $command = new CheckDomain(10, 'GET', 'https://example.com');
        $command->execute();

        $result = $command->getResult();

        $this->assertIsInt($result['response_time']);
        $this->assertGreaterThanOrEqual(0, $result['response_time']);
        $this->assertSame(200, $result['status_code']);
        $this->assertTrue($result['is_up']);
        $this->assertNull($result['error']);
    }

    public function test_execute_marks_redirect_response_as_up(): void
    {
        Http::fake([
            '*' => Http::response('', 301),
        ]);

        $command = new CheckDomain(10, 'GET', 'https://example.com');
        $command->execute();

        $result = $command->getResult();

        $this->assertSame(301, $result['status_code']);
        $this->assertTrue($result['is_up']);
        $this->assertNull($result['error']);
    }

    public function test_execute_sets_connection_error_result_when_request_fails_to_connect(): void
    {
        Http::fake(static fn () => throw new ConnectionException('Could not connect to host'));

        $command = new CheckDomain(10, 'GET', 'https://example.com');
        $command->execute();

        $result = $command->getResult();

        $this->assertNull($result['response_time']);
        $this->assertNull($result['status_code']);
        $this->assertFalse($result['is_up']);
        $this->assertSame('Connection failed: Could not connect to host', $result['error']);
    }

    public function test_execute_logs_and_sets_error_for_unexpected_exception(): void
    {
        Log::spy();
        Http::fake(static fn () => throw new \Exception('Unexpected failure'));

        $command = new CheckDomain(10, 'GET', 'https://example.com');
        $command->execute();

        $result = $command->getResult();

        $this->assertNull($result['response_time']);
        $this->assertNull($result['status_code']);
        $this->assertFalse($result['is_up']);
        $this->assertSame('Unexpected failure', $result['error']);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Domain check failed [https://example.com]: Unexpected failure');
    }
}
