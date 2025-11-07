<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class TestRunPolicy
{
    /**
     * Determine if the user can view the test run.
     */
    public function view(User $user, $testRun): bool
    {
        // TODO: Implement when TestRun model exists
        // return $testRun->session->user_id === $user->id;
        return true;
    }

    /**
     * Determine if the user can create a test run.
     */
    public function create(User $user, $session): bool
    {
        // TODO: Implement when Session model exists
        // return $session->user_id === $user->id;
        return true;
    }
}
