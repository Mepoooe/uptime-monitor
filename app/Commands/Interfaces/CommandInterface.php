<?php

declare(strict_types=1);

namespace App\Commands\Interfaces;

interface CommandInterface
{
    public function execute(): void;
}