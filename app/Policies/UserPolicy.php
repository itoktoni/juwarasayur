<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy extends BasePolicy
{
    // ponytail: GeneralRequest maps postApprove->can('approve') & postReject->can('reject').
    // Tanpa method ini Gate deny -> 403 untuk semua role termasuk developer.
    public function approve(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function reject(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }
}
