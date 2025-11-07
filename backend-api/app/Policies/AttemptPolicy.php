<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class AttemptPolicy
{
    /**
     * Determine if the user can view the attempt.
     */
    public function view(User $user, $attempt): bool
    {
        // TODO: Implement when Attempt model exists
        // return $attempt->session->user_id === $user->id;
        return true;
    }

    /**
     * Determine if the user can create an attempt.
     */
    public function create(User $user, $session): bool
    {
        // TODO: Implement when Session model exists
        // return $session->user_id === $user->id;
        return true;
    }
}
