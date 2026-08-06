<?php

declare(strict_types=1);

namespace App\Domains\Coach\Contracts;

use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Models\CoachingSession;

interface StageCritiqueRequestBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(CoachingSession $session, array $payload): CoachCritiqueRequest;
}
