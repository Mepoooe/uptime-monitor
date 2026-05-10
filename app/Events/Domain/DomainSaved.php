<?php

declare(strict_types=1);

namespace App\Events\Domain;

use App\Events\AbstractModelEvent;
use App\Models\Domain;

final class DomainSaved extends AbstractModelEvent
{
    public function __construct(
        public Domain $domain,
    ) {
    }
}