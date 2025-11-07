<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class SessionPolicy
{
    /**
     * Determine if the user can view the session.
     */
    public function view(User $user, $session): bool
    {
        // TODO: Implement when Session model exists
        // return $session->user_id === $user->id;
        return true;
    }

    /**
     * Determine if the user can update the session.
     */
    public function update(User $user, $session): bool
    {
        // TODO: Implement when Session model exists
        // return $session->user_id === $user->id;
        return true;
    }

    /**
     * Determine if the user can delete the session.
     */
    public function delete(User $user, $session): bool
    {
        // TODO: Implement when Session model exists
        // return $session->user_id === $user->id;
        return true;
    }
}
