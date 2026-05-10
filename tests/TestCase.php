<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function postWithCsrf(string $routeName, array $data = [])
    {
        $token = 'test-csrf-token';

        return $this->withSession(['_token' => $token])
            ->post(route($routeName), array_merge($data, ['_token' => $token]));
    }
}
