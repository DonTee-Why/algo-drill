<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CoachingSession;
use App\Models\User;

class SessionPolicy
{
    /**
     * Determine if the user can view the session.
     */
    public function view(User $user, CoachingSession $session): bool
    {
        return $session->user_id === $user->id;
    }

    /**
     * Determine if the user can update the session.
     */
    public function update(User $user, CoachingSession $session): bool
    {
        return $session->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the session.
     */
    public function delete(User $user, CoachingSession $session): bool
    {
        return $session->user_id === $user->id;
    }

    /**
     * Determine if the user can submit to the session.
     */
    public function submit(User $user, CoachingSession $session): bool
    {
        return $session->user_id === $user->id;
    }
}
