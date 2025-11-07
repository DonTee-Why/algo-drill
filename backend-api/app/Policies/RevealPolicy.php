<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class RevealPolicy
{
    /**
     * Determine if the user can reveal the solution for a session.
     */
    public function reveal(User $user, $session): bool
    {
        // TODO: Implement when Session model exists
        // Check session ownership
        // if ($session->user_id !== $user->id) {
        //     return false;
        // }
        //
        // // Check session state is DONE
        // if ($session->state !== 'DONE') {
        //     return false;
        // }
        //
        // // Check all rubric thresholds met
        // // Check all tests passed
        //
        // return true;
        return true;
    }
}
