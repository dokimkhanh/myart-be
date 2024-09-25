<?php

namespace App\Policies;

use App\Models\Art;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArtPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Art $art)
    {
        return ($user->is_admin || $user->is($art->user));
    }

    public function delete(User $user, Art $art)
    {
        return ($user->is_admin || $user->is($art->user));
    }
}
