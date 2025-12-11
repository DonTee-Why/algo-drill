<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\Stage;
use Exception;

class InvalidSessionStateException extends Exception
{
    /**
     * Exception for when a session cannot accept submissions
     */
    public static function sessionCompleted(): self
    {
        return new self('Cannot submit to a completed session. This session has reached the DONE stage.');
    }

    /**
     * Exception for when trying to submit to an invalid stage
     */
    public static function invalidStage(Stage $currentStage): self
    {
        return new self("Cannot submit to session in {$currentStage->value} stage.");
    }
}
