<?php

namespace App\Providers;

use App\Models\Domain;
use App\Policies\DomainPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Domain::class => DomainPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
